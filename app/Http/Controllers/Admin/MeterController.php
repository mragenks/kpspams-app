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
                // Kondisi 1: Didaftarkan sebelum atau pada bulan yang difilter
                $query->where('created_at', '<=', $batasWaktuFilter)
                    // Kondisi 2: ATAU punya riwayat tagihan di bulan-bulan sebelumnya (Kasus Backdate)
                    ->orWhereHas('pencatatanMeter', function ($subQuery) use ($tahun, $bulan) {
                        $subQuery->where('tahun_periode', '<', $tahun)
                            ->orWhere(function ($q) use ($tahun, $bulan) {
                                $q->where('tahun_periode', $tahun)
                                    ->where('bulan_periode', '<', $bulan);
                            });
                    });
            })
            // Tetap pastikan dia BELUM dicatat di bulan yang sedang difilter ini
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

        // 1. Cek duplikat di bulan & tahun yang sama (ini sudah benar)
        $exists = PencatatanMeter::where('pelanggan_id', $request->pelanggan_id)
            ->where('bulan_periode', $request->bulan_periode)
            ->where('tahun_periode', $request->tahun_periode)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Pencatatan meter untuk pelanggan ini sudah ada di periode tersebut!');
        }

        // 2. Ambil Data Master Pelanggan dan Pencatatan Paling Terakhir
        $pelanggan = Pelanggan::find($request->pelanggan_id);
        $prevMeter = PencatatanMeter::where('pelanggan_id', $request->pelanggan_id)
            ->orderByDesc('tahun_periode')
            ->orderByDesc('bulan_periode')
            ->first();

        // 3. MENCEGAH BACKDATING (Logika Mesin Waktu)
        if ($prevMeter) {
            // Ubah format input dan database menjadi YYYYMM (ex: 202604)
            $periodeInput = (int) sprintf("%04d%02d", $request->tahun_periode, $request->bulan_periode);
            $periodeTerakhir = (int) sprintf("%04d%02d", $prevMeter->tahun_periode, $prevMeter->bulan_periode);

            if ($periodeInput <= $periodeTerakhir) {
                $namaBulanTerakhir = \Carbon\Carbon::create()->month($prevMeter->bulan_periode)->isoFormat('MMMM');
                return back()->withInput()->with('error', "GAGAL: Tidak dapat mencatat mundur! Pencatatan terakhir adalah {$namaBulanTerakhir} {$prevMeter->tahun_periode}. Anda harus mencatat di bulan setelahnya.");
            }
        }

        // 4. MENCEGAH METERAN MUNDUR (Angka meter akhir tidak boleh lebih kecil dari meter awal)
        $meterAwal = $prevMeter ? $prevMeter->meter_akhir : $pelanggan->meteran_awal;

        if ($request->meter_akhir < $meterAwal) {
            return back()->withInput()->with('error', "GAGAL: Angka meter akhir ({$request->meter_akhir}) tidak masuk akal karena lebih kecil dari meter sebelumnya ({$meterAwal})!");
        }

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto_meteran')) {
            $fotoPath = $request->file('foto_meteran')->store('bukti/meteran', 'public');
        }

        // PERBAIKAN: Tangkap hasil return DB::transaction ke dalam variabel $tagihanTerbuat
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

            // Return hasil tagihan agar bisa dipakai di luar transaksi
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

        // Sekarang kita bisa memanggil notifikasi di sini dengan aman!
        if ($pelanggan->user) {
            $pelanggan->user->notify(new TagihanBaruNotification($tagihanTerbuat, $request->bulan_periode, $request->tahun_periode));
        }

        return redirect()->route('admin.meter.index')
            ->with('success', 'Pencatatan meter berhasil disimpan dan tagihan otomatis terbit!');
    }

    public function show(PencatatanMeter $meter)
    {
        $meter->load(['pelanggan', 'tagihanAir']);
        return view('admin.meter.show', compact('meter'));
    }

    public function destroy(PencatatanMeter $meter)
    {
        // Hapus tagihan terkait juga
        $meter->tagihanAir()->delete();
        $meter->delete();

        return redirect()->route('admin.meter.index')
            ->with('success', 'Data pencatatan meter berhasil dihapus!');
    }
}
