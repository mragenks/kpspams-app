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

    // 1. UBAH NAMA VARIABEL $pembayaran MENJADI $tagihan DI SINI
    // Laravel Implicit Binding akan mencari ID TagihanAir, bukan Pembayaran
    public function show($id)
    {
        // Cari TagihanAir berdasarkan ID, lalu load SEMUA relasi yang dibutuhkan
        // Kita tidak menggunakan implicit binding (TagihanAir $tagihan) untuk mencegah
        // kebingungan jika route parameter bernama {pembayaran}
        $tagihan = TagihanAir::with(['pelanggan', 'pencatatanMeter', 'pembayaran.akunKas'])
            ->findOrFail($id);

        $akunKas = AkunKas::where('is_active', true)->get();

        return view('admin.pembayaran.show', [
            'pembayaran' => $tagihan,
            'akunKas' => $akunKas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tagihan_id'    => 'required|exists:tagihan_air,id',
            'akun_kas_id'   => 'required|exists:akun_kas,id',
            'nominal_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'keterangan'    => 'nullable|string|max:255',
            'foto_bukti'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $tagihan = TagihanAir::findOrFail($request->tagihan_id);

        // 2. CEGAH PEMBAYARAN JIKA SUDAH LUNAS
        if ($tagihan->status_bayar === 'lunas' || $tagihan->sisa_tagihan <= 0) {
            return back()->with('error', 'Gagal: Tagihan ini sudah lunas.');
        }

        if ($request->nominal_bayar > $tagihan->sisa_tagihan) {
            return back()->withInput()->with('error', 'Nominal pembayaran (Rp ' . number_format($request->nominal_bayar, 0, ',', '.') . ') melebihi sisa tagihan (Rp ' . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ')!');
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

            // 3. LOGIKA PERHITUNGAN YANG LEBIH AMAN
            $totalTerbayarBaru = $tagihan->total_terbayar + $request->nominal_bayar;
            $sisaTagihanBaru = $tagihan->total_tagihan - $totalTerbayarBaru;

            $status = 'belum_lunas';
            // Pengecekan status yang paling aman adalah <= 0 (bukan == 0) untuk mencegah nilai minus
            if ($sisaTagihanBaru <= 0) {
                $status = 'lunas';
                $sisaTagihanBaru = 0; // Kunci agar tidak minus
            } elseif ($totalTerbayarBaru > 0) {
                $status = 'mencicil';
            }

            $tagihan->update([
                'total_terbayar' => $totalTerbayarBaru,
                'sisa_tagihan'   => $sisaTagihanBaru,
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
            ->with('success', 'Pembayaran sebesar Rp ' . number_format($request->nominal_bayar, 0, ',', '.') . ' berhasil dicatat!');
    }
}
