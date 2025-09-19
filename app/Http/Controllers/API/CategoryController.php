<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {

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

    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'nama_category' => 'required|max:100',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }
        $category = new Category();
        $category->nama_category = $request->input('nama_category');
        $category->save();
        return response()->json([
            'status' => 'Success',
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);


    }
    public function update(Request $request, Category $category)
    {

        $validate = Validator::make($request->all(), [
            'nama_category' => 'required|max:100',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

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

        $category->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);

    }
}

