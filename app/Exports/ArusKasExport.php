<?php

namespace App\Exports;

use App\Models\ArusKas;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ArusKasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    // Menangkap parameter rentang tanggal dari Controller
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return ArusKas::query()
            ->with(['akunKas', 'dicatatOleh']) // Pastikan relasi ini ada di Model ArusKas!
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Bukti',
            'Keterangan',
            'Akun Kas',
            'Jenis Transaksi',
            'Pemasukan (Rp)',
            'Pengeluaran (Rp)',
            'Dicatat Oleh'
        ];
    }

    public function map($row): array
    {
        // Logika pemisahan kolom debit/kredit
        $masuk = in_array($row->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? $row->nominal : 0;
        $keluar = in_array($row->jenis_transaksi, ['keluar', 'mutasi_keluar']) ? $row->nominal : 0;

        return [
            Carbon::parse($row->tanggal)->format('d/m/Y'),
            $row->no_bukti ?? '-',
            $row->keterangan,
            $row->akunKas->nama_akun ?? 'Tidak Diketahui',
            ucwords(str_replace('_', ' ', $row->jenis_transaksi)),
            $masuk,
            $keluar,
            $row->dicatatOleh->username ?? 'Sistem'
        ];
    }
}
