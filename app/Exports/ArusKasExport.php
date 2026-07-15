<?php

namespace App\Exports;

use App\Models\ArusKas;
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

class ArusKasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
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
        return ArusKas::query()
            ->with(['akunKas', 'dicatatOleh', 'pembayaran.pelanggan.user'])
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
        // Tetap diubah menjadi float agar Excel membacanya sebagai Angka, bukan Teks
        $masuk = in_array($row->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? (float) $row->nominal : 0;
        $keluar = in_array($row->jenis_transaksi, ['keluar', 'mutasi_keluar']) ? (float) $row->nominal : 0;

        $keterangan = $row->keterangan;
        if ($row->pembayaran && $row->pembayaran->pelanggan && $row->pembayaran->pelanggan->user) {
            $keterangan .= ' - ' . $row->pembayaran->pelanggan->user->nama_lengkap;
        }

        if (strpos($keterangan, ' - ') !== false) {
            $parts = explode(' - ', $keterangan, 2);
            $keterangan = $parts[0] . "\nKeterangan: " . $parts[1];
        } elseif (strpos($keterangan, 'Deposit/Titipan Saldo: ') === 0 && strpos($keterangan, '. ') !== false) {
            $parts = explode('. ', $keterangan, 2);
            $keterangan = $parts[0];
            if (isset($parts[1]) && trim($parts[1]) !== '') {
                $keterangan .= "\nKeterangan: " . $parts[1];
            }
        }

        return [
            Carbon::parse($row->tanggal)->format('d/m/Y') . ' ' . Carbon::parse($row->created_at)->format('H:i'),
            $row->no_bukti ?? '-',
            $keterangan,
            $row->akunKas->nama_akun ?? 'Tidak Diketahui',
            ucwords(str_replace('_', ' ', $row->jenis_transaksi)),
            $masuk,
            $keluar,
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

                $totalRow = $lastRow < 7 ? 8 : $lastRow + 1;

                // 1. Baris TOTAL KESELURUHAN
                $sheet->setCellValue('A' . $totalRow, 'TOTAL PERIODE INI');
                $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- SOLUSI AMPUH: Hitung Total Menggunakan PHP & Database ---
                $totalMasuk = ArusKas::whereBetween('tanggal', [$this->startDate, $this->endDate])
                    ->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])
                    ->sum('nominal');

                $totalKeluar = ArusKas::whereBetween('tanggal', [$this->startDate, $this->endDate])
                    ->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])
                    ->sum('nominal');

                // Memasukkan ANGKA JADI ke Excel (Bukan Rumus)
                $sheet->setCellValue('F' . $totalRow, (float) $totalMasuk);
                $sheet->setCellValue('G' . $totalRow, (float) $totalKeluar);
                // -------------------------------------------------------------

                // --- Membuat KOP LAPORAN ---
                $sheet->setCellValue('A1', 'LAPORAN ARUS KAS PAMSIMAS');
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

                // 2. Styling Tabel Header (Sesuai PDF)
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

                // 3. Styling Baris Total (Sesuai PDF)
                $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEEEEEE'],
                    ],
                ]);

                // Memberikan Border pada seluruh sel tabel
                $tableRange = 'A6:' . $lastColumn . $totalRow;
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Format Nominal (Rp) agar rapi menggunakan titik ribuan
                $sheet->getStyle('F7:G' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');

                // Wrap text untuk kolom Keterangan (C) agar text new line (\n) terlihat rapi
                $sheet->getStyle('C7:C' . $totalRow)->getAlignment()->setWrapText(true);

                // 4. Menambahkan Kolom Tanda Tangan (Sesuai PDF)
                $signRow = $totalRow + 3;

                $sheet->setCellValue('B' . $signRow, "Dibuat oleh,\nSekretaris KP-SPAMS\n\n\n\n( Yanni Puspitawati )");
                $sheet->setCellValue('G' . $signRow, "Mengetahui,\nKetua KP-SPAMS\n\n\n\n( Kunto Budhi )");

                $sheet->getStyle('B' . $signRow . ':G' . $signRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true
                    ],
                    'font' => [
                        'bold' => true
                    ]
                ]);

                $sheet->getRowDimension($signRow)->setRowHeight(100);
            },
        ];
    }
}
