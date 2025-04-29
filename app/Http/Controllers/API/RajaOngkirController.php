<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\RajaOngkirService;

class RajaOngkirController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function domestic(Request $request)
    {
        $domestic = $request->get('search');
        return response()->json($this->rajaOngkir->getDomestic($domestic));
    }

    public function cost(Request $request)
    {
        $data = $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required|integer',
            'courier' => 'required',
            'price' => 'required'
        ]);

        return response()->json($this->rajaOngkir->getCost(
            $data['origin'],
            $data['destination'],
            $data['weight'],
            $data['courier'],
            $data['price']
        ));
    }

    public function track(Request $request)
    {
    $data = $request->validate([
        'waybill' => 'required|string',
        'courier' => 'required|string',
    ]);

    return response()->json($this->rajaOngkir->trackDelivery(
        $data['waybill'],
        $data['courier']
    ));
    }

}
