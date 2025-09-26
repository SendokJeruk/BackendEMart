<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction['kode_transaksi'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .invoice-box { max-width: 800px; margin: 20px auto; padding: 30px; border: 1px solid #000; }
        h1 { font-size: 28px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table td { padding: 8px; vertical-align: top; }
        .heading td { border-bottom: 2px solid #000; font-weight: bold; }
        .item td { border-bottom: 1px solid #ddd; }
        .total td:nth-child(4) { border-top: 2px solid #000; font-weight: bold; }
        .company-info { text-align: right; }
        .bill-to { margin-top: 20px; }
        hr { border: 1px solid #000; margin: 20px 0; }
        @media print {
            body { background: #fff; }
            .invoice-box { box-shadow: none; border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table>
            <tr>
                <td>
                    <h1>INVOICE</h1>
                    Invoice #: {{ $transaction['kode_transaksi'] }}<br>
                    Tanggal: {{ date('d-m-Y', strtotime($transaction['tanggal_transaksi'])) }}<br>
                    Status: {{ ucfirst($transaction['status']) }}
                </td>
                <td class="company-info">
                    <strong>PT Cochlear Citrus</strong><br>
                    Jl. Contoh No.123<br>
                    Bandung, Indonesia<br>
                    Email: info@contoh.com<br>
                    Telp: +62 22 123456
                </td>
            </tr>
        </table>

        <hr>

        <div class="bill-to">
            <strong>Detail Pengiriman:</strong><br>
            {{-- Kurir: {{ $transaction['shipment'][0]['kurir'] ?? '-' }}<br> --}}
            Ongkir: Rp {{ number_format($transaction['total_ongkir'], 0, ',', '.') }}<br>
            Status Pengiriman: {{ ucfirst($transaction['shipment'][0]['status_pengiriman'] ?? '-') }}
        </div>

        <table>
            <tr class="heading">
                <td>Produk</td>
                <td>Jumlah</td>
                <td>Harga Satuan</td>
                <td>Subtotal</td>
            </tr>

            @foreach($transaction['detail_transaction'] as $item)
            <tr class="item">
                <td>{{ $item['product']['nama_product'] }}</td>
                <td>{{ $item['jumlah'] }}</td>
                <td>Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach

            <tr class="item">
                <td colspan="3">Ongkir</td>
                <td>Rp {{ number_format($transaction['total_ongkir'], 0, ',', '.') }}</td>
            </tr>

            <tr class="total">
                <td></td>
                <td></td>
                <td>Total Bayar</td>
                <td>Rp {{ number_format($transaction['total_harga'] + $transaction['total_ongkir'], 0, ',', '.') }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px;">Terima kasih atas pembelian Anda!</p>
    </div>
</body>
</html>
