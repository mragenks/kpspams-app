<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArusKasExport;
use App\Exports\InventarisExport;
use App\Exports\TagihanExport;
use App\Models\ArusKas;
use App\Models\RiwayatInventaris;
use App\Models\TagihanAir;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Tangkap parameter tab dari URL, default ke 'arus-kas' jika kosong
        $tab = $request->get('tab', 'arus-kas');

        // Setup filter tanggal default untuk Arus Kas & Inventaris
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Setup filter bulan/tahun default untuk Tagihan
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');

        // Wadah kosong untuk menyimpan data tabel
        $data = [];

        // HANYA query data sesuai tab yang sedang dibuka (Hemat RAM Server)
        if ($tab == 'arus-kas') {
            $data['arusKas'] = ArusKas::with(['akunKas', 'dicatatOleh'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'desc')
                ->get();
        } elseif ($tab == 'tagihan') {
            $data['tagihan'] = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
                ->where('bulan_periode', $bulan)
                ->where('tahun_periode', $tahun)
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($tab == 'inventaris') {
            $data['inventaris'] = RiwayatInventaris::with(['inventaris', 'dicatatOleh'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'desc')
                ->get();
        }

        return view('admin.laporan.index', compact('tab', 'startDate', 'endDate', 'bulan', 'tahun', 'data'));
    }

    public function exportExcelArusKas(Request $request)
    {
        // Validasi ini HARGA MATI. Jangan biarkan user submit form kosong.
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $fileName = 'Buku_Kas_Pamsimas_' . $request->start_date . '_sd_' . $request->end_date . '.xlsx';

        return Excel::download(new ArusKasExport($request->start_date, $request->end_date), $fileName);
    }

    public function exportExcelTagihan(Request $request)
    {
        // Validasi ketat. Tidak ada filter, tidak ada eksekusi.
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000',
        ]);

        $fileName = 'Laporan_Tagihan_Pamsimas_' . $request->bulan . '_' . $request->tahun . '.xlsx';

        return Excel::download(new TagihanExport($request->bulan, $request->tahun), $fileName);
    }


    public function exportExcelInventaris(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $fileName = 'Laporan_Mutasi_Inventaris_' . $request->start_date . '_sd_' . $request->end_date . '.xlsx';

        return Excel::download(new InventarisExport($request->start_date, $request->end_date), $fileName);
    }
}
