<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlamatUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_penerima',
        'kode_domestik',
        'label',
        'province_name',
        'city_name',
        'district_name',
        'subdistrict_name',
        'zip_code',
        'detail_alamat',
    ];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->HasMany(Transaction::class, 'id_alamat_user');
    }

}
