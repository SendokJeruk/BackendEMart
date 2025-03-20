<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_telp',
        'role',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];

    public function product(): HasMany
    {
        return $this->hasMany(related: Product::class, foreignKey: 'product_id');
    }
    public function transaction(): HasMany
    {
        return $this->hasMany(related: Transaction::class, foreignKey: 'transaction_id');
    }

    public function rating(): HasMany
    {
        return $this->hasMany(related: rating::class, foreignKey: 'rating_id');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
