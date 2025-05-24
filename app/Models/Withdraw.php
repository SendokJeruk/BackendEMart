<?php

namespace App\Models;

use App\Models\Income;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Withdraw extends Model
{
    use HasFactory;
    protected $guarded = [];

        public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

}
