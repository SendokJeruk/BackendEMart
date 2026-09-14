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
        // ngejalanin fungsi __construct
        $this->enkrypt = new EncryptRepository();
    }
    public function enkrypt(Request $request) {
        $nilai = $request->nilai;
        $hasil = $this->enkrypt->encryptor($nilai);
        return response()->json([
            'sebelum' => $nilai,
            'sesudah' => $hasil
        ], );
    }
    public function decrypt(Request $request) {
        // ngetes decrypt data buat balikin jadi teks asli
        $value = $request->nilai;
        $hasil = $this->enkrypt->decryptor($value);
        return response()->json([
            'sebelum' => $value,
            'sesudah' => $hasil
        ], );
    }
}
