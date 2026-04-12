<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\TagihanAir;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

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
            ->latest()
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
            'foto_bukti' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $pelanggan = auth()->user()->pelanggan;

        // Pastikan tagihan milik pelanggan ini
        $tagihan = TagihanAir::where('id', $request->tagihan_id)
            ->where('pelanggan_id', $pelanggan->id)
            ->whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->firstOrFail();

        // Upload foto
        $fotoPath = $request->file('foto_bukti')->store('bukti/pelanggan', 'public');

        // Simpan sebagai bukti pending (nominal 0, menunggu konfirmasi admin)
        Pembayaran::create([
            'tagihan_id'    => $tagihan->id,
            'pelanggan_id'  => $pelanggan->id,
            'akun_kas_id'   => null,
            'nominal_bayar' => 0,
            'tanggal_bayar' => now()->toDateString(),
            'keterangan'    => '[BUKTI PELANGGAN] ' . ($request->keterangan ?? 'Bukti pembayaran dari pelanggan'),
            'foto_bukti'    => $fotoPath,
            'dicatat_oleh'  => auth()->id(),
            'is_pending'    => true,
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil dikirim! Admin akan segera memproses.');
    }
}
