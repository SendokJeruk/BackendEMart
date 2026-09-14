<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailShipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_shipment',
        'detail_transaksi_id',
    ];
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'id_shipment');
    }

    public function detail_transaction()
    {
        return $this->belongsTo(DetailTransaction::class, 'detail_transaksi_id');
    }
}
