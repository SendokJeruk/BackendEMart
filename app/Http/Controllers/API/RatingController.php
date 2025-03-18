<?php

namespace App\Http\Controllers\API;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Validator;

class RatingController extends Controller
{
    public function index(){
        try {
            $rating = Rating::paginate(10);
            return response()->json([
                'message' => 'Berhasil menampilkan data rating',
                'data' => $category
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Requets $request){
        try {
            $validate = Validator::make($request->all(),[
                'user_id' => 'required',
                'product_id' => 'required',
                'rating' => 'required',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }
            $rating = new Rating();
            $rating->user_id = $request->user_id;
            $rating->product_id = $request->product_id;
            $rating->rating = $request->input('rating');
            $rating->save();
            return response()->json([
                'message' => 'Berhasil menambahkan rating',
                'rating' => $rating
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
