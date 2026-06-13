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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PembayaranController extends Controller
{
    // ==========================================
    // FUNGSI HELPER UNTUK WHATSAPP (FONNTE)
    // ==========================================
    private function sendWhatsApp($no_hp, $pesan)
    {
        $token = config('services.fonnte.token');

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => $token
            ])->post('https://api.fonnte.com/send', [
                'target' => $no_hp,
                'message' => $pesan,
                'delay' => '2',
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Fonnte API Error: ' . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // LOGIKA PENYUSUNAN PESAN WA PEMBAYARAN
    // ==========================================
    private function kirimNotifikasiWAPembayaran($tagihan, $nominalBayarTerakhir)
    {
        $pelanggan = $tagihan->pelanggan;

        if (!empty($pelanggan->no_hp) && strlen($pelanggan->no_hp) >= 10) {
            $pesan = "";
            $bulanStr = \Carbon\Carbon::create(null, $tagihan->bulan_periode, 1)->isoFormat('MMMM Y');

            $nominalBayar = "Rp " . number_format($nominalBayarTerakhir, 0, ',', '.');
            $sisaTagihan = "Rp " . number_format($tagihan->sisa_tagihan, 0, ',', '.');

            if ($tagihan->status_bayar === 'lunas') {
                $pesan = "Halo, Bpk/Ibu *{$pelanggan->nama_pelanggan}*.\n\n";
                $pesan .= "Terima kasih! Pembayaran tagihan air PAMSIMAS Tirta Wening untuk periode *{$bulanStr}* telah berhasil dikonfirmasi.\n\n";
                $pesan .= "✅ Status: *LUNAS*\n";
                $pesan .= "💰 Jumlah Dibayar: *{$nominalBayar}*\n\n";
                $pesan .= "Terima kasih atas partisipasi Anda membayar tepat waktu. 🙏";
            } elseif ($tagihan->status_bayar === 'mencicil') {
                $pesan = "Halo, Bpk/Ibu *{$pelanggan->nama_pelanggan}*.\n\n";
                $pesan .= "Terima kasih! Kami telah menerima pembayaran cicilan tagihan air PAMSIMAS Tirta Wening untuk periode *{$bulanStr}*.\n\n";
                $pesan .= "⏳ Status: *MENCICIL*\n";
                $pesan .= "💰 Jumlah Dibayar: *{$nominalBayar}*\n";
                $pesan .= "❗️ Sisa Tunggakan: *{$sisaTagihan}*\n\n";
                $pesan .= "Mohon untuk segera melunasi sisa tagihan Anda. Terima kasih. 🙏";
            }

            if ($pesan !== "") {
                $this->sendWhatsApp($pelanggan->no_hp, $pesan);
            }
        }
    }

    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        $status = $request->status ?? '';
        $search = $request->search;

        $tagihan = TagihanAir::with(['pelanggan.user', 'pencatatanMeter'])
            ->whereHas('pelanggan', fn($q) => $q->where('status', 'aktif'))
            ->where('bulan_periode', $bulan)
            ->where('tahun_periode', $tahun)
            ->when($status, fn($q) => $q->where('status_bayar', $status))
            ->when($search, function ($query) use ($search) {
                $query->whereHas('pelanggan', function ($q) use ($search) {
                    $q->where('kode_pelanggan', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('nama_lengkap', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pembayaran.index', compact('tagihan', 'bulan', 'tahun', 'status', 'search'));
    }

    public function show(Request $request, $id)
    {
        if ($request->has('notif_id')) {
            $notif = auth()->user()->notifications()->find($request->notif_id);
            if ($notif) {
                $notif->markAsRead();
            }
        }

        $tagihan = TagihanAir::with(['pelanggan.user', 'pencatatanMeter', 'pembayaran.akunKas'])
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
        ], [
            'foto_bukti.max'   => 'Gagal: Ukuran foto bukti maksimal adalah 5 MB.',
            'foto_bukti.image' => 'Gagal: File yang diupload harus berupa gambar.',
            'foto_bukti.mimes' => 'Gagal: Format gambar harus JPG, JPEG, atau PNG.'
        ]);

        $tagihan = TagihanAir::findOrFail($request->tagihan_id);

        if ($tagihan->status_bayar === 'lunas' || $tagihan->sisa_tagihan <= 0) {
            return back()->with('error', 'Gagal: Tagihan ini sudah lunas.');
        }

        if ($request->nominal_bayar > $tagihan->sisa_tagihan) {
            return back()->withInput()->with('error', 'Nominal pembayaran melebihi sisa tagihan!');
        }

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti/pembayaran', 'public');
        }

        DB::transaction(function () use ($request, &$tagihan, $fotoPath) {
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

            $akunKas = AkunKas::find($request->akun_kas_id);
            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => 'masuk',
                'nominal'         => $request->nominal_bayar,
                'keterangan'      => 'Pembayaran tagihan air ' . $tagihan->pelanggan->nama_pelanggan,
                'tanggal'         => $request->tanggal_bayar,
                'dicatat_oleh'    => auth()->id(),
                'pembayaran_id'   => $pembayaran->id,
            ]);

            $akunKas->increment('saldo', $request->nominal_bayar);
        });

        // PANGGIL FUNGSI WA
        $tagihan->refresh();
        $this->kirimNotifikasiWAPembayaran($tagihan, $request->nominal_bayar);

        return redirect()->route('admin.pembayaran.show', $request->tagihan_id)
            ->with('success', 'Pembayaran sebesar Rp ' . number_format($request->nominal_bayar, 0, ',', '.') . ' berhasil dicatat!');
    }

    public function confirm(Request $request, $id)
    {
        $pembayaranPending = Pembayaran::findOrFail($id);
        $tagihan = TagihanAir::findOrFail($pembayaranPending->tagihan_id);

        if ($tagihan->sisa_tagihan <= 0) {
            return back()->with('error', 'Tagihan sudah lunas! Silakan tolak/hapus bukti pending ini.');
        }

        $request->validate([
            'nominal_bayar' => 'required|numeric|min:1|max:' . $tagihan->sisa_tagihan,
            'akun_kas_id'   => 'required|exists:akun_kas,id',
        ]);

        DB::transaction(function () use ($request, $pembayaranPending, &$tagihan) {
            $pembayaranPending->update([
                'nominal_bayar' => $request->nominal_bayar,
                'akun_kas_id'   => $request->akun_kas_id,
                'status_validasi' => 'disetujui',
            ]);

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

            $akunKas = AkunKas::find($request->akun_kas_id);
            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => 'masuk',
                'nominal'         => $request->nominal_bayar,
                'keterangan'      => 'Konfirmasi transfer: ' . $tagihan->pelanggan->nama_pelanggan,
                'tanggal'         => $pembayaranPending->tanggal_bayar,
                'dicatat_oleh'    => auth()->id(),
                'pembayaran_id'   => $pembayaranPending->id,
            ]);

            $akunKas->increment('saldo', $request->nominal_bayar);
        });

        // PANGGIL FUNGSI WA
        $tagihan->refresh();
        $this->kirimNotifikasiWAPembayaran($tagihan, $request->nominal_bayar);

        return back()->with('success', 'Bukti pembayaran berhasil dikonfirmasi dan masuk ke Kas!');
    }

    public function reject($id)
    {
        $pembayaranPending = Pembayaran::findOrFail($id);
        $tagihan = TagihanAir::findOrFail($pembayaranPending->tagihan_id);
        $pelanggan = $tagihan->pelanggan;

        if ($pembayaranPending->foto_bukti) {
            Storage::disk('public')->delete($pembayaranPending->foto_bukti);
            $pembayaranPending->foto_bukti = null;
        }

        $pembayaranPending->update([
            'status_validasi' => 'ditolak',
            'keterangan' => $pembayaranPending->keterangan . ' [DITOLAK ADMIN]'
        ]);

        // Kirim Notifikasi WA ke Pelanggan
        if (!empty($pelanggan->no_hp) && strlen($pelanggan->no_hp) >= 10) {
            $pesan = "Halo, Bpk/Ibu *{$pelanggan->nama_pelanggan}*.\n\n";
            $pesan .= "Mohon maaf, bukti pembayaran tagihan air Anda untuk periode *" . \Carbon\Carbon::create(null, $tagihan->bulan_periode, 1)->isoFormat('MMMM Y') . "* *DITOLAK* oleh Admin.\n\n";
            $pesan .= "Silakan login ke aplikasi PAMSIMAS untuk mengunggah ulang bukti pembayaran yang valid.\n\n";
            $pesan .= "Terima kasih.";

            $this->sendWhatsApp($pelanggan->no_hp, $pesan);
        }

        return back()->with('success', 'Bukti pembayaran ditolak. Status dikembalikan dan pelanggan dapat mengunggah ulang.');
    }

    public function payWithSaldo(Request $request, $tagihanId)
    {
        $tagihan = TagihanAir::findOrFail($tagihanId);
        $pelanggan = $tagihan->pelanggan;
        $nominalPotong = 0;

        if ($pelanggan->saldo <= 0) {
            return back()->with('error', 'Saldo pelanggan tidak mencukupi (Rp 0).');
        }

        DB::transaction(function () use (&$tagihan, $pelanggan, &$nominalPotong) {
            $nominalPotong = min($pelanggan->saldo, $tagihan->sisa_tagihan);

            $pembayaran = Pembayaran::create([
                'tagihan_id'    => $tagihan->id,
                'pelanggan_id'  => $pelanggan->id,
                'nominal_bayar' => $nominalPotong,
                'tanggal_bayar' => now(),
                'keterangan'    => 'Pelunasan via Saldo/Deposit',
                'status_validasi' => 'disetujui',
                'dicatat_oleh'  => auth()->id(),
            ]);

            $tagihan->total_terbayar += $nominalPotong;
            $tagihan->sisa_tagihan   -= $nominalPotong;
            $tagihan->status_bayar    = ($tagihan->sisa_tagihan <= 0) ? 'lunas' : 'mencicil';
            $tagihan->save();

            $pelanggan->decrement('saldo', $nominalPotong);

            ArusKas::create([
                'akun_kas_id'     => 1,
                'jenis_transaksi' => 'masuk',
                'nominal'         => 0,
                'keterangan'      => "Pelunasan Tagihan: {$pelanggan->nama_pelanggan} (Via Saldo - Rp " . number_format($nominalPotong, 0, ',', '.') . ")",
                'tanggal'         => now(),
                'pembayaran_id'   => $pembayaran->id,
                'dicatat_oleh'    => auth()->id(),
            ]);
        });

        // PANGGIL FUNGSI WA
        $tagihan->refresh();
        $this->kirimNotifikasiWAPembayaran($tagihan, $nominalPotong);

        return back()->with('success', 'Tagihan berhasil dibayar menggunakan saldo!');
    }
}
