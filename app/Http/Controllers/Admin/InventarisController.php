<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\RiwayatInventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->jenis ?? '';
        $kondisi = $request->kondisi ?? '';

        $inventaris = Inventaris::query()
            ->when($jenis, fn($q) => $q->where('jenis_aset', $jenis))
            ->when($kondisi, fn($q) => $q->where('kondisi', $kondisi))
            ->latest()
            ->paginate(10);

        $totalTetap = Inventaris::where('jenis_aset', 'tetap')->count();
        $totalTidakTetap = Inventaris::where('jenis_aset', 'tidak_tetap')->count();
        $totalRusak = Inventaris::where('kondisi', 'rusak')->count();

        return view('admin.inventaris.index', compact(
            'inventaris',
            'jenis',
            'kondisi',
            'totalTetap',
            'totalTidakTetap',
            'totalRusak'
        ));
    }

    public function create()
    {
        $akunKas = \App\Models\AkunKas::where('is_active', true)->get();
        return view('admin.inventaris.create', compact('akunKas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang'       => 'required|string|max:255',
            'jenis_aset'        => 'required|in:tetap,tidak_tetap',
            'jumlah'            => 'required|integer|min:1',
            'kondisi'           => 'required|in:baik,rusak,perlu_perbaikan',
            'lokasi'            => 'nullable|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'keterangan'        => 'nullable|string',
            'stok_minimum'      => 'required|integer|min:0',
            'akun_kas_id'       => 'required_if:catat_pengeluaran,1|nullable|exists:akun_kas,id',
            'harga_perolehan'   => 'required_if:catat_pengeluaran,1|nullable|numeric|min:0',
            'foto_bukti'        => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'akun_kas_id.required_if' => 'Akun Kas wajib dipilih jika mencatat pengeluaran kas.',
            'harga_perolehan.required_if' => 'Harga perolehan wajib diisi jika mencatat pengeluaran kas.',
            'foto_bukti.image' => 'Berkas bukti harus berupa gambar.',
            'foto_bukti.mimes' => 'Format gambar bukti harus jpeg, png, atau jpg.',
            'foto_bukti.max' => 'Ukuran gambar bukti maksimal 5MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti/arus-kas', 'public');
        }

        $tanggalPerolehanCombined = $request->tanggal_perolehan . ' ' . date('H:i:s');

        DB::transaction(function () use ($request, $fotoPath, $tanggalPerolehanCombined) {
            $prefix = $request->jenis_aset === 'tetap' ? 'AT' : 'ATT';
            $lastItem = Inventaris::where('jenis_aset', $request->jenis_aset)
                ->latest('id')->first();
            $newNumber = $lastItem
                ? (intval(substr($lastItem->kode_barang, strlen($prefix) + 1)) + 1)
                : 1;
            $kodeBarang = $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            $inventaris = Inventaris::create([
                'kode_barang'       => $kodeBarang,
                'nama_barang'       => $request->nama_barang,
                'jenis_aset'        => $request->jenis_aset,
                'stok_awal'         => $request->jumlah,
                'stok_saat_ini'     => $request->jumlah,
                'kondisi'           => $request->kondisi,
                'lokasi'            => $request->lokasi,
                'tanggal_perolehan' => $tanggalPerolehanCombined,
                'keterangan'        => $request->keterangan,
                'stok_minimum'      => $request->stok_minimum,
            ]);

            // Catat riwayat
            RiwayatInventaris::create([
                'inventaris_id' => $inventaris->id,
                'jenis_mutasi'  => 'masuk',
                'jumlah'        => $request->jumlah,
                'stok_sebelum'  => 0,
                'stok_sesudah'  => $request->jumlah,
                'keterangan'    => 'Penambahan aset baru',
                'tanggal'       => date('Y-m-d', strtotime($tanggalPerolehanCombined)),
                'dicatat_oleh'  => auth()->id(),
            ]);

            if ($request->catat_pengeluaran == '1') {
                $noBukti = $this->generateNoBukti('keluar', $tanggalPerolehanCombined);
                \App\Models\ArusKas::create([
                    'akun_kas_id'     => $request->akun_kas_id,
                    'jenis_transaksi' => 'keluar',
                    'nominal'         => $request->harga_perolehan,
                    'keterangan'      => 'Pembelian Aset: ' . $request->nama_barang . ' (' . $kodeBarang . ')',
                    'no_bukti'        => $noBukti,
                    'foto_bukti'      => $fotoPath,
                    'tanggal'         => date('Y-m-d', strtotime($tanggalPerolehanCombined)),
                    'dicatat_oleh'    => auth()->id(),
                    'created_at'      => $tanggalPerolehanCombined,
                ]);

                // Update Saldo Kas
                \App\Models\AkunKas::find($request->akun_kas_id)->decrement('saldo', $request->harga_perolehan);
            }
        });

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Aset berhasil ditambahkan!');
    }

    public function show(Inventaris $inventaris)
    {
        $inventaris->load('riwayat');
        $akunKas = \App\Models\AkunKas::where('is_active', true)->get();
        return view('admin.inventaris.show', compact('inventaris', 'akunKas'));
    }

    public function edit(Inventaris $inventaris)
    {
        return view('admin.inventaris.edit', compact('inventaris'));
    }

    public function update(Request $request, Inventaris $inventaris)
    {
        $request->validate([
            'nama_barang'       => 'required|string|max:255',
            'kondisi'           => 'required|in:baik,rusak,perlu_perbaikan',
            'lokasi'            => 'nullable|string|max:255',
            'tanggal_perolehan' => 'nullable|date',
            'keterangan'        => 'nullable|string',
            'stok_minimum'      => 'required|integer|min:0',
        ]);

        $inventaris->update($request->only([
            'nama_barang',
            'kondisi',
            'lokasi',
            'tanggal_perolehan',
            'keterangan',
            'stok_minimum'
        ]));

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Data aset berhasil diupdate!');
    }

    public function mutasiStok(Request $request, Inventaris $inventaris)
    {
        $request->validate([
            'jenis_mutasi'      => 'required|in:masuk,keluar,pengembalian',
            'jumlah'            => 'required|integer|min:1',
            'keterangan'        => 'required|string|max:255',
            'tanggal'           => 'required|date',
            'akun_kas_id'       => 'required_if:catat_pengeluaran,1|nullable|exists:akun_kas,id',
            'harga_perolehan'   => 'required_if:catat_pengeluaran,1|nullable|numeric|min:0',
            'foto_bukti'        => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'akun_kas_id.required_if' => 'Akun Kas wajib dipilih jika mencatat pengeluaran kas.',
            'harga_perolehan.required_if' => 'Harga perolehan wajib diisi jika mencatat pengeluaran kas.',
            'foto_bukti.image' => 'Berkas bukti harus berupa gambar.',
            'foto_bukti.mimes' => 'Format gambar bukti harus jpeg, png, atau jpg.',
            'foto_bukti.max' => 'Ukuran gambar bukti maksimal 5MB.',
        ]);

        if (
            $request->jenis_mutasi === 'keluar' &&
            $inventaris->stok_saat_ini < $request->jumlah
        ) {
            return back()->with('error', 'Stok tidak mencukupi! Sisa stok saat ini hanya ' . $inventaris->stok_saat_ini . ' unit.');
        }

        DB::transaction(function () use ($request, $inventaris) {
            $stokSebelum = $inventaris->stok_saat_ini;

            $stokSesudah = in_array($request->jenis_mutasi, ['masuk', 'pengembalian'])
                ? $stokSebelum + $request->jumlah
                : $stokSebelum - $request->jumlah;

            RiwayatInventaris::create([
                'inventaris_id' => $inventaris->id,
                'jenis_mutasi'  => $request->jenis_mutasi,
                'jumlah'        => $request->jumlah,
                'stok_sebelum'  => $stokSebelum,
                'stok_sesudah'  => $stokSesudah,
                'keterangan'    => $request->keterangan,
                'tanggal'       => $request->tanggal,
                'dicatat_oleh'  => auth()->id(),
            ]);

            $inventaris->update(['stok_saat_ini' => $stokSesudah]);

            if ($request->jenis_mutasi === 'masuk' && $request->catat_pengeluaran == '1') {
                $fotoPath = null;
                if ($request->hasFile('foto_bukti')) {
                    $fotoPath = $request->file('foto_bukti')->store('bukti/arus-kas', 'public');
                }

                $tanggalCombined = $request->tanggal . ' ' . date('H:i:s');
                $noBukti = $this->generateNoBukti('keluar', $tanggalCombined);

                \App\Models\ArusKas::create([
                    'akun_kas_id'     => $request->akun_kas_id,
                    'jenis_transaksi' => 'keluar',
                    'nominal'         => $request->harga_perolehan,
                    'keterangan'      => 'Pembelian Aset (Mutasi Masuk): ' . $inventaris->nama_barang . ' (' . $inventaris->kode_barang . ') - ' . $request->keterangan,
                    'no_bukti'        => $noBukti,
                    'foto_bukti'      => $fotoPath,
                    'tanggal'         => $request->tanggal,
                    'dicatat_oleh'    => auth()->id(),
                    'created_at'      => $tanggalCombined,
                ]);

                // Update Saldo Kas
                \App\Models\AkunKas::find($request->akun_kas_id)->decrement('saldo', $request->harga_perolehan);
            }
        });

        return redirect()->route('admin.inventaris.show', $inventaris)
            ->with('success', 'Mutasi stok berhasil dicatat!');
    }

    public function destroy(Inventaris $inventaris)
    {
        $inventaris->riwayat()->delete();
        $inventaris->delete();

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Aset berhasil dihapus!');
    }

    private function generateNoBukti($jenis, $tanggal)
    {
        $dateStr = date('Ymd', strtotime($tanggal));
        
        $prefixCode = 'MUT';
        if ($jenis === 'masuk') {
            $prefixCode = 'MAS';
        } elseif ($jenis === 'keluar') {
            $prefixCode = 'KEL';
        }
        
        $prefix = $prefixCode . '-' . $dateStr . '-';

        $latest = \App\Models\ArusKas::where('no_bukti', 'like', $prefix . '%')
            ->orderBy('no_bukti', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = intval(substr($latest->no_bukti, strlen($prefix)));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
