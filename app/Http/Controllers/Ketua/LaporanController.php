<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Exports\ArusKasExport;
use App\Exports\TagihanExport;
use App\Exports\InventarisExport;
use App\Models\ArusKas;
use App\Models\RiwayatInventaris;
use App\Models\TagihanAir;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'arus-kas');

        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        $data = [];

        if ($tab == 'arus-kas') {
            $data['arusKas'] = ArusKas::with('akunKas')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'asc')
                ->paginate(15);
        } elseif ($tab == 'tagihan') {
            $searchPelanggan = $request->search_pelanggan;

            $query = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereHas('pencatatanMeter', function ($sub) use ($startDate, $endDate) {
                        $sub->whereBetween('tanggal_pencatatan', [$startDate, $endDate]);
                    })->orWhere(function ($sub2) use ($startDate, $endDate) {
                        $sub2->doesntHave('pencatatanMeter')
                            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    });
                });

            if ($searchPelanggan) {
                $query->whereHas('pelanggan', function ($q) use ($searchPelanggan) {
                    $q->where('nama_pelanggan', 'like', '%' . $searchPelanggan . '%')
                        ->orWhere('kode_pelanggan', 'like', '%' . $searchPelanggan . '%');
                });
            }

            $data['tagihan'] = $query->orderBy('created_at', 'desc')->paginate(15);
            $data['searchPelanggan'] = $searchPelanggan;
        } elseif ($tab == 'inventaris') {
            $data['inventaris'] = RiwayatInventaris::with(['inventaris', 'dicatatOleh'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'desc')
                ->get();
        }

        return view('ketua.laporan.index', compact('tab', 'startDate', 'endDate', 'data'));
    }

    // ================= EXCEL EXPORTS =================
    public function exportExcelArusKas(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date']);
        return Excel::download(new ArusKasExport($request->start_date, $request->end_date), 'laporan-arus-kas-' . $request->start_date . '-sd-' . $request->end_date . '.xlsx');
    }

    public function exportExcelTagihan(Request $request)
    {
        // Tagihan Excel memakai start_date dan end_date seperti Admin
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date']);
        return Excel::download(new TagihanExport($request->start_date, $request->end_date), 'laporan-tagihan-' . $request->start_date . '-sd-' . $request->end_date . '.xlsx');
    }

    public function exportExcelInventaris(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date']);
        return Excel::download(new InventarisExport($request->start_date, $request->end_date), 'laporan-inventaris-' . $request->start_date . '-sd-' . $request->end_date . '.xlsx');
    }

    // ================= PDF EXPORTS =================
    public function exportPDF(Request $request)
    {
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();

        $arusKas = ArusKas::with('akunKas')->whereBetween('tanggal', [$startDate, $endDate])->orderBy('tanggal', 'asc')->get();
        $saldoAwal = ArusKas::where('tanggal', '<', $startDate)->selectRaw("SUM(CASE WHEN jenis_transaksi IN ('masuk', 'mutasi_masuk') THEN nominal ELSE -nominal END) as total")->first()->total ?? 0;

        $totalMasuk = $arusKas->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])->sum('nominal');
        $totalKeluar = $arusKas->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])->sum('nominal');

        $data = [
            'arusKas' => $arusKas,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMM Y'),
            'saldoAwal' => $saldoAwal,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAkhir' => $saldoAwal + $totalMasuk - $totalKeluar,
            'tanggalCetak' => Carbon::now()->isoFormat('D MMMM Y')
        ];

        // Kita bisa me-reuse template PDF milik Admin!
        $pdf = Pdf::loadView('admin.laporan.pdf_aruskas', $data)->setPaper('a4', 'landscape');
        return $pdf->stream("Laporan_ArusKas_{$startDate}_sd_{$endDate}.pdf");
    }

    public function exportTagihanPDF(Request $request)
    {
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();
        $searchPelanggan = $request->search_pelanggan;

        $query = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereHas('pencatatanMeter', function ($sub) use ($startDate, $endDate) {
                    $sub->whereBetween('tanggal_pencatatan', [$startDate, $endDate]);
                })->orWhere(function ($sub2) use ($startDate, $endDate) {
                    $sub2->doesntHave('pencatatanMeter')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            });

        if ($searchPelanggan) {
            $query->whereHas('pelanggan', function ($q) use ($searchPelanggan) {
                $q->where('nama_pelanggan', 'like', '%' . $searchPelanggan . '%')->orWhere('kode_pelanggan', 'like', '%' . $searchPelanggan . '%');
            });
        }

        $tagihan = $query->orderBy('created_at', 'asc')->get();

        $data = [
            'tagihan' => $tagihan,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMM Y'),
            'searchPelanggan' => $searchPelanggan,
            'totalTagihan' => $tagihan->sum('total_tagihan'),
            'totalTerbayar' => $tagihan->sum('total_terbayar'),
            'totalTunggakan' => $tagihan->sum('sisa_tagihan'),
        ];

        $pdf = Pdf::loadView('admin.laporan.pdf_tagihan', $data)->setPaper('a4', 'landscape');
        return $pdf->stream("Laporan_Tagihan_{$startDate}_sd_{$endDate}.pdf");
    }

    public function exportInventarisPDF(Request $request)
    {
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();

        $inventaris = RiwayatInventaris::with('inventaris')->whereBetween('tanggal', [$startDate, $endDate])->orderBy('tanggal', 'asc')->get();

        $data = [
            'inventaris' => $inventaris,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMM Y'),
        ];

        $pdf = Pdf::loadView('admin.laporan.pdf_inventaris', $data)->setPaper('a4', 'portrait');
        return $pdf->stream("Laporan_Mutasi_Inventaris.pdf");
    }
}
