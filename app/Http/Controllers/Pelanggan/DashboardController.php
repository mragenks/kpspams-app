<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\TagihanAir;
use App\Models\Pembayaran;
use App\Models\User;
use App\Notifications\PembayaranBaruNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $pelanggan = auth()->user()->pelanggan;

        if (!$pelanggan) {
            abort(403, 'Data pelanggan tidak ditemukan.');
        }

        $tagihanAktif = TagihanAir::where('pelanggan_id', $pelanggan->id)
            ->whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->orderBy('tahun_periode', 'asc')
            ->orderBy('bulan_periode', 'asc')
            ->first();

        $riwayatTagihan = TagihanAir::where('pelanggan_id', $pelanggan->id)
            ->with(['pencatatanMeter', 'pembayaran.akunKas'])
            ->latest()
            ->paginate(6);

        return view('pelanggan.dashboard', compact(
            'pelanggan',
            'tagihanAktif',
            'riwayatTagihan'
        ));
    }

    public function submitBukti(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tagihan_air,id',
            'foto_bukti' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'foto_bukti.max'   => 'Gagal: Ukuran foto bukti maksimal adalah 5 MB.',
            'foto_bukti.image' => 'Gagal: File yang diupload harus berupa gambar.',
            'foto_bukti.mimes' => 'Gagal: Format gambar harus JPG, JPEG, atau PNG.',
            'foto_bukti.required' => 'Gagal: Anda belum memasukkan foto bukti pembayaran.'
        ]);

        $pelanggan = auth()->user()->pelanggan;

        $tagihan = TagihanAir::where('id', $request->tagihan_id)
            ->where('pelanggan_id', $pelanggan->id)
            ->whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->firstOrFail();

        // Upload foto
        $fotoPath = $request->file('foto_bukti')->store('bukti/pelanggan', 'public');

        $pembayaran = Pembayaran::create([
            'tagihan_id'    => $tagihan->id,
            'pelanggan_id'  => $pelanggan->id,
            'akun_kas_id'   => null,
            'nominal_bayar' => 0,
            'tanggal_bayar' => now()->toDateString(),
            'keterangan'    => '[BUKTI PELANGGAN] ' . ($request->keterangan ?? 'Bukti pembayaran dari pelanggan'),
            'foto_bukti'    => $fotoPath,
            'status_validasi' => 'pending',
            'dicatat_oleh'  => auth()->id(),
        ]);

        $admins = User::whereIn('role', ['admin', 'ketua'])->get();

        Notification::send($admins, new PembayaranBaruNotification($pembayaran, $pelanggan));

        return back()->with('success', 'Bukti pembayaran berhasil dikirim! Admin akan segera memproses.');
    }

    public function suratTagihan(Request $request, $id)
    {
        if ($request->has('notif_id')) {
            $notif = auth()->user()->notifications()->find($request->notif_id);
            if ($notif) {
                $notif->markAsRead();
            }
        }

        $pelanggan = auth()->user()->pelanggan;
        if (!$pelanggan) {
            abort(403, 'Data pelanggan tidak ditemukan.');
        }

        $tagihan = TagihanAir::with(['pelanggan.user', 'pencatatanMeter'])
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        return view('pelanggan.surat_tagihan', compact('tagihan'));
    }
}
