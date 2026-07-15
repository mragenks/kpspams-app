<!DOCTYPE html>
<html>

<head>
    <title>Laporan Arus Kas PAMSIMAS</title>
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

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .footer-table {
            border: none !important;
            margin-top: 30px;
        }

        .footer-table td {
            border: none !important;
            text-align: center;
            width: 33%;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>BUKU KAS HARIAN KP-SPAMS TIRTA WENING</h2>
        <div>DESA SINGODUTAN, KEC/KAB: SELOGIRI / WONOGIRI</div>
        {{-- Menampilkan rentang tanggal periode yang dipilih --}}
        <div style="font-weight: bold; margin-top: 5px;">
            PERIODE : {{ strtoupper($startDate) }} - {{ strtoupper($endDate) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">TANGGAL</th>
                <th width="40%">URAIAN</th>
                <th width="13%">DEBIT (Rp)</th>
                <th width="13%">KREDIT (Rp)</th>
                <th width="14%">SALDO (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center">1</td>
                {{-- Tanggal saldo awal mengambil dari hari pertama periode yang dipilih --}}
                <td align="center">{{ $startDate }}</td>
                <td class="font-bold">SALDO AWAL</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
            </tr>
            @php $currentSaldo = $saldoAwal; @endphp
            @foreach ($arusKas as $index => $item)
                @php
                    $isMasuk = in_array($item->jenis_transaksi, ['masuk', 'mutasi_masuk']);
                    $currentSaldo += $isMasuk ? $item->nominal : -$item->nominal;
                @endphp
                <tr>
                    <td align="center">{{ $index + 2 }}</td>
                    <td align="center">
                        {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}
                        <br><small style="color: #444; font-size: 10px;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</small>
                    </td>
                    <td>
                        {{ $item->keterangan }}
                        @if($item->no_bukti)
                            <br><small style="color: #666; font-size: 9px;">No. Bukti: {{ $item->no_bukti }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $isMasuk ? number_format($item->nominal, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ !$isMasuk ? number_format($item->nominal, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #eee;">
                <td colspan="3" align="center">TOTAL PERIODE INI</td>
                <td class="text-right">{{ number_format($totalMasuk, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalKeluar, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($saldoAkhir, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Tanda Tangan --}}
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
