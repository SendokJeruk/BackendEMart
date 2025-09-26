<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{

    public function generateInvoice($kode_transaksi)
    {
        $transaction = Transaction::with(['user', 'detail_transaction.product', 'shipment'])
            ->where('kode_transaksi', $kode_transaksi)
            ->first();
        // return $transaction;
        $pdf = Pdf::loadView('invoice', ['transaction' => $transaction]);
        return $pdf->download("Invoice-{$transaction->kode_transaksi}-".now().".pdf");
    }

    public function adminMonthlyReport(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $transactions = Transaction::whereYear('tanggal_transaksi', $year)
            ->whereMonth('tanggal_transaksi', $month)
            ->get();
        return $this->generateExcel($transactions, "laporan-admin-$month-$year.xlsx");
    }

    public function sellerTransactionReport($seller_id)
    {
        $transactions = Transaction::whereHas('detail_transaction.product', function ($q) use ($seller_id) {
            $q->where('user_id', $seller_id);
        })
            ->with(['user', 'detail_transaction.product.seller'])
            ->get();


        $seller = \App\Models\User::find($seller_id);
        if (!$seller) {
            return abort(404, "Seller tidak ditemukan");
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $seller->name);

        return $this->generatePdf($transactions, "laporan-seller-$safeName.pdf");
    }



    public function userTransactionReport($user_id)
    {
        $transactions = Transaction::where('user_id', $user_id)
            ->where('status', 'success')
            ->get();

        return $this->generatePdf($transactions, "laporan-user-$user_id.pdf");
    }


    public function generatePdf($transactions, $fileName)
    {
        $pdf = Pdf::loadView('ReportPDF', compact('transactions'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }

    public function generateExcel($transactions, $fileName)
    {
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
}
