<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Withdraw;
use App\Models\AlamatUser;
use App\Models\RequestSeller;
use App\Models\SellerBalance;
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
        'role_id',
        'foto_profil',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];


    public function product(): HasMany
    {
        return $this->hasMany(related: Product::class, foreignKey: 'user_id');
    }
    public function transaction(): HasMany
    {
        return $this->hasMany(related: Transaction::class, foreignKey: 'user_id');
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

    public function cart(): hasOne
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    public function income(): hasOne
    {
        return $this->hasOne(Income::class, 'user_id');
    }

    public function balance(): hasOne
    {
        return $this->hasOne(SellerBalance::class, 'user_id');
    }

    public function RequestSeller(): hasOne
    {
        return $this->hasOne(RequestSeller::class);
    }
    public function withdraw(): hasMany
    {
        return $this->hasMany(Withdraw::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'timestamps',
        'created_at',
        'updated_at',
        'email_verified_at',
        'google_id',
        'google_refresh_token',
        'google_token'
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
