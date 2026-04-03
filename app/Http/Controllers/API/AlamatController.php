<?php
namespace App\Http\Controllers\API;
use Exception;
use App\Models\AlamatUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\Alamat\StoreRequest;
use App\Http\Requests\Alamat\UpdateRequest;

class AlamatController extends Controller
{
    public function get()
    {
        // ngambil data alamat user yang lagi login trus dipaginasi biar rapi
        $data = Auth::user()->alamat()->paginate(5);
        return response()->json([
            'status' => 'Success',
            'message' => 'Successfully retrieved all user address data',
            'data' => $data
        ]);
    }

    public function store(StoreRequest $request)
    {
        // nyimpen data alamat baru yang udah divalidasi ke database, langsung dikaitin sama user yang login
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $alamat = AlamatUser::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Address added successfully',
            'data' => $alamat
        ], 201);
    }

    public function update(UpdateRequest $request, AlamatUser $alamat)
    {
        // ngecek dulu apa alamat ini beneran punya user, kalo iya baru deh diupdate datanya
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

    public function delete(AlamatUser $alamat)
    {
        // pastiin alamat punya user yang login, kalo aman langsung dihapus dari database
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
