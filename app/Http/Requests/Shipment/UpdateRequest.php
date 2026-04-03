<?php
namespace App\Http\Requests\Shipment;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'kode_transaksi' => 'required',
            'status_pengiriman' => 'required|string|in:dibuat,dijadwalkan,kurir_ditugaskan,dalam_proses,tiba',
            'resi' => 'nullable|string',
            'ekspedisi' => 'nullable|string',
            'plat_nomor' => 'nullable|string',
            'estimasi_tiba' => 'nullable|date',
            'bukti_pengiriman' => 'nullable|string',
        ];
    }
        public function attributes() {
        return [
            'kode_transaksi' => 'kode transaksi',
            'status_pengiriman' => 'status pengiriman',
            'resi' => 'resi',
            'ekspedisi' => 'ekspedisi',
            'plat_nomor' => 'plat nomor',
            'estimasi_tiba' => 'estimasi tiba',
            'bukti_pengiriman' => 'bukti pengiriman',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'in' => 'status pengiriman tidak valid.',
            'date' => 'format tanggal tidak valid.',
        ];
    }
}
