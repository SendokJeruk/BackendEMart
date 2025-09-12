<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Foto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use Illuminate\Support\Facades\Validator;

class FotoController extends Controller
{
    protected $upload;

    public function __construct()
    {
        $this->upload = new UploadRepository();
    }

    public function index()
    {

        $foto = Foto::paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Photo data retrieved successfully',
            'data' => $foto
        ]);
    }

    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'foto' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
        ]);


        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();
        $data['foto'] = $this->upload->save($request->file('foto'));
        $foto = foto::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Photo added successfully',
            'data' => $foto
        ],201 );
    }

    public function update(Request $request, Foto $foto)
    {

        $validate = Validator::make($request->all(), [
            'foto' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();

        if ($request->file('foto')) {
            $data['foto'] = $this->upload->update($foto->foto, $request->file('foto'));
        }

        $foto->update($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Photo updated successfully',
            'data' => $foto->fresh()
        ]);

    }

    public function delete(Foto $foto)
    {

        $this->upload->delete($foto->Foto);
        $foto->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);

    }

}
