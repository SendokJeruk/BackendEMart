<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Toko;
use App\Models\User;
use App\Models\Shipment;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use App\Models\DetailIncome;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DetailTransaction;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{

    public function generateInvoice($kode_transaksi)
    {
        // ngambil data transaksi utuh, trus di-render jadi file PDF invoice buat di-download
        $transaction = Transaction::with(['user', 'detail_transaction.product', 'shipment.alamat'])
            ->where('kode_transaksi', $kode_transaksi)
            ->firstOrFail();

        if ($transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $pdf = Pdf::loadView('invoice', ['transaction' => $transaction]);
        return $pdf->download("Invoice-{$transaction->kode_transaksi}-".now()->format('Y-m-d_H-i-s').".pdf");
    }

    public function printStrukSeller($shipment_id)
    {
        $seller_id = auth()->id();

        $shipment = Shipment::with([
            'transaction.user',
            'alamat',
            'detail_shipments.detail_transaction.product'
        ])->where('id', $shipment_id)->firstOrFail();

        $isOwner = $shipment->detail_shipments()
            ->whereHas('detail_transaction.product', function ($query) use ($seller_id) {
                $query->where('user_id', $seller_id);
            })->exists();

        if (!$isOwner) {
            throw new AuthorizationException();
        }

        $transaction = $shipment->transaction;

        // cek detail transaksi punya seller (yg login)
        $detail_transactions = $shipment->detail_shipments->map(function ($ds) use ($seller_id) {
            if ($ds->detail_transaction->product->user_id == $seller_id) {
                return $ds->detail_transaction;
            }
            return null;
        })->filter()->values();

        $subtotal_produk = $detail_transactions->sum('subtotal');
        $ongkir = $shipment->ongkir ?? 0;

        // Sikit map struktur array buat ke pdf
        $dataForInvoice = $transaction->toArray();
        $dataForInvoice['user'] = $transaction->user ? $transaction->user->toArray() : null;

        $dataForInvoice['shipment'] = [
            [
                'kurir' => $shipment->kurir,
                'kode_resi' => $shipment->kode_resi,
                'alamat' => $shipment->alamat ? $shipment->alamat->toArray() : null,
            ]
        ];

        $detail_transactions_array = [];
        foreach ($detail_transactions as $dt) {
            $dtArray = $dt->toArray();
            $dtArray['product'] = $dt->product ? $dt->product->toArray() : null;
            $detail_transactions_array[] = $dtArray;
        }

        $dataForInvoice['detail_transaction'] = $detail_transactions_array;
        $dataForInvoice['total_harga'] = $subtotal_produk;
        $dataForInvoice['total_ongkir'] = $ongkir;

        $pdf = Pdf::loadView('invoice', ['transaction' => $dataForInvoice]);
        return $pdf->download("Struk-Pengiriman-{$transaction->kode_transaksi}-".now()->format('Y-m-d_H-i-s').".pdf");
    }

    public function adminPeriodicExcelReport(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfMonth();

        $spreadsheet = new Spreadsheet();

        // ini bikin shit transaksi all
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Transaksi Global');

        $sheet1->setCellValue('A1', 'LAPORAN TRANSAKSI GLOBAL E-MART');
        $sheet1->mergeCells('A1:H1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet1->setCellValue('A3', 'Periode Laporan:');
        $sheet1->setCellValue('C3', $startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y'));

        $rowHeader1 = 5;
        $headers1 = ['No', 'Kode Transaksi', 'Tanggal Transaksi', 'Nama Pembeli', 'Total Harga Barang', 'Total Ongkir', 'Total Bayar', 'Status'];
        foreach ($headers1 as $index => $header) {
            $col = chr(65 + $index);
            $sheet1->setCellValue("{$col}{$rowHeader1}", $header);
        }
        $sheet1->getStyle("A{$rowHeader1}:H{$rowHeader1}")->getFont()->setBold(true);

        $transactions = Transaction::with('user')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->get();

        $row1 = 6;
        $totalGmv = 0;
        $totalOngkir = 0;

        foreach ($transactions as $i => $trx) {
            $sheet1->setCellValue("A{$row1}", $i + 1);
            $sheet1->setCellValue("B{$row1}", $trx->kode_transaksi);
            $sheet1->setCellValue("C{$row1}", Carbon::parse($trx->tanggal_transaksi)->format('Y-m-d H:i'));
            $sheet1->setCellValue("D{$row1}", $trx->user->name ?? 'User Tidak Ditemukan');
            $sheet1->setCellValue("E{$row1}", $trx->total_harga);
            $sheet1->setCellValue("F{$row1}", $trx->total_ongkir);
            $sheet1->setCellValue("G{$row1}", $trx->total_harga + $trx->total_ongkir);
            $sheet1->setCellValue("H{$row1}", strtoupper($trx->status));

            if ($trx->status === 'success') {
                $totalGmv += $trx->total_harga;
                $totalOngkir += $trx->total_ongkir;
            }
            $row1++;
        }

        $sheet1->setCellValue("F{$row1}", 'TOTAL (HANYA SUCCESS)');
        $sheet1->setCellValue("G{$row1}", $totalGmv + $totalOngkir);
        $sheet1->getStyle("F{$row1}:G{$row1}")->getFont()->setBold(true);

        foreach (range('A', 'H') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ini shit penarikan
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Penarikan Dana Seller');

        $sheet2->setCellValue('A1', 'LAPORAN PENARIKAN DANA SELLER (WITHDRAWAL)');
        $sheet2->mergeCells('A1:G1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet2->setCellValue('A3', 'Periode Laporan:');
        $sheet2->setCellValue('C3', $startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y'));

        $rowHeader2 = 5;
        $headers2 = ['No', 'Tanggal Request', 'Nama Seller / Toko', 'Nominal Pencairan', 'Metode', 'Rekening Tujuan', 'Status'];
        foreach ($headers2 as $index => $header) {
            $col = chr(65 + $index);
            $sheet2->setCellValue("{$col}{$rowHeader2}", $header);
        }
        $sheet2->getStyle("A{$rowHeader2}:G{$rowHeader2}")->getFont()->setBold(true);

        $withdraws = \App\Models\Withdraw::with('user.toko')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $row2 = 6;
        $totalWithdraw = 0;

        foreach ($withdraws as $i => $wd) {
            $namaSeller = $wd->user && $wd->user->toko ? $wd->user->toko->nama_toko : ($wd->user->name ?? 'User Tidak Ditemukan');

            $sheet2->setCellValue("A{$row2}", $i + 1);
            $sheet2->setCellValue("B{$row2}", Carbon::parse($wd->created_at)->format('Y-m-d H:i'));
            $sheet2->setCellValue("C{$row2}", $namaSeller);
            $sheet2->setCellValue("D{$row2}", $wd->jumlah);
            $sheet2->setCellValue("E{$row2}", strtoupper($wd->metode));
            $sheet2->setCellValue("F{$row2}", $wd->rekening_tujuan);
            $sheet2->setCellValue("G{$row2}", strtoupper($wd->status));

            if (in_array(strtolower($wd->status), ['accepted', 'success'])) {
                $totalWithdraw += $wd->jumlah;
            }
            $row2++;
        }

        $sheet2->setCellValue("C{$row2}", 'TOTAL BERHASIL DICAIRKAN');
        $sheet2->setCellValue("D{$row2}", $totalWithdraw);
        $sheet2->getStyle("C{$row2}:D{$row2}")->getFont()->setBold(true);

        foreach (range('A', 'G') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $fileName = "Laporan-Admin-EMart-" . $startDate->format('Ymd') . "-" . $endDate->format('Ymd') . ".xlsx";

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"$fileName\"",
            'Cache-Control' => 'max-age=0'
        ]);
    }

    public function sellerTransactionReport($seller_id)
    {
        // filter transaksi yang ada produk si seller, trus dibikinin laporan PDF-nya
        if ((int)$seller_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $transactions = Transaction::whereHas('detail_transaction.product', function ($q) use ($seller_id) {
            $q->where('user_id', $seller_id);
        })
            ->with(['user', 'detail_transaction.product.seller'])
            ->get();


        $seller = User::find($seller_id);
        if (!$seller) {
            return abort(404, "Seller tidak ditemukan");
        }

    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $seller->name);

    return $this->generatePdf($transactions, "laporan-seller-$safeName-".now().".pdf");
}

    public function sellerPeriodicExcelReport(Request $request)
    {
        $seller_id = auth()->id();

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfMonth();

        // Get seller info
        $seller = User::with('toko')->find($seller_id);
        $namaToko = $seller->toko ? $seller->toko->nama_toko : $seller->name;

        // Ambil dari DetailIncome, karena income baru masuk jika shipment sudah diterima
        $details = DetailIncome::with(['detailTransaction.transaction', 'detailTransaction.product'])
            ->whereHas('detailTransaction.product', fn($q) => $q->where('user_id', $seller_id))
            ->whereHas('detailTransaction.transaction', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                  ->where('status', 'success');
            })
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Laporan
        $sheet->setCellValue('A1', 'LAPORAN PENDAPATAN PENJUALAN');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Nama Toko:');
        $sheet->setCellValue('C3', $namaToko);
        $sheet->mergeCells('C3:G3');

        $sheet->setCellValue('A4', 'Periode Laporan:');
        $sheet->setCellValue('C4', $startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y'));
        $sheet->mergeCells('C4:G4');

        $rowHeader = 6;
        $sheet->setCellValue("A{$rowHeader}", 'No');
        $sheet->setCellValue("B{$rowHeader}", 'Kode Transaksi');
        $sheet->setCellValue("C{$rowHeader}", 'Tanggal Transaksi');
        $sheet->setCellValue("D{$rowHeader}", 'Nama Produk');
        $sheet->setCellValue("E{$rowHeader}", 'Harga Satuan');
        $sheet->setCellValue("F{$rowHeader}", 'Jumlah');
        $sheet->setCellValue("G{$rowHeader}", 'Subtotal Pendapatan');

        $sheet->getStyle("A{$rowHeader}:G{$rowHeader}")->getFont()->setBold(true);

        $row = 7;
        $totalPendapatan = 0;

        foreach ($details as $i => $detailIncome) {
            $dt = $detailIncome->detailTransaction;
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $dt->transaction->kode_transaksi);
            $sheet->setCellValue("C{$row}", Carbon::parse($dt->transaction->tanggal_transaksi)->format('Y-m-d'));
            $sheet->setCellValue("D{$row}", $dt->product->nama_product);
            $sheet->setCellValue("E{$row}", $dt->harga);
            $sheet->setCellValue("F{$row}", $dt->jumlah);
            $sheet->setCellValue("G{$row}", $detailIncome->jumlah);

            $totalPendapatan += $detailIncome->jumlah;
            $row++;
        }

        $sheet->setCellValue("F{$row}", 'TOTAL PENDAPATAN');
        $sheet->setCellValue("G{$row}", $totalPendapatan);
        $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "laporan-pendapatan-seller-" . $startDate->format('Ymd') . "-" . $endDate->format('Ymd') . ".xlsx";

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"$fileName\"",
            'Cache-Control' => 'max-age=0'
        ]);
    }

    public function userTransactionReport($user_id)
    {
        // ngambil riwayat transaksi sukses punya user buat dicetak jadi PDF
        if ((int)$user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $transactions = Transaction::where('user_id', $user_id)
            ->where('status', 'success')
            ->get();

        return $this->generatePdf($transactions, "laporan-user-$user_id.pdf");
    }


    public function generatePdf($transactions, $fileName)
    {
        // fungsi reusable buat ngerender HTML jadi PDF dan langsung di-download
        $pdf = Pdf::loadView('ReportPDF', compact('transactions'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }

    public function generateExcel($transactions, $fileName)
    {
        // nge-generate file Excel dari data transaksi pake library PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Transaksi');
        $sheet->setCellValue('C1', 'User ID');
        $sheet->setCellValue('D1', 'Seller ID');
        $sheet->setCellValue('E1', 'Total Harga');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Tanggal Transaksi');

        // Isi data
        $row = 2;
        foreach ($transactions as $i => $trx) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $trx->kode_transaksi);
            $sheet->setCellValue("C{$row}", $trx->user_id);
            $sheet->setCellValue("D{$row}", $trx->user->role === 'seller' ? $trx->user->name : '-');
            $sheet->setCellValue("E{$row}", $trx->total_harga);
            $sheet->setCellValue("F{$row}", $trx->status);
            $sheet->setCellValue("G{$row}", $trx->tanggal_transaksi);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"$fileName\"",
            'Cache-Control' => 'max-age=0'
        ]);
    }

    // ini tuh bikin statistik
    public function getPeriodStatistic(Request $request) {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $sellerId = auth()->id();

        // Ambil data dari DB
        $transactionsFromDb = DetailTransaction::whereHas('product', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                      ->where('status', 'success');
            })
            ->selectRaw('DATE(created_at) as date, SUM(subtotal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // --- MODIFIKASI CARBON PERIOD ---
        $period = CarbonPeriod::create($startDate, $endDate);

        $finalData = [];
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            // Ambil dari DB kalau ada, kalau nggak ada kasih 0
            $finalData[$formattedDate] = $transactionsFromDb[$formattedDate] ?? 0;
        }
        // --------------------------------

        return response()->json([
            'message' => 'Berhasil menampilkan data statistik seller',
            'data' => $finalData // Struktur tetap sama: {"2026-04-01": 5000, "2026-04-02": 0}
        ]);
    }

    // ini tuh bikin statistik admin
    public function getAdminPeriodStatistic(Request $request) {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        // Hitung jarak hari untuk menentukan grouping dinamis
        $jarakHari = $startDate->diffInDays($endDate);

        if ($jarakHari <= 31) {
            // Jika kurang dari atau sama dengan sebulan, grouping per hari
            $sqlFormatTrx = 'DATE(tanggal_transaksi)';
            $sqlFormatUser = 'DATE(created_at)';
            $labelFormat = 'Harian';
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            $phpFormat = 'Y-m-d';
        } elseif ($jarakHari <= 365) {
            // Jika lebih dari sebulan sampai setahun, grouping per bulan
            $sqlFormatTrx = 'DATE_FORMAT(tanggal_transaksi, "%Y-%m")';
            $sqlFormatUser = 'DATE_FORMAT(created_at, "%Y-%m")';
            $labelFormat = 'Bulanan';
            $period = CarbonPeriod::create($startDate->copy()->startOfMonth(), '1 month', $endDate->copy()->startOfMonth());
            $phpFormat = 'Y-m';
        } else {
            // Jika lebih dari setahun, grouping per tahun
            $sqlFormatTrx = 'YEAR(tanggal_transaksi)';
            $sqlFormatUser = 'YEAR(created_at)';
            $labelFormat = 'Tahunan';
            $period = CarbonPeriod::create($startDate->copy()->startOfYear(), '1 year', $endDate->copy()->startOfYear());
            $phpFormat = 'Y';
        }

        // Siapkan array kosong berisi 0 untuk semua tanggal/bulan/tahun di periode tersebut
        $emptyData = [];
        foreach ($period as $dt) {
            $emptyData[$dt->format($phpFormat)] = 0;
        }

        // Hitung GMV (Gross Merchandise Value) alias Total Pendapatan Kotor Platform
        $gmvData = Transaction::whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->where('status', 'success')
            ->selectRaw("{$sqlFormatTrx} as date_group, SUM(total_harga) as total")
            ->groupBy('date_group')
            ->orderBy('date_group', 'asc')
            ->pluck('total', 'date_group')
            ->toArray();

        // Statistik jumlah pendaftar baru
        $userGrowth = User::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("{$sqlFormatUser} as date_group, COUNT(id) as total_user")
            ->groupBy('date_group')
            ->orderBy('date_group', 'asc')
            ->pluck('total_user', 'date_group')
            ->toArray();

        // Gabungkan (Merge) array kosong dengan data asli dari database
        $gmvData = array_replace($emptyData, $gmvData);
        $userGrowth = array_replace($emptyData, $userGrowth);

        $totalGmv = Transaction::whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->where('status', 'success')
            ->sum('total_harga');

        $totalNewUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();

        $totalSuccessTransactions = Transaction::whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->where('status', 'success')
            ->count();

        // Total seluruh toko yang ada di platform saat ini
        $totalStores = Toko::count();

        $topSellers = DetailTransaction::query()
            ->join('transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
            ->join('products', 'detail_transactions.product_id', '=', 'products.id')
            ->join('tokos', 'products.user_id', '=', 'tokos.user_id')
            ->whereBetween('transactions.tanggal_transaksi', [$startDate, $endDate])
            ->where('transactions.status', 'success')
            ->selectRaw('tokos.nama_toko, SUM(detail_transactions.subtotal) as total_pendapatan, COUNT(DISTINCT transactions.id) as total_pesanan')
            ->groupBy('tokos.id', 'tokos.nama_toko')
            ->orderBy('total_pendapatan', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'message' => 'Berhasil menampilkan data statistik admin',
            'grouping_type' => $labelFormat,
            'summary' => [
                'total_gmv' => (int) $totalGmv,
                'total_new_users' => $totalNewUsers,
                'total_success_transactions' => $totalSuccessTransactions,
                'total_stores' => $totalStores
            ],
            'data' => [
                'gmv_trend' => $gmvData,
                'user_growth' => $userGrowth,
                'top_sellers' => $topSellers
            ]
        ]);
    }

    public function adminPeriodicPdfReport(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfMonth();

        $transactions = Transaction::with('user')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->get();

        $withdraws = \App\Models\Withdraw::with('user.toko')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $pdf = Pdf::loadView('admin_periodic_report', compact('startDate', 'endDate', 'transactions', 'withdraws'))->setPaper('a4', 'landscape');
        $fileName = "Laporan-Admin-EMart-" . $startDate->format('Ymd') . "-" . $endDate->format('Ymd') . ".pdf";

        return $pdf->download($fileName);
    }

    public function sellerPeriodicPdfReport(Request $request)
    {
        $seller_id = auth()->id();

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfMonth();

        // Get seller info
        $seller = User::with('toko')->find($seller_id);
        $namaToko = $seller->toko ? $seller->toko->nama_toko : $seller->name;

        // Ambil dari DetailIncome, karena income baru masuk jika shipment sudah diterima
        $details = DetailIncome::with(['detailTransaction.transaction', 'detailTransaction.product'])
            ->whereHas('detailTransaction.product', fn($q) => $q->where('user_id', $seller_id))
            ->whereHas('detailTransaction.transaction', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                  ->where('status', 'success');
            })
            ->get();

        $pdf = Pdf::loadView('seller_periodic_report', compact('startDate', 'endDate', 'namaToko', 'details'))->setPaper('a4', 'portrait');
        $fileName = "laporan-pendapatan-seller-" . $startDate->format('Ymd') . "-" . $endDate->format('Ymd') . ".pdf";

        return $pdf->download($fileName);
    }
}
