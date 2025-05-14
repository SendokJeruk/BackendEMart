<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\AlamatUser;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function toko(): HasOne
    {
        return $this->hasOne(Toko::class);
    }

    public function alamat(): HasMany
    {
        return $this->hasMany(related: AlamatUser::class, foreignKey: 'user_id');
    }

        public function cart(): HasMany
    {
        return $this->HasMany(Cart::class, 'cart_id');
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
