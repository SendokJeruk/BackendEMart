<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Toko;
use App\Models\User;
use App\Models\AlamatToko;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TokoController extends Controller
{
    public function index(Request $request)
    {

            $query = Toko::query();
            if ($request->has('nama_toko')) {
                $query->where('nama_toko', 'like', "%{$request->nama_toko}%");
            }
            if ($request->has('id')) {
                $query->where('id', $request->id);
            }

            $toko = $query->with('alamatToko')->paginate(10);

            return response()->json([
                'message' => 'Berhasil Dapatkan Data toko',
                'data' => $toko
            ]);

    }

    public function store(Request $request)
    {

            $validate = Validator::make($request->all(), [
                'nama_toko' => 'required|string|max:100',
                'deskripsi' => 'required|string|max:255',
                'no_telp' => 'required|max:12',

                //validator alamatnya
                'kode_domestik' => 'required',
                'label' => 'required',
                'province_name' => 'required',
                'city_name' => 'required',
                'district_name' => 'required',
                'subdistrict_name' => 'required',
                'zip_code' => 'required',
                'detail_alamat' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $user = Auth::user();
            if ($user->toko) {
                return response()->json([
                    'message' => 'User sudah memiliki toko',
                ], 422);
            }

            $toko = new Toko();
            $toko->user_id = auth()->id();
            $toko->nama_toko = $request->input('nama_toko');
            $toko->deskripsi = $request->input('deskripsi');
            $toko->no_telp = $request->input('no_telp');
            $toko->save();

            //alamat toko
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
                'message' => 'Toko telah terbuat',
                'data' => $toko
            ], 200);

    }

    public function updateAlamat(Request $request, Toko $toko)
    {

            $validate = Validator::make($request->all(), [
                'kode_domestik' => 'nullable',
                'label' => 'nullable',
                'province_name' => 'nullable',
                'city_name' => 'nullable',
                'district_name' => 'nullable',
                'subdistrict_name' => 'nullable',
                'zip_code' => 'nullable',
                'detail_alamat' => 'nullable',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

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
                'message' => 'Alamat Toko telah diperbarui',
                'data' => $toko
            ], 200);


    }

    public function update(Request $request, Toko $toko)
    {

            $validate = Validator::make($request->all(), [
                'nama_toko' => 'required|string|max:100',
                'deskripsi' => 'required|string|max:255',
                'no_telp' => 'required|max:12',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $toko->update([
                'user_id' => auth()->id(),
                'nama_toko' => $request->nama_toko,
                'deskripsi' => $request->deskripsi,
                'no_telp' => $request->no_telp,
                'alamat_toko_id' => $toko->alamat_toko_id
            ]);

            return response()->json([
                'message' => 'Toko telah diperbarui',
                'data' => $toko
            ], 200);


    }

    public function delete(Toko $toko)
    {

            $toko->alamatToko->delete();
            $toko->delete();

            return response()->json([
                'message' => 'Data toko beserta alamat berhasil dihapus'
            ]);

    }

}


