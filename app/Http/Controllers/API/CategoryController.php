<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // nampilin semua kategori, bisa difilter pake nama kategori juga
        $query = Category::query();
        if ($request->has('nama_category')) {
            $query->where('nama_category', 'like', "%{$request->nama_category}%");
        }
        $category = $query->paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Category data retrieved successfully',
            'data' => $category
        ]);
    }

    public function store(StoreRequest $request)
    {
        // bikin kategori baru dan disimpen ke database
        $category = new Category();
        $category->nama_category = $request->input('nama_category');
        $category->save();
        return response()->json([
            'status' => 'Success',
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    public function update(UpdateRequest $request, Category $category)
    {
        // ngupdate nama kategori berdasarkan inputan
        $category->update([
            'nama_category' => $request->nama_category
        ]);
        return response()->json([
            'status' => 'Success',
            'message' => 'Data updated successfully',
            'data' => $category
        ], 200);
    }

    public function delete(Category $category)
    {
        // ngapus kategori dari database
        $category->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }
}
