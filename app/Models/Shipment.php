<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;
    protected $guarded = [];
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

}
