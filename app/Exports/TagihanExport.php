<?php

namespace App\Exports;

use App\Models\TagihanAir;
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

class TagihanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
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

    public function startCell(): string
    {
        return 'A6';
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
            $row->pelanggan->user->nama_lengkap ?? 'N/A',
            $row->pencatatanMeter->meter_awal ?? 0,
            $row->pencatatanMeter->meter_akhir ?? 0,
            $row->pencatatanMeter->volume_m3 ?? 0,
            (float) $row->biaya_beban,
            (float) $row->biaya_pemakaian,
            (float) $row->total_tagihan,
            (float) $row->total_terbayar,
            (float) $row->sisa_tagihan,
            strtoupper(str_replace('_', ' ', $row->status_bayar))
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastColumn = $sheet->getHighestDataColumn(); // 'L'
                $lastRow = $sheet->getHighestDataRow();

                $totalRow = $lastRow < 7 ? 8 : $lastRow + 1;

                // --- 1. Baris TOTAL KESELURUHAN ---
                $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN');
                $sheet->mergeCells('A' . $totalRow . ':H' . $totalRow); // Menggabungkan kolom A sampai H
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Menghitung Total dari Database Menggunakan PHP
                $totalTagihan = TagihanAir::whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$this->startPeriode, $this->endPeriode])->sum('total_tagihan');
                $totalTerbayar = TagihanAir::whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$this->startPeriode, $this->endPeriode])->sum('total_terbayar');
                $totalTunggakan = TagihanAir::whereRaw("CONCAT(tahun_periode, '-', LPAD(bulan_periode, 2, '0')) BETWEEN ? AND ?", [$this->startPeriode, $this->endPeriode])->sum('sisa_tagihan');

                // Memasukkan Hasil Hitung ke Kolom I, J, K
                $sheet->setCellValue('I' . $totalRow, (float) $totalTagihan);
                $sheet->setCellValue('J' . $totalRow, (float) $totalTerbayar);
                $sheet->setCellValue('K' . $totalRow, (float) $totalTunggakan);

                // --- 2. Membuat KOP LAPORAN ---
                $sheet->setCellValue('A1', 'LAPORAN TAGIHAN AIR PAMSIMAS');
                $sheet->mergeCells('A1:' . $lastColumn . '1');

                $sheet->setCellValue('A2', 'Desa Singodutan, Kec. Selogiri, Kab. Wonogiri');
                $sheet->mergeCells('A2:' . $lastColumn . '2');

                $periode = 'Periode: ' . Carbon::createFromFormat('Y-m', $this->startPeriode)->isoFormat('MMMM Y') . ' s.d ' . Carbon::createFromFormat('Y-m', $this->endPeriode)->isoFormat('MMMM Y');
                $sheet->setCellValue('A4', $periode);
                $sheet->mergeCells('A4:' . $lastColumn . '4');

                // Styling KOP
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // --- 3. Styling Tabel Header (#f2f2f2) ---
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

                // --- 4. Styling Baris Total (#eeeeee) ---
                $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEEEEEE'],
                    ],
                ]);

                // Memberikan Border
                $tableRange = 'A6:' . $lastColumn . $totalRow;
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Format Nominal (Rp) untuk Kolom Biaya & Tagihan
                $sheet->getStyle('G7:K' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');

                // --- 5. Tanda Tangan ---
                $signRow = $totalRow + 3;

                $sheet->setCellValue('B' . $signRow, "Dibuat oleh,\nSekretaris KP-SPAMS\n\n\n\n( Yanni Puspitawati )");
                $sheet->setCellValue('K' . $signRow, "Mengetahui,\nKetua KP-SPAMS\n\n\n\n( Kunto Budhi )");

                $sheet->getStyle('B' . $signRow . ':K' . $signRow)->applyFromArray([
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
