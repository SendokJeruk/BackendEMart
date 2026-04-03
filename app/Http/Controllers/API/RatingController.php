<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use App\Http\Requests\Rating\StoreRequest;

class RatingController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // ngejalanin fungsi __construct
        $this->upload = new UploadRepository();
    }

    public function index()
    {
        // ngambil semua rating, bisa difilter per produk, beserta info user yang ngasih rating
        $ratings = Rating::when(request('product_id'), function ($query, $product_id) {
            return $query->where('product_id', $product_id);
        })->with('user')->get();

        return response()->json([
            'status' => 'Success',
            'message' => 'Rating data retrieved successfully',
            'data' => $ratings
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngupload foto review (kalo ada) trus nyimpen rating dan ulasan buat suatu produk
        $rating = new Rating();

        if ($request->has('foto_review')) {
            $rating->foto_review = $this->upload->save($request->file('foto_review'));
        }

        $rating->user_id = auth()->id();
        $rating->product_id = $request->product_id;
        $rating->rating = $request->input('rating');
        $rating->detail_transaction_id = $request->input('detail_transaction_id');
        $rating->deskripsi = $request->input('deskripsi');
        $rating->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Rating added successfully',
            'data' => $rating
        ], 201);
    }
}
