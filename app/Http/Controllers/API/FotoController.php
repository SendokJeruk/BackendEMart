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

    public function index(){
       try {
        $foto = Foto::paginate(10);
        return response()->json([
            'message' => 'Berhasil Dapatkan Data foto',
            'data' => $foto
        ]);
       } catch (Exception $e) {
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
       }
    }
    public function store(Request $request){
        try {
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
                'message' => 'Berhasil Menambahkan foto',
                'data' => $foto
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Foto $foto){
        try {
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
                'message' => 'Berhasil Edit foto',
                'data' => $foto->fresh()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Foto $foto){
        try {
            $this->upload->delete($foto->Foto);
            $foto->delete();
            return response()->json([
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
