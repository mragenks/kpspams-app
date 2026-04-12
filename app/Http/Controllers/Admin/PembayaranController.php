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

    public function confirm(Request $request, $id)
    {
        $pembayaranPending = Pembayaran::findOrFail($id);
        $tagihan = TagihanAir::findOrFail($pembayaranPending->tagihan_id);

        // Validasi jika tagihan sudah lunas duluan
        if ($tagihan->sisa_tagihan <= 0) {
            return back()->with('error', 'Tagihan sudah lunas! Silakan tolak/hapus bukti pending ini.');
        }

        $request->validate([
            'nominal_bayar' => 'required|numeric|min:1|max:' . $tagihan->sisa_tagihan,
            'akun_kas_id'   => 'required|exists:akun_kas,id',
        ]);

        DB::transaction(function () use ($request, $pembayaranPending, $tagihan) {
            // 1. Update bukti pending menjadi pembayaran sah
            $pembayaranPending->update([
                'nominal_bayar' => $request->nominal_bayar,
                'akun_kas_id'   => $request->akun_kas_id,
                'is_pending'    => false,
            ]);

            // 2. Kalkulasi Tagihan
            $totalTerbayarBaru = $tagihan->total_terbayar + $request->nominal_bayar;
            $sisaTagihanBaru = $tagihan->total_tagihan - $totalTerbayarBaru;

            $status = 'belum_lunas';
            if ($sisaTagihanBaru <= 0) {
                $status = 'lunas';
                $sisaTagihanBaru = 0;
            } elseif ($totalTerbayarBaru > 0) {
                $status = 'mencicil';
            }

            $tagihan->update([
                'total_terbayar' => $totalTerbayarBaru,
                'sisa_tagihan'   => $sisaTagihanBaru,
                'status_bayar'   => $status,
            ]);

            // 3. Catat ke Arus Kas
            $akunKas = AkunKas::find($request->akun_kas_id);
            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => 'masuk',
                'nominal'         => $request->nominal_bayar,
                'keterangan'      => 'Konfirmasi transfer: ' . $tagihan->pelanggan->nama_pelanggan,
                'tanggal'         => $pembayaranPending->tanggal_bayar, // Pakai tanggal upload pelanggan
                'dicatat_oleh'    => auth()->id(),
                'pembayaran_id'   => $pembayaranPending->id,
            ]);

            $akunKas->increment('saldo', $request->nominal_bayar);
        });

        return back()->with('success', 'Bukti pembayaran berhasil dikonfirmasi dan masuk ke Kas!');
    }

    public function reject($id)
    {
        $pembayaranPending = Pembayaran::findOrFail($id);

        // Hapus file foto dari server agar tidak memenuhi harddisk
        if ($pembayaranPending->foto_bukti) {
            Storage::disk('public')->delete($pembayaranPending->foto_bukti);
        }

        $pembayaranPending->delete();

        return back()->with('success', 'Bukti pembayaran ditolak dan dihapus.');
    }

    public function payWithSaldo(Request $request, $tagihanId)
    {
        $tagihan = \App\Models\TagihanAir::findOrFail($tagihanId);
        $pelanggan = $tagihan->pelanggan;

        if ($pelanggan->saldo <= 0) {
            return back()->with('error', 'Saldo pelanggan tidak mencukupi (Rp 0).');
        }

        DB::transaction(function () use ($tagihan, $pelanggan) {
            $nominalPotong = min($pelanggan->saldo, $tagihan->sisa_tagihan);

            // 1. Buat catatan Pembayaran
            $pembayaran = \App\Models\Pembayaran::create([
                'tagihan_id'    => $tagihan->id,
                'pelanggan_id'  => $pelanggan->id,
                'nominal_bayar' => $nominalPotong,
                'tanggal_bayar' => now(),
                'keterangan'    => 'Pelunasan via Saldo/Deposit',
                'is_pending'    => false,
                'dicatat_oleh'  => auth()->id(),
            ]);

            // 2. Update Tagihan
            $tagihan->total_terbayar += $nominalPotong;
            $tagihan->sisa_tagihan   -= $nominalPotong;
            $tagihan->status_bayar    = ($tagihan->sisa_tagihan <= 0) ? 'lunas' : 'mencicil';
            $tagihan->save();

            // 3. Potong Saldo Pelanggan
            $pelanggan->decrement('saldo', $nominalPotong);

            // 4. TAMBAHKAN KE ARUS KAS SEBAGAI MEMO (NOMINAL 0)
            \App\Models\ArusKas::create([
                'akun_kas_id'     => 1, // Opsional: arahkan ke kas utama atau kas manapun
                'jenis_transaksi' => 'masuk',
                'nominal'         => 0, // <--- Kunci: Nominal 0 agar tidak merusak saldo riil
                'keterangan'      => "Pelunasan Tagihan: {$pelanggan->nama_pelanggan} (Via Saldo - Rp " . number_format($nominalPotong, 0, ',', '.') . ")",
                'tanggal'         => now(),
                'pembayaran_id'   => $pembayaran->id,
                'dicatat_oleh'    => auth()->id(),
            ]);
        });

        return back()->with('success', 'Tagihan berhasil dibayar menggunakan saldo!');
    }
}
