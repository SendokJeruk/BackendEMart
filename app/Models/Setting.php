<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = Crypt::encryptString($value);
    }

    public function getValueAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function scopeFilter($query, $request)
    {
        return $query->where(function ($q) use ($request) {
            if ($request->has('server_midtrans')) {
                $q->orWhere('name', 'MIDTRANS_SERVER_KEY');
            }
            if ($request->has('client_midtrans')) {
                $q->orWhere('name', 'MIDTRANS_CLIENT_KEY');
            }
            if ($request->has('midtrans_prod')) {
                $q->orWhere('name', 'MIDTRANS_IS_PRODUCTION');
            }
            if ($request->has('rajaongkir_ship')) {
                $q->orWhere('name', 'RAJAONGKIR_SHIPPING_KEY');
            }
            if ($request->has('rajaongkir_delivery')) {
                $q->orWhere('name', 'RAJAONGKIR_DELIVERY_KEY');
            }
        });
    }

}
