<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
   protected $guarded =[];

   
   public function product(): BelongsTo
   {
       return $this->BelongsTo(User::class, 'product_id');
   }
   public function user(): BelongsTo
   {
       return $this->BelongsTo(User::class, 'user_id');
   }
}

