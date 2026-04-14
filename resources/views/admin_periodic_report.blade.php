<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Admin E-Mart</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 14px; font-weight: bold; }
        .subtitle { font-size: 12px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN GLOBAL E-MART</div>
        <div class="subtitle">Periode: {{ $startDate->format('d M Y') }} s/d {{ $endDate->format('d M Y') }}</div>
    </div>

    <div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">Transaksi Global</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Tgl Transaksi</th>
                <th>Nama Pembeli</th>
                <th>Total Harga Barang</th>
                <th>Total Ongkir</th>
                <th>Total Bayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $totalGmv = 0; $totalOngkir = 0; @endphp
            @foreach($transactions as $trx)
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td>{{ $trx->kode_transaksi }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('Y-m-d H:i') }}</td>
                <td>{{ $trx->user->name ?? 'User Tidak Ditemukan' }}</td>
                <td class="right">{{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($trx->total_ongkir, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($trx->total_harga + $trx->total_ongkir, 0, ',', '.') }}</td>
                <td class="center">{{ strtoupper($trx->status) }}</td>
            </tr>
            @php
                if ($trx->status === 'success') {
                    $totalGmv += $trx->total_harga;
                    $totalOngkir += $trx->total_ongkir;
                }
            @endphp
            @endforeach
            <tr>
                <td colspan="6" class="right bold">TOTAL (HANYA SUCCESS)</td>
                <td colspan="2" class="left bold">{{ number_format($totalGmv + $totalOngkir, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="font-size: 12px; font-weight: bold; margin-bottom: 5px; margin-top: 20px;">Penarikan Dana Seller</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Request</th>
                <th>Nama Seller / Toko</th>
                <th>Nominal Pencairan</th>
                <th>Metode</th>
                <th>Rekening Tujuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $totalWithdraw = 0; @endphp
            @foreach($withdraws as $wd)
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td>{{ \Carbon\Carbon::parse($wd->created_at)->format('Y-m-d H:i') }}</td>
                <td>{{ $wd->user && $wd->user->toko ? $wd->user->toko->nama_toko : ($wd->user->name ?? 'User Tidak Ditemukan') }}</td>
                <td class="right">{{ number_format($wd->jumlah, 0, ',', '.') }}</td>
                <td class="center">{{ strtoupper($wd->metode) }}</td>
                <td>{{ $wd->rekening_tujuan }}</td>
                <td class="center">{{ strtoupper($wd->status) }}</td>
            </tr>
            @php
                if (in_array(strtolower($wd->status), ['accepted', 'success'])) {
                    $totalWithdraw += $wd->jumlah;
                }
            @endphp
            @endforeach
            <tr>
                <td colspan="3" class="right bold">TOTAL BERHASIL DICAIRKAN</td>
                <td colspan="4" class="left bold">{{ number_format($totalWithdraw, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>