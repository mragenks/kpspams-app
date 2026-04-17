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

        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        $bulan = $request->bulan ?: date('n');
        $tahun = $request->tahun ?: date('Y');

        $data = [];

        if ($tab == 'arus-kas') {
            $data['arusKas'] = ArusKas::with('akunKas')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'asc')
                ->paginate(15);
        } elseif ($tab == 'tagihan') {
            $searchPelanggan = $request->search_pelanggan;

            // PERBAIKAN BUG FILTER TANGGAL: Logika ketat (Strict Filter)
            $query = TagihanAir::with(['pelanggan', 'pencatatanMeter'])
                ->where(function ($q) use ($startDate, $endDate) {
                    // Kasus 1: Jika tagihan punya relasi meteran, STRICT filter dari tanggal_pencatatan
                    $q->whereHas('pencatatanMeter', function ($sub) use ($startDate, $endDate) {
                        $sub->whereBetween('tanggal_pencatatan', [$startDate, $endDate]);
                    })
                        // Kasus 2: Jika tagihan TIDAK punya meteran (manual), barulah pakai created_at
                        ->orWhere(function ($sub2) use ($startDate, $endDate) {
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

        return view('admin.laporan.index', compact('tab', 'startDate', 'endDate', 'bulan', 'tahun', 'data'));
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
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();
        $searchPelanggan = $request->search_pelanggan;

        // PERBAIKAN BUG FILTER TANGGAL (Sama seperti index)
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

        $tagihan = $query->orderBy('created_at', 'asc')->get();

        $data = [
            'tagihan' => $tagihan,
            'startDate' => Carbon::parse($startDate)->isoFormat('D MMM Y'),
            'endDate' => Carbon::parse($endDate)->isoFormat('D MMM Y'),
            'searchPelanggan' => $searchPelanggan,
            'totalTagihan' => $tagihan->sum('total_tagihan'),
            'totalTerbayar' => $tagihan->sum('total_terbayar'),
            'totalTunggakan' => $tagihan->sum('sisa_tagihan'),
            'tanggalCetak' => Carbon::now()->isoFormat('D MMMM Y')
        ];

        $pdf = Pdf::loadView('admin.laporan.pdf_tagihan', $data)->setPaper('a4', 'landscape');
        return $pdf->stream("Laporan_Tagihan_{$startDate}_sd_{$endDate}.pdf");
    }
}
