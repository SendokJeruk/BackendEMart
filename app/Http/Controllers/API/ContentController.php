<?php

namespace App\Http\Controllers\API;

use App\Models\Content;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;

class ContentController extends Controller
{
    protected $upload;

    public function __construct()
    {
        $this->upload = new UploadRepository();
    }

    public function getContent(Request $request)
    {
        $section = $request->query('section');

        $content = Content::where('section', $section)->first();

        if (!$content) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Content get successfully',
            'data' => $content
        ]);
    }

    public function storeContent(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'section' => 'required|in:login,dashboard',
        ]);

        $data = $request->only([
            'image',
            'section',
        ]);

        $data['image'] = $this->upload->save($request->file('foto_cover'));

        $content = Content::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Content added successfully',
            'data' => $content
        ], 201);
    }

    public function updateContent(Content $content, Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'section' => 'required|in:login,dashboard',
        ]);

        $data = $request->only([
            'image',
            'section',
        ]);

        if ($request->hasFile('foto_cover')) {
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
        $this->upload->delete($content->image);
        $content->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Content deleted successfully',
        ]);
    }
}
