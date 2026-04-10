<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\TagihanAir;
use App\Models\AkunKas;
use App\Models\ArusKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        $status = $request->status ?? '';

        $tagihan = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
            ->whereHas('pelanggan', fn($q) => $q->where('status', 'aktif'))
            ->where('bulan_periode', $bulan)
            ->where('tahun_periode', $tahun)
            ->when($status, fn($q) => $q->where('status_bayar', $status))
            ->latest()
            ->paginate(10);

        return view('admin.pembayaran.index', compact('tagihan', 'bulan', 'tahun', 'status'));
    }

    public function show(TagihanAir $pembayaran)
    {
        $pembayaran->load(['pelanggan', 'pencatatanMeter', 'pembayaran.akunKas']);
        $akunKas = AkunKas::where('is_active', true)->get();
        return view('admin.pembayaran.show', compact('pembayaran', 'akunKas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tagihan_id'    => 'required|exists:tagihan_air,id',
            'akun_kas_id'   => 'required|exists:akun_kas,id',
            'nominal_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'keterangan'    => 'nullable|string|max:255',
            'foto_bukti'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $tagihan = TagihanAir::findOrFail($request->tagihan_id);

        if ($request->nominal_bayar > $tagihan->sisa_tagihan) {
            return back()->with('error', 'Nominal pembayaran melebihi sisa tagihan!');
        }

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti/pembayaran', 'public');
        }

        DB::transaction(function () use ($request, $tagihan, $fotoPath) {
            $pembayaran = Pembayaran::create([
                'tagihan_id'    => $tagihan->id,
                'pelanggan_id'  => $tagihan->pelanggan_id,
                'akun_kas_id'   => $request->akun_kas_id,
                'nominal_bayar' => $request->nominal_bayar,
                'tanggal_bayar' => $request->tanggal_bayar,
                'keterangan'    => $request->keterangan,
                'foto_bukti'    => $fotoPath,
                'dicatat_oleh'  => auth()->id(),
            ]);

            $totalTerbayar = $tagihan->total_terbayar + $request->nominal_bayar;
            $sisaTagihan = $tagihan->total_tagihan - $totalTerbayar;

            $status = 'belum_lunas';
            if ($sisaTagihan <= 0) {
                $status = 'lunas';
                $sisaTagihan = 0;
            } elseif ($totalTerbayar > 0) {
                $status = 'mencicil';
            }

            $tagihan->update([
                'total_terbayar' => $totalTerbayar,
                'sisa_tagihan'   => $sisaTagihan,
                'status_bayar'   => $status,
            ]);

            $akunKas = AkunKas::find($request->akun_kas_id);
            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => 'masuk',
                'nominal'         => $request->nominal_bayar,
                'keterangan'      => 'Pembayaran tagihan air ' .
                    $tagihan->pelanggan->nama_pelanggan . ' - ' .
                    \Carbon\Carbon::create()->month($tagihan->bulan_periode)->isoFormat('MMMM') . ' ' .
                    $tagihan->tahun_periode,
                'tanggal'         => $request->tanggal_bayar,
                'dicatat_oleh'    => auth()->id(),
                'pembayaran_id'   => $pembayaran->id,
            ]);

            $akunKas->increment('saldo', $request->nominal_bayar);
        });

        return redirect()->route('admin.pembayaran.show', $request->tagihan_id)
            ->with('success', 'Pembayaran berhasil dicatat!');
    }
}
