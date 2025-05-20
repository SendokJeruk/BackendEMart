<?php

namespace App\Repository;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class EncryptRepository
{
    public static function encrypt($value)
    {
        function randomString($length = 16)
        {
            $chars = 'stuzABUVWXYZ01CD67$%abce)FGHE#dQR89!@^&KLSTvwxy2345fghijk*(IJlmnopqrMNOP';
            return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
        }

        $first_random = randomString(7);
        $tanggal = Carbon::now()->format('d');
        $bulan   = Carbon::now()->format('m');
        $tahun   = Carbon::now()->format('Y');
        $jam     = Carbon::now()->format('H');
        $menit   = Carbon::now()->format('i');
        $detik   = Carbon::now()->format('s');
        $last_random = randomString(13);

        $enkryptValue = Crypt::encryptString(base64_encode($first_random.$tanggal.$bulan.$value.$tahun.$jam.$last_random.$menit.$detik));

        return $enkryptValue;
    }

    public static function decrypt($value) {
        $decrypt = Crypt::decryptString($value);
        $decrypt = base64_decode($decrypt);
        $length = strlen($decrypt);
        $lastlength = $length - 34;
        $realvalue = substr($decrypt, 11,$lastlength);

        return $realvalue;
    }
}
