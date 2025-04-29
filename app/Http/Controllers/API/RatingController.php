<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class RatingController extends Controller
{
    public function index(){
        try {
            
            $ratings = Rating::when(request('product_id'), function ($query, $product_id) {
                return $query->where('product_id', $product_id);
            })->get();

            return response()->json([
                'message' => 'Berhasil menampilkan data rating',
                'data' => $ratings
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
                'data' => $rating
                ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
