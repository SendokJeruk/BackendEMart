<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\AlamatUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class AlamatController extends Controller
{
    public function get()
    {
        $data = Auth::user()->alamat()->paginate(5);
        return response()->json([
            'status' => 'Success',
            'message' => 'Successfully retrieved all user address data',
            'data' => $data
        ], );
    }

    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'kode_domestik'    => 'required|integer',
            'label'            => 'required|string|max:100',
            'province_name'    => 'required|string|max:100',
            'city_name'        => 'required|string|max:100',
            'district_name'    => 'required|string|max:100',
            'subdistrict_name' => 'required|string|max:100',
            'zip_code'         => 'required|digits:5',
            'detail_alamat'    => 'required|string|max:255',
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
            'status' => 'Success',
            'message' => 'Address added successfully',
            'data' => $alamat
        ], 201);


    }

    //todo edit
    public function update(Request $request, AlamatUser $alamat)
    {
        if ($alamat->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }   

        $validate = Validator::make($request->all(), [
            'kode_domestik'    => 'required|integer',
            'label'            => 'required|string|max:100',
            'province_name'    => 'required|string|max:100',
            'city_name'        => 'required|string|max:100',
            'district_name'    => 'required|string|max:100',
            'subdistrict_name' => 'required|string|max:100',
            'zip_code'         => 'required|digits:5',
            'detail_alamat'    => 'nullable',
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
            'status' => 'Success',
            'message' => 'Data updated successfully',
            'data' => $alamat
        ], 200);


    }
    //todo hapus
    public function delete(AlamatUser $alamat)
    {
        if ($alamat->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $alamat->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ], 200);

    }
}
