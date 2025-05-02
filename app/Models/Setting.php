<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];

    // public function setValueAttribute($value)
    // {
    //     $this->attributes['value'] = Crypt::encryptString($value);
    // }

    // public function getValueAttribute($value)
    // {
    //     return Crypt::decryptString($value);
    // }

    // public static function getValue($name)
    // {
    //     $setting = self::where('name', $name)->first();
    //     return $setting ? $setting->value : null;
    // }

}
