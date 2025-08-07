<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\AlamatUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlamatController extends Controller
{
    public function get()
    {
        try {

            $data = Auth::user()->alamat()->paginate(5);
            return response()->json([
                'message' => 'Berhasil mendapatkan semua data alamat user',
                'data' => $data
            ], );
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'kode_domestik' => 'required|integer',
                'label' => 'required',
                'province_name' => 'required',
                'city_name' => 'required',
                'district_name' => 'required',
                'subdistrict_name' => 'required',
                'zip_code' => 'required|integer',
                'detail_alamat' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = $request->all();
            $data['user_id'] = auth()->id();
            $alamat = AlamatUser::create($data);


            return response()->json([
                'message' => 'Berhasil Menambahkan Alamat',
                'data' => $alamat
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //todo edit
    public function update(Request $request, AlamatUser $alamat)
    {
        try {
            $validate = Validator::make($request->all(), [
                'kode_domestik' => 'required|integer',
                'label' => 'required',
                'province_name' => 'required',
                'city_name' => 'required',
                'district_name' => 'required',
                'subdistrict_name' => 'required',
                'zip_code' => 'required|integer',
                'detail_alamat' => 'nullable',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $alamat->update([
                'kode_domestik' => $request->kode_domestik,
                'label' => $request->label,
                'province_name' => $request->province_name,
                'city_name' => $request->city_name,
                'district_name' => $request->district_name,
                'subdistrict_name' => $request->subdistrict_name,
                'zip_code' => $request->zip_code,
                'detail_alamat' => $request->detail_alamat
            ]);

            return response()->json([
                'message' => 'Data berhasil di update',
                'data' => $alamat
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //todo hapus
    public function delete(AlamatUser $alamat)
    {
        try {
            $alamat->delete();

            return response()->json([
                'message' => 'data berhasil di hapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
