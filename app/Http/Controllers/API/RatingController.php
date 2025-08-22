<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repository\UploadRepository;


class RatingController extends Controller
{
    protected $upload;

    public function __construct()
    {
        $this->upload = new UploadRepository();
    }
    public function index()
    {

            $ratings = Rating::when(request('product_id'), function ($query, $product_id) {
                return $query->where('product_id', $product_id);
            })->get();

            return response()->json([
                'message' => 'Berhasil menampilkan data rating',
                'data' => $ratings
            ]);

    }

    public function store(Request $request)
    {

            $validate = Validator::make($request->all(), [
                'product_id' => 'required',
                'rating' => 'required|integer',
                'deskripsi' => 'nullable'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $rating = new Rating();

            if ($request->has('foto_review')) {
                $rating->foto_review = $this->upload->save($request->file('foto_review'));
            }

            $rating->user_id = auth()->id();
            $rating->product_id = $request->product_id;
            $rating->rating = $request->input('rating');
            $rating->deskripsi = $request->input('deskripsi');
            $rating->save();

            return response()->json([
                'message' => 'Berhasil menambahkan rating',
                'data' => $rating
            ], 200);


    }
}
