<!DOCTYPE html>
<html>

<head>
    <title>Laporan Mutasi Inventaris</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: center;
        }

        td {
            padding: 6px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-green {
            color: #16a34a;
        }

        /* Warna hijau mirip Tailwind */
        .text-red {
            color: #dc2626;
        }

        /* Warna merah mirip Tailwind */
        .footer-table {
            border: none !important;
            margin-top: 30px;
        }

        .footer-table td {
            border: none !important;
            text-align: center;
            width: 33%;
        }

        .filter-info {
            margin-top: 5px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN MUTASI INVENTARIS KP-SPAMS TIRTA WENING</h2>
        <div>DESA SINGODUTAN, KEC/KAB: SELOGIRI / WONOGIRI</div>
        <div class="filter-info">PERIODE : {{ strtoupper($startDate) }} - {{ strtoupper($endDate) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="12%">TANGGAL</th>
                <th width="15%">KODE BARANG</th>
                <th width="28%">NAMA BARANG</th>
                <th width="15%">JENIS MUTASI</th>
                <th width="10%">JUMLAH</th>
                <th width="15%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventaris as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $item->inventaris->kode_barang }}</td>
                    <td>{{ $item->inventaris->nama_barang }}</td>
                    <td class="text-center">{{ strtoupper($item->jenis_mutasi) }}</td>

                    {{-- Menampilkan angka + hijau untuk Masuk/Pengembalian, dan - merah untuk Keluar --}}
                    <td class="text-center font-bold">
                        @if (in_array($item->jenis_mutasi, ['masuk', 'pengembalian']))
                            <span class="text-green">+{{ $item->jumlah }}</span>
                        @else
                            <span class="text-red">-{{ $item->jumlah }}</span>
                        @endif
                    </td>

                    <td>{{ $item->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data mutasi inventaris pada
                        periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Kolom Tanda Tangan --}}
    <table class="footer-table">
        <tr>
            <td>
                Mengetahui,<br>Ketua KP-SPAMS<br><br><br><br>
                <b>( Kunto Budhi Sasongko )</b>
            </td>
            <td>
                Sekretaris KP-SPAMS<br><br><br><br>
                <b>( Puji Hasitutiningsih )</b>
            </td>
            <td>
                Dibuat oleh,<br>Bendahara KP-SPAMS<br><br><br><br>
                <b>( Yanni Puspitawati )</b>
            </td>
        </tr>
    </table>
</body>

</html>
