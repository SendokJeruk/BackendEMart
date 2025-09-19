<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $path = $request->file('foto_ktp')->getRealPath();
        $hash = hash_file('sha256', $path);

        return $hash;
    }
}
//!06f565bdb12deb1008f36a54ae555c582ae164875fb256aa3b1ddb18430e5412
//?06f565bdb12deb1008f36a54ae555c582ae164875fb256aa3b1ddb18430e5412
//* 
