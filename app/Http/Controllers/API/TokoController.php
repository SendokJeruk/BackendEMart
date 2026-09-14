<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Toko;
use App\Models\User;
use App\Models\AlamatToko;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\Toko\StoreRequest;
use App\Http\Requests\Toko\UpdateAlamatRequest;
use App\Http\Requests\Toko\UpdateRequest;

class TokoController extends Controller
{
    public function index(Request $request)
    {
        // nampilin daftar toko sekalian info alamat dan produknya
        $query = Toko::query();
        if ($request->has('nama_toko')) {
            $query->where('nama_toko', 'like', "%{$request->nama_toko}%");
        }
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
        if ($request->has('self')) {
            $query->where('user_id', auth()->id());
        }

        $toko = $query->with(['alamatToko', 'products'])->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Store data retrieved successfully',
            'data' => $toko
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngecek user udah punya toko apa belum, trus bikin data toko sekalian alamatnya
        $user = Auth::user();
        if ($user->toko) {
            return response()->json([
                'message' => 'User already has a store',
            ], 422);
        }

        $toko = new Toko();
        $toko->user_id = auth()->id();
        $toko->nama_toko = $request->input('nama_toko');
        $toko->deskripsi = $request->input('deskripsi');
        $toko->no_telp = $request->input('no_telp');
        $toko->save();

        $alamat = new AlamatToko();
        $alamat->kode_domestik = $request->input('kode_domestik');
        $alamat->label = $request->input('label');
        $alamat->province_name = $request->input('province_name');
        $alamat->city_name = $request->input('city_name');
        $alamat->district_name = $request->input('district_name');
        $alamat->subdistrict_name = $request->input('subdistrict_name');
        $alamat->zip_code = $request->input('zip_code');
        $alamat->detail_alamat = $request->input('detail_alamat');
        $alamat->save();

        $toko->alamat_toko_id = $alamat->id;
        $toko->update();

        return response()->json([
            'status' => 'Success',
            'message' => 'Store created successfully',
            'data' => $toko
        ], 201);
    }

    public function updateAlamat(UpdateAlamatRequest $request, Toko $toko)
    {
        // pastiin pemilik toko, trus update detail alamat tokonya doang
        $toko->alamatToko->update([
            'kode_domestik' => $request->input('kode_domestik'),
            'label' => $request->input('label'),
            'province_name' => $request->input('province_name'),
            'city_name' => $request->input('city_name'),
            'district_name' => $request->input('district_name'),
            'subdistrict_name' => $request->input('subdistrict_name'),
            'zip_code' => $request->input('zip_code'),
            'detail_alamat' => $request->input('detail_alamat'),
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Store address updated successfully',
            'data' => $toko
        ], 200);
    }

    public function update(UpdateRequest $request, Toko $toko)
    {
        // ngupdate detail info toko kayak nama, deskripsi, atau no telp
        $toko->update([
            'user_id' => auth()->id(),
            'nama_toko' => $request->nama_toko,
            'deskripsi' => $request->deskripsi,
            'no_telp' => $request->no_telp,
            'alamat_toko_id' => $toko->alamat_toko_id
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Store updated successfully',
            'data' => $toko
        ], 200);
    }

    public function delete(Toko $toko)
    {
        // ngapus data alamat toko, trus lanjut ngapus data tokonya
        if ($toko->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $toko->alamatToko->delete();
        $toko->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Store and address data deleted successfully'
        ]);
    }
}
