<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index() {
        try {
            $category = Category::paginate(10);
            return response()->json([
                'message' => 'Berhasil Dapatkan Data Produk',
                'data' => $category
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
            $validate = Validator::make($request->all(),[
                'nama_category' => 'required',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }
            $category = new Category();
            $category->nama_category = $request->input('nama_category');
            $category->save();
            return response()->json([
                'message' => 'Category Updated',
                'data' => $category
                ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, Category $category){
        try {
            $validate = Validator::make($request->all(),[
                'nama_category' => 'required',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $category->update([
                'nama_category' => $request->nama_category
            ]);

            return response()->json([
                'message' => 'Category Updated',
                'data' => $category
                ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function delete(Category $category)
    {
        try {
           $category->delete();

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

