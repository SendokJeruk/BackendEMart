<?php
namespace App\Http\Controllers\API;

use App\Models\Content;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use App\Http\Requests\Content\StoreRequest;
use App\Http\Requests\Content\UpdateRequest;

class ContentController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // ngejalanin fungsi __construct
        $this->upload = new UploadRepository();
    }

    public function getAllContent() {
        $contents = Content::paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Get All successfully',
            'data' => $contents
        ]);
    }

    public function getContent(Request $request)
    {
        // ngambil konten spesifik berdasarkan section (misal: login atau dashboard)
        $section = $request->query('section');
        $content = Content::where('section', $section)->get();
        if (!$content) {
            return response()->json(['message' => 'Content not found'], 404);
        }
        return response()->json([
            'status' => 'Success',
            'message' => 'Content get successfully',
            'data' => $content
        ]);
    }

    public function storeContent(StoreRequest $request)
    {
        // ngupload gambar baru dan nyimpen data konten beserta section-nya
        $data = $request->only(['image', 'section']);
        $data['image'] = $this->upload->save($request->file('image'));
        $content = Content::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Content added successfully',
            'data' => $content
        ], 201);
    }

    public function updateContent(Content $content, UpdateRequest $request)
    {
        // kalo ada gambar baru diupload, gambar lama diganti, trus update data kontennya
        $data = $request->only(['image', 'section']);
        if ($request->hasFile('image')) {
            $data['image'] = $this->upload->update($content->image, $request->file('image'));
        }
        $content->update($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Content updated successfully',
            'data' => $content->fresh()
        ]);
    }

    public function deleteContent(Content $content)
    {
        // ngapus gambar dari storage dan hapus data konten dari database
        $this->upload->delete($content->image);
        $content->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Content deleted successfully',
        ]);
    }
}
