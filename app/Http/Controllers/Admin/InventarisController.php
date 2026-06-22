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
        return view('admin.inventaris.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang'       => 'required|string|max:255',
            'jenis_aset'        => 'required|in:tetap,tidak_tetap',
            'jumlah'            => 'required|integer|min:1',
            'kondisi'           => 'required|in:baik,rusak,perlu_perbaikan',
            'lokasi'            => 'nullable|string|max:255',
            'tanggal_perolehan' => 'nullable|date',
            'keterangan'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
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
                'tanggal_perolehan' => $request->tanggal_perolehan,
                'keterangan'        => $request->keterangan,
            ]);

            // Catat riwayat
            RiwayatInventaris::create([
                'inventaris_id' => $inventaris->id,
                'jenis_mutasi'  => 'masuk',
                'jumlah'        => $request->jumlah,
                'stok_sebelum'  => 0,
                'stok_sesudah'  => $request->jumlah,
                'keterangan'    => 'Penambahan aset baru',
                'tanggal'       => now()->toDateString(),
                'dicatat_oleh'  => auth()->id(),
            ]);
        });

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Aset berhasil ditambahkan!');
    }

    public function show(Inventaris $inventaris)
    {
        $inventaris->load('riwayat');
        return view('admin.inventaris.show', compact('inventaris'));
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
        ]);

        $inventaris->update($request->only([
            'nama_barang',
            'kondisi',
            'lokasi',
            'tanggal_perolehan',
            'keterangan'
        ]));

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Data aset berhasil diupdate!');
    }

    public function mutasiStok(Request $request, Inventaris $inventaris)
    {
        $request->validate([
            'jenis_mutasi' => 'required|in:masuk,keluar,pengembalian',
            'jumlah'       => 'required|integer|min:1',
            'keterangan'   => 'required|string|max:255',
            'tanggal'      => 'required|date',
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
}
