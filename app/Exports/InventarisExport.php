<?php

namespace App\Exports;

use App\Models\RiwayatInventaris;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class InventarisExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return RiwayatInventaris::query()
            ->with(['inventaris', 'dicatatOleh'])
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'Tanggal Mutasi',
            'Kode Barang',
            'Nama Barang',
            'Jenis Mutasi',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Keterangan',
            'Dicatat Oleh'
        ];
    }

    public function map($row): array
    {
        return [
            Carbon::parse($row->tanggal)->format('d/m/Y'),
            $row->inventaris->kode_barang ?? 'N/A',
            $row->inventaris->nama_barang ?? 'N/A',
            ucwords(str_replace('_', ' ', $row->jenis_mutasi)),
            $row->jumlah,
            $row->stok_sebelum,
            $row->stok_sesudah,
            $row->keterangan ?? '-',
            $row->dicatatOleh->username ?? 'Sistem'
        ];
    }
}
