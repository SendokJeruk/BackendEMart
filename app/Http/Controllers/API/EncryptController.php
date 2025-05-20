<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repository\EncryptRepository;
use Illuminate\Http\Request;

class EncryptController extends Controller
{
    protected $enkrypt;

    public function __construct()
    {
        $this->enkrypt = new EncryptRepository();
    }
    public function enkrypt(Request $request) {
        $nilai = $request->nilai;
        $hasil = $this->enkrypt->encrypt($nilai);
        return response()->json([
            'sebelum' => $nilai,
            'sesudah' => $hasil
        ], );
    }
    public function decrypt(Request $request) {
        $value = $request->nilai;
        $hasil = $this->enkrypt->decrypt($value);
        return response()->json([
            'sebelum' => $value,
            'sesudah' => $hasil
        ], );
    }
}
