<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendapatan Seller</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 12px; margin-bottom: 5px; }
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
        <div class="title">LAPORAN PENDAPATAN PENJUALAN</div>
        <div class="subtitle">Nama Toko: {{ $namaToko }}</div>
        <div class="subtitle">Periode: {{ $startDate->format('d M Y') }} s/d {{ $endDate->format('d M Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Tgl Transaksi</th>
                <th>Nama Produk</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Subtotal Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $totalPendapatan = 0; @endphp
            @foreach($details as $detailIncome)
            @php $dt = $detailIncome->detailTransaction; @endphp
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td>{{ $dt->transaction->kode_transaksi }}</td>
                <td>{{ \Carbon\Carbon::parse($dt->transaction->tanggal_transaksi)->format('Y-m-d') }}</td>
                <td>{{ $dt->product->nama_product }}</td>
                <td class="right">{{ number_format($dt->harga, 0, ',', '.') }}</td>
                <td class="center">{{ $dt->jumlah }}</td>
                <td class="right">{{ number_format($detailIncome->jumlah, 0, ',', '.') }}</td>
            </tr>
            @php $totalPendapatan += $detailIncome->jumlah; @endphp
            @endforeach
            <tr>
                <td colspan="6" class="right bold">TOTAL PENDAPATAN</td>
                <td class="right bold">{{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>