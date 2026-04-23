<?php

namespace App\Exports;

use App\Models\TagihanAir;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TagihanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $startPeriode;
    protected $endPeriode;

    public function __construct($startPeriode, $endPeriode)
    {
        $this->startPeriode = $startPeriode;
        $this->endPeriode = $endPeriode;
    }

    public function query()
    {
        return TagihanAir::query()
            ->with(['pelanggan.user', 'pencatatanMeter'])
            ->whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$this->startPeriode, $this->endPeriode])
            ->orderBy('tahun_periode', 'desc')
            ->orderBy('bulan_periode', 'desc');
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Kode Pelanggan',
            'Nama Pelanggan',
            'Meter Awal',
            'Meter Akhir',
            'Pemakaian (m³)',
            'Biaya Beban (Rp)',
            'Biaya Pemakaian (Rp)',
            'Total Tagihan (Rp)',
            'Terbayar (Rp)',
            'Sisa Tunggakan (Rp)',
            'Status'
        ];
    }

    public function map($row): array
    {
        return [
            str_pad($row->bulan_periode, 2, '0', STR_PAD_LEFT) . '/' . $row->tahun_periode,
            $row->pelanggan->kode_pelanggan ?? 'N/A',
            $row->pelanggan->nama_pelanggan ?? 'N/A', 
            $row->pencatatanMeter->meter_awal ?? 0,
            $row->pencatatanMeter->meter_akhir ?? 0,
            $row->pencatatanMeter->volume_m3 ?? 0,
            $row->biaya_beban,
            $row->biaya_pemakaian,
            $row->total_tagihan,
            $row->total_terbayar,
            $row->sisa_tagihan,
            ucwords(str_replace('_', ' ', $row->status_bayar))
        ];
    }
}