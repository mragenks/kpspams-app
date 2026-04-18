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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'arus-kas');

        // Untuk Arus Kas & Inventaris
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        // Khusus Laporan Tagihan (Bulan & Tahun)
        $startPeriode = $request->start_periode ?: Carbon::now()->format('Y-m');
        $endPeriode = $request->end_periode ?: Carbon::now()->format('Y-m');

        $data = [];

        if ($tab == 'arus-kas') {
            $data['arusKas'] = ArusKas::with('akunKas')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'asc')
                ->paginate(15);
        } elseif ($tab == 'tagihan') {
            $searchPelanggan = $request->search_pelanggan;

            // LOGIKA BARU: Filter 100% murni berdasarkan Periode Tagihan (Bulan & Tahun)
            $query = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
                ->whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);

            if ($searchPelanggan) {
                $query->whereHas('pelanggan', function ($q) use ($searchPelanggan) {
                    $q->where('nama_pelanggan', 'like', '%' . $searchPelanggan . '%')
                        ->orWhere('kode_pelanggan', 'like', '%' . $searchPelanggan . '%');
                });
            }

            $data['tagihan'] = $query->orderBy('tahun_periode', 'desc')->orderBy('bulan_periode', 'desc')->paginate(15);
            $data['searchPelanggan'] = $searchPelanggan;
        } elseif ($tab == 'inventaris') {
            $data['inventaris'] = RiwayatInventaris::with(['inventaris', 'dicatatOleh'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'desc')
                ->get();
        }

        return view('admin.laporan.index', compact('tab', 'startDate', 'endDate', 'startPeriode', 'endPeriode', 'data'));
    }

    public function exportExcelArusKas(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $fileName = 'Buku_Kas_Pamsimas_' . $request->start_date . '_sd_' . $request->end_date . '.xlsx';
        return Excel::download(new ArusKasExport($request->start_date, $request->end_date), $fileName);
    }

    public function exportExcelTagihan(Request $request)
    {
        $request->validate([
            'start_periode' => 'required|date_format:Y-m',
            'end_periode' => 'required|date_format:Y-m|after_or_equal:start_periode',
        ]);

        $fileName = 'Laporan_Tagihan_Pamsimas_' . $request->start_periode . '_sd_' . $request->end_periode . '.xlsx';
        return Excel::download(new TagihanExport($request->start_periode, $request->end_periode), $fileName);
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

    public function exportPDF(Request $request)
    {
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();

        $arusKas = ArusKas::with('akunKas')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->get();

        $saldoAwal = ArusKas::where('tanggal', '<', $startDate)
            ->selectRaw("SUM(CASE WHEN jenis_transaksi IN ('masuk', 'mutasi_masuk') THEN nominal ELSE -nominal END) as total")
            ->first()->total ?? 0;

        $totalMasuk = $arusKas->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])->sum('nominal');
        $totalKeluar = $arusKas->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])->sum('nominal');
        $saldoAkhir = $saldoAwal + $totalMasuk - $totalKeluar;

        $data = [
            'arusKas' => $arusKas,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMM Y'),
            'saldoAwal' => $saldoAwal,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAkhir' => $saldoAkhir,
            'tanggalCetak' => Carbon::now()->isoFormat('D MMMM Y')
        ];

        $pdf = Pdf::loadView('admin.laporan.pdf_aruskas', $data)->setPaper('a4', 'landscape');
        return $pdf->stream("Laporan_ArusKas_{$startDate}_sd_{$endDate}.pdf");
    }

    public function exportTagihanPDF(Request $request)
    {
        $startPeriode = $request->start_periode ?: Carbon::now()->format('Y-m');
        $endPeriode = $request->end_periode ?: Carbon::now()->format('Y-m');
        $searchPelanggan = $request->search_pelanggan;

        $query = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
            ->whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$startPeriode, $endPeriode]);

        if ($searchPelanggan) {
            $query->whereHas('pelanggan', function ($q) use ($searchPelanggan) {
                $q->where('nama_pelanggan', 'like', '%' . $searchPelanggan . '%')
                    ->orWhere('kode_pelanggan', 'like', '%' . $searchPelanggan . '%');
            });
        }

        $tagihan = $query->orderBy('tahun_periode', 'asc')->orderBy('bulan_periode', 'asc')->get();

        // Format Label Periode (Jika 1 bulan saja, tampilkan 1. Jika rentang, tampilkan Awal - Akhir)
        $labelStart = Carbon::createFromFormat('Y-m', $startPeriode)->isoFormat('MMM Y');
        $labelEnd = Carbon::createFromFormat('Y-m', $endPeriode)->isoFormat('MMM Y');
        $periodeLabel = $labelStart === $labelEnd ? strtoupper($labelStart) : strtoupper($labelStart . ' - ' . $labelEnd);

        $data = [
            'tagihan' => $tagihan,
            'periodeLabel' => $periodeLabel,
            'searchPelanggan' => $searchPelanggan,
            'totalTagihan' => $tagihan->sum('total_tagihan'),
            'totalTerbayar' => $tagihan->sum('total_terbayar'),
            'totalTunggakan' => $tagihan->sum('sisa_tagihan'),
            'tanggalCetak' => Carbon::now()->isoFormat('D MMMM Y')
        ];

        $pdf = Pdf::loadView('admin.laporan.pdf_tagihan', $data)->setPaper('a4', 'landscape');
        return $pdf->stream("Laporan_Tagihan_{$startPeriode}_sd_{$endPeriode}.pdf");
    }
}
