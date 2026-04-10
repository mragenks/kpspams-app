<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Exports\ArusKasExport;
use App\Exports\TagihanExport;
use App\Exports\InventarisExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('ketua.laporan.index');
    }

    public function exportExcelArusKas(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $filename = 'laporan-arus-kas-' . $request->start_date . '-sd-' . $request->end_date . '.xlsx';

        return Excel::download(
            new ArusKasExport($request->start_date, $request->end_date),
            $filename
        );
    }

    public function exportExcelTagihan(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000',
        ]);

        $namaBulan = \Carbon\Carbon::create()->month($request->bulan)->isoFormat('MMMM');
        $filename = 'laporan-tagihan-' . $namaBulan . '-' . $request->tahun . '.xlsx';

        return Excel::download(
            new TagihanExport($request->bulan, $request->tahun),
            $filename
        );
    }

    public function exportExcelInventaris(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $filename = 'laporan-inventaris-' . $request->start_date . '-sd-' . $request->end_date . '.xlsx';

        return Excel::download(
            new InventarisExport($request->start_date, $request->end_date),
            $filename
        );
    }
}
