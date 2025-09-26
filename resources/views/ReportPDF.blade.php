<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
{{-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> --}}
    <title>Transaction Report</title>
</head>
<body>
  <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: white;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        .separator {
            height: 1px;
            background-color: #1e88e5;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;  
            margin-bottom: 20px;
        }
        th {
            background-color: #e6f0fa;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .right-align {
            text-align: right;
        }
        .center-align {
            text-align: center;
        }
        .status-success {
            color: #4caf50;
        }
        .status-failed {
            color: #f44336;
        }
        .status-pending {
            color: #ff9800;
        }
        .empty-state {
            text-align: center;
            font-style: italic;
            color: #999;
            padding: 20px;
        }
        .footer {
            text-align: right;
            font-size: 10px;
            font-style: italic;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN TRANSAKSI</div>
        <div class="company">E-Mart</div>
        <div class="separator"></div>
    </div>

  <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Pembeli</th>
                <th>Penjual</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp

            @forelse($transactions as $trx)
                @foreach($trx->detail_transaction as $detail)
                    <tr>
                        <td class="center-align">{{ $no++ }}</td>
                        <td>{{ $trx->kode_transaksi }}</td>
                        <td>{{ $trx->user->name ?? 'Tidak ada' }}</td>
                        <td>{{ $detail->product->seller->name ?? 'Tidak ada' }}</td>

                        {{-- gunakan subtotal (fallback ke harga * jumlah jika subtotal null) --}}
                        <td class="right-align">
                            Rp {{ number_format($detail->subtotal ?? ($detail->harga * $detail->jumlah), 0, ',', '.') }}
                        </td>

                        <td class="center-align">
                            @php
                                $status = $trx->status;
                                $label = $status === 'success' ? 'Berhasil' : ($status === 'failed' ? 'Gagal' : 'Menunggu');
                            @endphp
                            <span class="status-{{ $status }}">{{ $label }}</span>
                        </td>

                        <td class="center-align">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="empty-state">Data transaksi tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dibuat otomatis oleh sistem Marketplace pada {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
