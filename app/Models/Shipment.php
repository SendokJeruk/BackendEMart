<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;
    protected $table = 'shipments';

    protected $fillable = [
        'kode_transaksi',
        'kurir',
        'plat_nomor',
        'kode_resi',
        'ongkir',
        'status_pengiriman',
        'estimasi_tiba',
        'tiba_di_tujuan',
        'bukti_pengiriman',
        'id_alamat_user',
        'id_toko',
    ];
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'kode_transaksi', 'kode_transaksi');
    }

    public function detail_shipments()
    {
        return $this->hasMany(DetailShipment::class, 'id_shipment');
    }

    public function history_shipments()
    {
        return $this->hasMany(HistoryShipment::class, 'id_shipment');
    }

    public function alamat()
    {
        return $this->belongsTo(AlamatUser::class, 'id_alamat_user')->withTrashed();
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }

}
