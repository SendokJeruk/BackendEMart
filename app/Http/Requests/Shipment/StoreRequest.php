<?php
namespace App\Http\Requests\Shipment;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'kode_transaksi' => 'required|unique:shipments,kode_transaksi',
            'status_pengiriman' => 'required|string|in:dibuat,dijadwalkan,kurir_ditugaskan,dalam_proses,tiba',
            'kode_resi' => 'nullable|string',
            'kurir' => 'nullable|string',
            'plat_nomor' => 'nullable|string',
            'estimasi_tiba' => 'nullable|date',
            'bukti_pengiriman' => 'nullable|string',
        ];
    }
        public function attributes() {
        return [
            'kode_transaksi' => 'kode transaksi',
            'status_pengiriman' => 'status pengiriman',
            'kode_resi' => 'kode resi',
            'kurir' => 'kurir',
            'plat_nomor' => 'plat nomor',
            'estimasi_tiba' => 'estimasi tiba',
            'bukti_pengiriman' => 'bukti pengiriman',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'string' => ':attribute harus berupa teks.',
            'in' => 'status pengiriman tidak valid.',
            'date' => 'format tanggal tidak valid.',
        ];
    }
}
