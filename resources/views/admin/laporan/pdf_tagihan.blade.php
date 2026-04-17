<!DOCTYPE html>
<html>

<head>
    <title>Laporan Tagihan Air</title>
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

        .text-center {
            text-align: center;
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

        .filter-info {
            margin-top: 5px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN TAGIHAN & TUNGGAKAN AIR KP-SPAMS TIRTA WENING</h2>
        <div>DESA SINGODUTAN, KEC/KAB: SELOGIRI / WONOGIRI</div>
        <div class="filter-info">PERIODE : {{ strtoupper($startDate) }} - {{ strtoupper($endDate) }}</div>

        @if ($searchPelanggan)
            <div class="filter-info" style="color: #d97706; margin-top: 3px;">
                DIFILTER UNTUK PELANGGAN : {{ strtoupper($searchPelanggan) }}
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">TANGGAL</th>
                <th width="23%">NAMA PELANGGAN</th>
                <th width="10%">VOL (m³)</th>
                <th width="15%">TOTAL TAGIHAN</th>
                <th width="12%">SUDAH BAYAR</th>
                <th width="10%">STATUS</th>
                <th width="10%">SISA/TUNGGAKAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tagihan as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{-- PERBAIKAN BUG: Gunakan nullsafe operator ?-> --}}
                        {{ \Carbon\Carbon::parse($item->pencatatanMeter?->tanggal_pencatatan ?? $item->created_at)->format('d/m/Y') }}
                        <br>
                        <small style="color: #666;">Periode:
                            {{ \Carbon\Carbon::create(null, $item->bulan_periode, 1)->isoFormat('MMM') }}
                            {{ $item->tahun_periode }}</small>
                    </td>
                    <td>
                        {{ $item->pelanggan->nama_pelanggan }}<br>
                        <small style="color: #666;">{{ $item->pelanggan->kode_pelanggan }}</small>
                    </td>
                    <td class="text-center">{{ $item->pencatatanMeter?->volume_m3 ?? 0 }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_terbayar, 0, ',', '.') }}</td>
                    <td class="text-center">{{ strtoupper(str_replace('_', ' ', $item->status_bayar)) }}</td>
                    <td class="text-right text-red-600">Rp {{ number_format($item->sisa_tagihan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data tagihan pada periode
                        ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #eee;">
                <td colspan="4" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
                <td class="text-center">-</td>
                <td class="text-right">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td>Dibuat oleh,<br>Sekretaris KP-SPAMS<br><br><br><br><b>( Yanni Puspitawati )</b></td>
            <td></td>
            <td>Mengetahui,<br>Ketua KP-SPAMS<br><br><br><br><b>( Kunto Budhi Sasongko )</b></td>
        </tr>
    </table>
</body>

</html>
