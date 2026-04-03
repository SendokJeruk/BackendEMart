<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Foto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use App\Http\Requests\Foto\StoreRequest;
use App\Http\Requests\Foto\UpdateRequest;

class FotoController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // ngejalanin fungsi __construct
        $this->upload = new UploadRepository();
    }

    public function index()
    {
        // nampilin semua data foto yang udah disimpen
        $foto = Foto::paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Photo data retrieved successfully',
            'data' => $foto
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngupload file foto ke storage trus simpen path-nya ke database
        $data = $request->all();
        $data['foto'] = $this->upload->save($request->file('foto'));
        $foto = Foto::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Photo added successfully',
            'data' => $foto
        ], 201);
    }

    public function update(UpdateRequest $request, Foto $foto)
    {
        // ngganti file foto lama sama yang baru kalo ada, trus update datanya
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
        // ngapus file foto dari storage sekalian datanya dari database
        $this->upload->delete($foto->Foto);
        $foto->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }
}
