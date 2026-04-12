<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RajaOngkirService;
use App\Http\Requests\RajaOngkir\CostRequest;
use App\Http\Requests\RajaOngkir\TrackRequest;

class RajaOngkirController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        // ngejalanin fungsi __construct
        $this->rajaOngkir = $rajaOngkir;
    }

    public function domestic(Request $request)
    {
        // nyari data kota atau provinsi buat keperluan pengiriman domestik via RajaOngkir
        $domestic = $request->get('search');
        return response()->json($this->rajaOngkir->getDomestic($domestic));
    }

    public function cost(CostRequest $request)
    {
        // ngecek harga ongkos kirim berdasarkan berat, asal, tujuan, sama kurir yang dipilih
        $data = $request->validated();
        return response()->json($this->rajaOngkir->getCost(
            $data['origin'],
            $data['destination'],
            $data['weight'],
            $data['courier'],
            $data['price']
        ));
    }

    public function track(TrackRequest $request)
    {
        // ngelacak resi pengiriman pake API RajaOngkir
        $data = $request->validated();
        return response()->json($this->rajaOngkir->trackShipment(
            $data['waybill'],
            $data['courier']
        ));
    }
}
