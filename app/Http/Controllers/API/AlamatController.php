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
    public function get() {
        try {
            return 
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

    public function store(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
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
    //todo hapus
}
