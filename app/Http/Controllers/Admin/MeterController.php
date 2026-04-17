<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PencatatanMeter;
use App\Models\Pelanggan;
use App\Models\PengaturanTarif;
use App\Models\TagihanAir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\TagihanBaruNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http; // <-- WAJIB TAMBAHKAN INI UNTUK API

class MeterController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');

        $pencatatan = PencatatanMeter::with(['pelanggan', 'tagihanAir'])
            ->where('bulan_periode', $bulan)
            ->where('tahun_periode', $tahun)
            ->latest()
            ->paginate(10);

        $batasWaktuFilter = \Carbon\Carbon::create($tahun, $bulan)->endOfMonth();

        // LOGIKA PENYEMPURNAAN
        $pelangganBelumDicatat = Pelanggan::where('status', 'aktif')
            ->where(function ($query) use ($batasWaktuFilter, $tahun, $bulan) {
                $query->where('created_at', '<=', $batasWaktuFilter)
                    ->orWhereHas('pencatatanMeter', function ($subQuery) use ($tahun, $bulan) {
                        $subQuery->where('tahun_periode', '<', $tahun)
                            ->orWhere(function ($q) use ($tahun, $bulan) {
                                $q->where('tahun_periode', $tahun)
                                    ->where('bulan_periode', '<', $bulan);
                            });
                    });
            })
            ->whereDoesntHave('pencatatanMeter', function ($q) use ($bulan, $tahun) {
                $q->where('bulan_periode', $bulan)
                    ->where('tahun_periode', $tahun);
            })->get();

        return view('admin.meter.index', compact(
            'pencatatan',
            'pelangganBelumDicatat',
            'bulan',
            'tahun'
        ));
    }

    public function create(Request $request)
    {
        $pelanggan = Pelanggan::where('status', 'aktif')
            ->with(['pencatatanMeter' => function ($query) {
                $query->latest('tahun_periode')->latest('bulan_periode');
            }])
            ->get()
            ->map(function ($p) {
                $pencatatanTerakhir = $p->pencatatanMeter->first();

                $p->meter_terakhir = $pencatatanTerakhir ? $pencatatanTerakhir->meter_akhir : $p->meteran_awal;
                $p->bulan_terakhir = $pencatatanTerakhir ? $pencatatanTerakhir->bulan_periode : 0;
                $p->tahun_terakhir = $pencatatanTerakhir ? $pencatatanTerakhir->tahun_periode : 0;

                return $p;
            });

        $selectedPelanggan = null;
        if ($request->pelanggan_id) {
            $selectedPelanggan = Pelanggan::find($request->pelanggan_id);
        }

        // Ambil data tarif dari database
        $tarif = PengaturanTarif::first();

        return view('admin.meter.create', compact('pelanggan', 'selectedPelanggan', 'tarif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id'       => 'required|exists:pelanggan,id',
            'bulan_periode'      => 'required|integer|min:1|max:12',
            'tahun_periode'      => 'required|integer|min:2020',
            'meter_akhir'        => 'required|numeric|min:0',
            'tanggal_pencatatan' => 'required|date',
            'foto_meteran'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $exists = PencatatanMeter::where('pelanggan_id', $request->pelanggan_id)
            ->where('bulan_periode', $request->bulan_periode)
            ->where('tahun_periode', $request->tahun_periode)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Pencatatan meter untuk pelanggan ini sudah ada di periode tersebut!');
        }

        $pelanggan = Pelanggan::find($request->pelanggan_id);
        $prevMeter = PencatatanMeter::where('pelanggan_id', $request->pelanggan_id)
            ->orderByDesc('tahun_periode')
            ->orderByDesc('bulan_periode')
            ->first();

        if ($prevMeter) {
            $periodeInput = (int) sprintf("%04d%02d", $request->tahun_periode, $request->bulan_periode);
            $periodeTerakhir = (int) sprintf("%04d%02d", $prevMeter->tahun_periode, $prevMeter->bulan_periode);

            if ($periodeInput <= $periodeTerakhir) {
                $namaBulanTerakhir = \Carbon\Carbon::create()->month($prevMeter->bulan_periode)->isoFormat('MMMM');
                return back()->withInput()->with('error', "GAGAL: Tidak dapat mencatat mundur! Pencatatan terakhir adalah {$namaBulanTerakhir} {$prevMeter->tahun_periode}. Anda harus mencatat di bulan setelahnya.");
            }
        }

        $meterAwal = $prevMeter ? $prevMeter->meter_akhir : $pelanggan->meteran_awal;

        if ($request->meter_akhir < $meterAwal) {
            return back()->withInput()->with('error', "GAGAL: Angka meter akhir ({$request->meter_akhir}) tidak masuk akal karena lebih kecil dari meter sebelumnya ({$meterAwal})!");
        }

        $fotoPath = null;
        if ($request->hasFile('foto_meteran')) {
            $fotoPath = $request->file('foto_meteran')->store('bukti/meteran', 'public');
        }

        $tagihanTerbuat = DB::transaction(function () use ($request, $meterAwal, $pelanggan, $fotoPath) {
            $pencatatan = PencatatanMeter::create([
                'pelanggan_id'       => $request->pelanggan_id,
                'bulan_periode'      => $request->bulan_periode,
                'tahun_periode'      => $request->tahun_periode,
                'meter_awal'         => $meterAwal,
                'meter_akhir'        => $request->meter_akhir,
                'tanggal_pencatatan' => $request->tanggal_pencatatan,
                'foto_meteran'       => $fotoPath,
                'dicatat_oleh'       => auth()->id(),
            ]);

            $biayaPemakaian = $pencatatan->hitungBiayaPemakaian();
            $biayaBeban = \App\Models\PengaturanTarif::first()->biaya_beban ?? 5000;
            $totalTagihan = $biayaPemakaian + $biayaBeban;

            return TagihanAir::create([
                'pelanggan_id'        => $request->pelanggan_id,
                'pencatatan_meter_id' => $pencatatan->id,
                'bulan_periode'       => $request->bulan_periode,
                'tahun_periode'       => $request->tahun_periode,
                'biaya_beban'         => $biayaBeban,
                'biaya_pemakaian'     => $biayaPemakaian,
                'total_tagihan'       => $totalTagihan,
                'total_terbayar'      => 0,
                'sisa_tagihan'        => $totalTagihan,
                'status_bayar'        => 'belum_lunas',
            ]);
        });

        // 1. Kirim Notifikasi Lonceng (In-App)
        if ($pelanggan->user) {
            $pelanggan->user->notify(new TagihanBaruNotification($tagihanTerbuat, $request->bulan_periode, $request->tahun_periode));
        }

        // 2. Kirim Notifikasi WhatsApp via Fonnte
        if ($pelanggan->no_hp) {
            $namaBulan = \Carbon\Carbon::create()->month($request->bulan_periode)->isoFormat('MMMM');
            $nominal = number_format($tagihanTerbuat->total_tagihan, 0, ',', '.');
            $volume = $tagihanTerbuat->pencatatanMeter->volume_m3;

            // FORMAT PESAN DENGAN NOMOR REKENING
            $pesanWA = "Halo, Bpk/Ibu *{$pelanggan->nama_pelanggan}*.\n\n";
            $pesanWA .= "Tagihan air PAMSIMAS Tirta Wening untuk periode *{$namaBulan} {$request->tahun_periode}* telah terbit.\n\n";
            $pesanWA .= "📊 *Rincian Pemakaian:*\n";
            $pesanWA .= "💧 Volume: *{$volume} m³*\n";
            $pesanWA .= "💰 Total Tagihan: *Rp {$nominal}*\n\n";
            $pesanWA .= "Silakan lakukan pembayaran melalui kasir atau transfer ke rekening berikut:\n\n";
            $pesanWA .= "🏦 *BANK BRI*\n";
            $pesanWA .= "💳 No. Rek: *6892-01-023456-53-1*\n";
            $pesanWA .= "👤 A.N: *siapa ya?*\n\n";
            $pesanWA .= "Setelah melakukan transfer, mohon upload foto bukti pembayaran di Portal Pelanggan.\n\n";
            $pesanWA .= "Terima kasih. 🙏";

            $this->sendWhatsApp($pelanggan->no_hp, $pesanWA);
        }

        return redirect()->route('admin.meter.index')
            ->with('success', 'Pencatatan meter berhasil disimpan dan tagihan otomatis terbit (Notifikasi WA terkirim)!');
    }

    public function show(PencatatanMeter $meter)
    {
        $meter->load(['pelanggan', 'tagihanAir']);
        return view('admin.meter.show', compact('meter'));
    }

    public function edit(PencatatanMeter $meter)
    {
        $meter->load('pelanggan');
        return view('admin.meter.edit', compact('meter'));
    }

    public function update(Request $request, PencatatanMeter $meter)
    {
        // 1. Validasi input
        $request->validate([
            'meter_akhir'  => 'required|numeric|min:' . $meter->meter_awal,
            'foto_meteran' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'meter_akhir.min' => "Angka meter akhir tidak boleh lebih kecil dari meter awal ({$meter->meter_awal})!"
        ]);

        DB::transaction(function () use ($request, $meter) {
            // 2. Update Foto (Opsional: Jika ada foto baru yang diupload)
            if ($request->hasFile('foto_meteran')) {
                // Hapus foto lama jika ada
                if ($meter->foto_meteran) {
                    Storage::disk('public')->delete($meter->foto_meteran);
                }
                $fotoPath = $request->file('foto_meteran')->store('bukti/meteran', 'public');
                $meter->foto_meteran = $fotoPath;
            }

            // 3. Update Angka Meter
            $meter->meter_akhir = $request->meter_akhir;
            $meter->save();

            // 4. Hitung Ulang Tagihan
            $tagihan = $meter->tagihanAir;
            if ($tagihan) {
                // Hitung ulang biaya berdasarkan meteran yang baru
                $biayaPemakaian = $meter->hitungBiayaPemakaian();
                $totalTagihan = $tagihan->biaya_beban + $biayaPemakaian;

                $tagihan->biaya_pemakaian = $biayaPemakaian;
                $tagihan->total_tagihan = $totalTagihan;

                // Update sisa tagihan (Total Tagihan Baru - Yang Sudah Dibayar)
                $tagihan->sisa_tagihan = $totalTagihan - $tagihan->total_terbayar;

                // Sesuaikan kembali status pembayaran jika berubah
                if ($tagihan->sisa_tagihan <= 0 && $tagihan->total_tagihan > 0) {
                    $tagihan->status_bayar = 'lunas';
                    $tagihan->sisa_tagihan = 0;
                } elseif ($tagihan->total_terbayar > 0 && $tagihan->sisa_tagihan > 0) {
                    $tagihan->status_bayar = 'cicil';
                } else {
                    $tagihan->status_bayar = 'belum_lunas';
                }

                $tagihan->save();
            }
        });

        return redirect()->route('admin.meter.show', $meter->id)
            ->with('success', 'Angka meteran berhasil diperbarui dan Tagihan telah dihitung ulang otomatis!');
    }

    public function destroy(PencatatanMeter $meter)
    {
        $meter->tagihanAir()->delete();
        $meter->delete();

        return redirect()->route('admin.meter.index')
            ->with('success', 'Data pencatatan meter berhasil dihapus!');
    }

    // ==========================================
    // FUNGSI HELPER UNTUK WHATSAPP (FONNTE)
    // ==========================================
    private function sendWhatsApp($no_hp, $pesan)
    {
        // GANTI INI DENGAN TOKEN DARI FONNTE KAMU
        $token = config('services.fonnte.token');

        // Pastikan format nomor HP diawali dengan 08 atau 628 (Fonnte bisa membaca keduanya)
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
            'target' => $no_hp,
            'message' => $pesan,
            'delay' => '2', // Delay 2 detik agar tidak dibanned oleh WhatsApp
        ]);

        return $response->json();
    }
}
