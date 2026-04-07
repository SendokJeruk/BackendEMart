<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction['kode_transaksi'] }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; color: #333; background-color: #f9f9f9; }
        .invoice-box { max-width: 800px; margin: 40px auto; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .header-top h1 { font-size: 36px; margin: 0 0 10px 0; color: #2c3e50; letter-spacing: 1px; }
        .invoice-meta { font-size: 14px; color: #7f8c8d; line-height: 1.6; }
        .invoice-meta strong { color: #34495e; }
        .company-info { text-align: right; font-size: 14px; line-height: 1.6; color: #555; }
        .company-info strong { font-size: 18px; color: #2c3e50; display: block; margin-bottom: 5px; }

        .info-section { margin-bottom: 30px; border-top: 2px solid #ecf0f1; border-bottom: 2px solid #ecf0f1; padding: 20px 0; }
        .info-block { width: 100%; }
        .info-block h3 { margin: 0 0 15px 0; font-size: 16px; color: #2c3e50; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-block p { margin: 0; font-size: 14px; line-height: 1.6; color: #555; }
        .info-block p strong { color: #333; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        table th { background-color: #f8f9fa; color: #2c3e50; padding: 12px 15px; text-align: left; font-weight: bold; border-bottom: 2px solid #bdc3c7; }
        table td { padding: 12px 15px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; }

        .item-row td { color: #555; }
        .item-name { font-weight: bold; color: #34495e; }

        .summary-section { width: 50%; float: right; margin-top: 20px; }
        .summary-section table th, .summary-section table td { border: none; padding: 8px 15px; }
        .summary-section table tr.total td { border-top: 2px solid #2c3e50; font-size: 18px; font-weight: bold; color: #2c3e50; padding-top: 15px; }

        .footer { clear: both; margin-top: 80px; text-align: center; color: #7f8c8d; font-size: 14px; border-top: 1px solid #ecf0f1; padding-top: 20px; }

        @media print {
            body { background: #fff; margin: 0; }
            .invoice-box { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; border: none; }
        }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="invoice-box clearfix">
        <table style="margin-top:0;">
            <tr>
                <td style="padding:0; border:none; width:50%;">
                    <h1 style="font-size: 36px; margin: 0 0 10px 0; color: #2c3e50; letter-spacing: 1px;">INVOICE</h1>
                    <div class="invoice-meta">
                        <strong>Invoice #</strong> {{ $transaction['kode_transaksi'] }}<br>
                        <strong>Tanggal:</strong> {{ date('d F Y', strtotime($transaction['tanggal_transaksi'])) }}<br>
                    </div>
                </td>
                <td class="company-info" style="padding:0; border:none; width:50%; vertical-align:top;">
                    <strong>Eleven Mart</strong>
                    oleh Team SendokJeruk<br>
                    Bandung, Indonesia<br>
                    Email: support@elevenmart.com<br>
                </td>
            </tr>
        </table>

        <div style="margin-top: 40px; margin-bottom: 30px; border-top: 2px solid #ecf0f1; border-bottom: 2px solid #ecf0f1; padding: 20px 0;">
            <table style="width: 100%; margin:0;">
                <tr>
                    <td style="padding:0; border:none; vertical-align:top; width: 50%;">
                        <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #2c3e50; text-transform: uppercase;">Ditagihkan Kepada:</h3>
                        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #555;">
                            <strong>{{ $transaction['user']['name'] ?? 'Pelanggan' }}</strong><br>
                            {{ $transaction['user']['email'] ?? '' }}<br>
                            {{ $transaction['user']['no_telp'] ?? '' }}<br><br>

                            <strong>Status Pembayaran:</strong><br>
                            @if($transaction['status'] === 'success')
                                <span style="color: #27ae60; font-weight: bold; font-size: 16px; display: inline-block; margin-top: 5px; padding: 4px 8px; border: 1px solid #27ae60; border-radius: 4px;">LUNAS</span>
                            @else
                                <span style="color: #e74c3c; font-weight: bold; font-size: 16px; display: inline-block; margin-top: 5px; padding: 4px 8px; border: 1px solid #e74c3c; border-radius: 4px;">BELUM LUNAS</span>
                            @endif
                        </p>
                    </td>
                    <td style="padding:0; border:none; vertical-align:top; width: 50%;">
                        <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #2c3e50; text-transform: uppercase;">Informasi Pengiriman:</h3>
                        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #555;">
                            @php
                                $shipment = $transaction['shipment'][0] ?? null;
                                $alamat = $shipment['alamat'] ?? null;
                            @endphp

                            @if($alamat)
                                <strong>{{ $alamat['nama_penerima'] ?? '-' }}</strong><br>
                                {{ $alamat['detail_alamat'] ?? '-' }}<br>
                                {{ $alamat['subdistrict_name'] ?? '-' }}, {{ $alamat['district_name'] ?? '-' }}<br>
                                {{ $alamat['city_name'] ?? '-' }}, {{ $alamat['province_name'] ?? '-' }} {{ $alamat['zip_code'] ?? '-' }}<br>
                            @else
                                <em>Alamat tidak tersedia</em><br>
                            @endif
                            <br>
                            <strong>Kurir:</strong> {{ strtoupper($shipment['kurir'] ?? '-') }}<br>
                            <strong>No. Resi:</strong> {{ $shipment['kode_resi'] ?? 'Belum tersedia' }}
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi Produk</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction['detail_transaction'] as $item)
                <tr class="item-row">
                    <td>
                        <span class="item-name">{{ $item['product']['nama_product'] ?? 'Produk' }}</span>
                    </td>
                    <td style="text-align: center;">{{ $item['jumlah'] }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <table style="width:100%;">
                <tr>
                    <td style="text-align: right; color: #555;">Subtotal Produk:</td>
                    <td style="text-align: right; width: 120px;">Rp {{ number_format($transaction['total_harga'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: right; color: #555;">Biaya Pengiriman:</td>
                    <td style="text-align: right;">Rp {{ number_format($transaction['total_ongkir'], 0, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td style="text-align: right;">Total Pembayaran:</td>
                    <td style="text-align: right;">Rp {{ number_format($transaction['total_harga'] + $transaction['total_ongkir'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih telah berbelanja di Eleven Mart.<br>Jika Anda memiliki pertanyaan mengenai invoice ini, silakan hubungi layanan pelanggan kami.</p>
        </div>
    </div>
</body>
</html>
