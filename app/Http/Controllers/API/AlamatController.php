<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\AlamatUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AlamatController extends Controller
{
    public function store(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'user_id' => 'required',
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
}
