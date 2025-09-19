<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;
   protected $guarded =[];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

   public function product(): BelongsTo
   {
       return $this->BelongsTo(User::class, 'product_id');
   }
   public function user(): BelongsTo
   {
       return $this->BelongsTo(User::class, 'user_id');
   }
}

