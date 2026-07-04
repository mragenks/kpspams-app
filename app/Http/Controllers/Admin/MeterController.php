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
use Illuminate\Support\Facades\Http;

class MeterController extends Controller
{
    public function index(Request $request)
    {
        $startPeriode = $request->start_periode ?? date('Y-m');
        $endPeriode = $request->end_periode ?? date('Y-m');
        $search = $request->search;

        if ($startPeriode > $endPeriode) {
            $temp = $startPeriode;
            $startPeriode = $endPeriode;
            $endPeriode = $temp;
        }

        $pencatatan = PencatatanMeter::with(['pelanggan', 'tagihanAir'])
            ->whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$startPeriode, $endPeriode])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('pelanggan', function ($q) use ($search) {
                    $q->where('kode_pelanggan', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('nama_lengkap', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->orderBy('tahun_periode', 'desc')
            ->orderBy('bulan_periode', 'desc')
            ->paginate(10)
            ->withQueryString();

        $endYear = (int) substr($endPeriode, 0, 4);
        $endMonth = (int) substr($endPeriode, 5, 2);

        $batasWaktuFilter = \Carbon\Carbon::create($endYear, $endMonth)->endOfMonth();

        $pelangganBelumDicatat = Pelanggan::where('status', 'aktif')
            ->where(function ($query) {
                $query->doesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('is_active', true);
                    });
            })
            ->where('created_at', '<=', $batasWaktuFilter)
            ->whereDoesntHave('pencatatanMeter', function ($q) use ($endMonth, $endYear) {
                $q->where('bulan_periode', $endMonth)
                    ->where('tahun_periode', $endYear);
            })->get();

        return view('admin.meter.index', compact(
            'pencatatan',
            'pelangganBelumDicatat',
            'startPeriode',
            'endPeriode',
            'endMonth',
            'endYear',
            'search'
        ));
    }

    public function create(Request $request)
    {
        $pelanggan = Pelanggan::where('status', 'aktif')
            ->where(function ($query) {
                $query->doesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('is_active', true);
                    });
            })
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
        ], [
            'pelanggan_id.required'       => 'Pelanggan wajib dipilih!',
            'pelanggan_id.exists'         => 'Pelanggan tidak valid!',
            'bulan_periode.required'      => 'Bulan periode wajib diisi!',
            'tahun_periode.required'      => 'Tahun periode wajib diisi!',
            'meter_akhir.required'        => 'Angka meter akhir wajib diisi!',
            'meter_akhir.numeric'         => 'Angka meter akhir harus berupa angka!',
            'meter_akhir.min'             => 'Angka meter akhir tidak boleh kurang dari 0!',
            'tanggal_pencatatan.required' => 'Tanggal pencatatan wajib dipilih!',
            'tanggal_pencatatan.date'     => 'Format tanggal tidak valid!',
            'foto_meteran.image'          => 'File harus berupa gambar!',
            'foto_meteran.mimes'          => 'Format foto harus JPG, JPEG, atau PNG!',
            'foto_meteran.max'            => 'Ukuran foto maksimal 2MB!',
        ]);

        $pelanggan = Pelanggan::findOrFail($request->pelanggan_id);
        if (
            strtolower($pelanggan->status) !== 'aktif' ||
            ($pelanggan->user && !$pelanggan->user->is_active)
        ) {
            return back()->withInput()->with('error', 'GAGAL: Pelanggan ini atau akun loginnya sudah dinonaktifkan!');
        }

        $exists = PencatatanMeter::where('pelanggan_id', $request->pelanggan_id)
            ->where('bulan_periode', $request->bulan_periode)
            ->where('tahun_periode', $request->tahun_periode)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Pencatatan meter untuk pelanggan ini sudah ada di periode tersebut!');
        }

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

        if ($pelanggan->user) {
            $pelanggan->user->notify(new TagihanBaruNotification($tagihanTerbuat, $request->bulan_periode, $request->tahun_periode));
        }

        if ($pelanggan->no_hp) {
            $namaBulan = \Carbon\Carbon::create()->month($request->bulan_periode)->isoFormat('MMMM');
            $nominal = number_format($tagihanTerbuat->total_tagihan, 0, ',', '.');
            $volume = $tagihanTerbuat->pencatatanMeter->volume_m3;

            $pesanWA = "Halo, Bpk/Ibu *{$pelanggan->nama_pelanggan}*.\n\n";
            $pesanWA .= "Tagihan air PAMSIMAS Tirta Wening untuk periode *{$namaBulan} {$request->tahun_periode}* telah terbit.\n\n";
            $pesanWA .= "📊 *Rincian Pemakaian:*\n";
            $pesanWA .= "💧 Volume: *{$volume} m³*\n";
            $pesanWA .= "💰 Total Tagihan: *Rp {$nominal}*\n\n";
            $pesanWA .= "Silakan lakukan pembayaran melalui kasir atau transfer ke rekening berikut:\n\n";
            $pesanWA .= "🏦 *BANK Jateng*\n";
            $pesanWA .= "💳 No. Rek: *3011 0333 25*\n";
            $pesanWA .= "👤 A.N: *Puji Hastutiningsih*\n\n";
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
        $request->validate([
            'meter_akhir'  => 'required|numeric|min:' . $meter->meter_awal,
            'foto_meteran' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'meter_akhir.required' => 'Angka meter akhir wajib diisi!',
            'meter_akhir.numeric'  => 'Angka meter akhir harus berupa angka!',
            'meter_akhir.min'      => "Angka meter akhir tidak boleh lebih kecil dari meter awal ({$meter->meter_awal})!",
            'foto_meteran.image'   => 'File harus berupa gambar!',
            'foto_meteran.mimes'   => 'Format foto harus JPG, JPEG, atau PNG!',
            'foto_meteran.max'     => 'Ukuran foto maksimal 2MB!',
        ]);

        DB::transaction(function () use ($request, $meter) {
            if ($request->hasFile('foto_meteran')) {
                if ($meter->foto_meteran) {
                    Storage::disk('public')->delete($meter->foto_meteran);
                }
                $fotoPath = $request->file('foto_meteran')->store('bukti/meteran', 'public');
                $meter->foto_meteran = $fotoPath;
            }

            $meter->meter_akhir = $request->meter_akhir;
            $meter->save();

            $tagihan = $meter->tagihanAir;
            if ($tagihan) {
                $biayaPemakaian = $meter->hitungBiayaPemakaian();
                $totalTagihan = $tagihan->biaya_beban + $biayaPemakaian;

                $tagihan->biaya_pemakaian = $biayaPemakaian;
                $tagihan->total_tagihan = $totalTagihan;
                $tagihan->sisa_tagihan = $totalTagihan - $tagihan->total_terbayar;

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

    private function sendWhatsApp($no_hp, $pesan)
    {
        $token = config('services.fonnte.token');

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
            'target' => $no_hp,
            'message' => $pesan,
            'delay' => '2',
        ]);

        return $response->json();
    }
}
