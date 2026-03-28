<?php

namespace App\Models;

use App\Models\User;
use App\Models\Rating;
use App\Models\Category;
use App\Models\DetailTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nama_product',
        'deskripsi',
        'harga',
        'stock',
        'berat',
        'foto_cover',
        'terjual',
        'status_produk',
    ];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function detail_transaction(): HasMany
    {
        return $this->hasMany( DetailTransaction::class,  'product_id');
    }
    public function rating(): HasMany
    {
        return $this->hasMany( Rating::class,  'product_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_products', 'product_id', 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function foto(): BelongsToMany
    {
        return $this->belongsToMany(Foto::class, 'foto_products');
    }

    public function cart_detail(): HasMany
    {
        return $this->HasMany(Cart_detail::class, 'cartDetail_id');
    }


    public function scopeFilter($query, $request)
    {
        $nama = trim($request->nama_product ?? '');
        return $query
            ->when($nama !== '', fn($q) =>
            $q->where('nama_product', 'like', "%{$nama}%"))

            ->when($request->has('publish'), fn($q) =>
            $q->where('status_produk', 'publish'))

            ->when($request->has('myproducts'), fn($q) =>
            $q->where('user_id', Auth::user()->id))

            ->when($request->has('draft'), fn($q) =>
            $q->where('status_produk', 'draft'))

            ->when($request->filled('id'), fn($q) =>
            $q->where('id', $request->id))

            ->when($request->filled('user_id'), fn($q) =>
            $q->where('user_id', $request->user_id))

            ->when($request->filled('categories'), function ($q) use ($request) {
                $categoryIds = is_array($request->categories)
                    ? $request->categories
                    : explode(',', $request->categories);

                $q->whereHas('categories', function ($catQuery) use ($categoryIds) {
                    $catQuery->whereIn('categories.id', $categoryIds);
                }, '=', count($categoryIds));
            });
    }


    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
