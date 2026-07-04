<?php

namespace App\Exports;

use App\Models\RiwayatInventaris;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class InventarisExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
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

    public function startCell(): string
    {
        return 'A6';
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
        // Memberikan tanda + atau - pada jumlah sesuai PDF
        $jumlah = $row->jumlah;
        if (in_array($row->jenis_mutasi, ['masuk', 'pengembalian'])) {
            $jumlah = '+' . $jumlah;
        } else {
            $jumlah = '-' . $jumlah;
        }

        return [
            Carbon::parse($row->tanggal)->format('d/m/Y'),
            $row->inventaris->kode_barang ?? 'N/A',
            $row->inventaris->nama_barang ?? 'N/A',
            strtoupper(str_replace('_', ' ', $row->jenis_mutasi)),
            $jumlah,
            $row->stok_sebelum,
            $row->stok_sesudah,
            $row->keterangan,
            $row->dicatatOleh->nama_lengkap ?? 'Sistem'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastColumn = $sheet->getHighestDataColumn();
                $lastRow = $sheet->getHighestDataRow();

                // --- Membuat KOP LAPORAN ---
                $sheet->setCellValue('A1', 'LAPORAN MUTASI INVENTARIS PAMSIMAS');
                $sheet->mergeCells('A1:' . $lastColumn . '1');

                $sheet->setCellValue('A2', 'Desa Singodutan, Kec. Selogiri, Kab. Wonogiri');
                $sheet->mergeCells('A2:' . $lastColumn . '2');

                $periode = 'Periode: ' . Carbon::parse($this->startDate)->isoFormat('D MMMM Y') . ' s.d ' . Carbon::parse($this->endDate)->isoFormat('D MMMM Y');
                $sheet->setCellValue('A4', $periode);
                $sheet->mergeCells('A4:' . $lastColumn . '4');

                // Styling KOP
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // --- Styling Tabel Header (#f2f2f2) ---
                $sheet->getStyle('A6:' . $lastColumn . '6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF2F2F2'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // --- Memberikan Border Tabel ---
                $tableRange = 'A6:' . $lastColumn . $lastRow;
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Alignment Center untuk Kolom Mutasi
                $sheet->getStyle('D7:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- Tanda Tangan ---
                $signRow = $lastRow + 3;

                $sheet->setCellValue('B' . $signRow, "Dibuat oleh,\nSekretaris KP-SPAMS\n\n\n\n( Yanni Puspitawati )");
                $sheet->setCellValue('H' . $signRow, "Mengetahui,\nKetua KP-SPAMS\n\n\n\n( Kunto Budhi )");

                $sheet->getStyle('B' . $signRow . ':H' . $signRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true
                    ],
                    'font' => ['bold' => true]
                ]);

                $sheet->getRowDimension($signRow)->setRowHeight(100);
            },
        ];
    }
}
