<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Income extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detail_incomes()
    {
        return $this->hasMany(DetailIncome::class);
    }

    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(Withdraw::class, 'withdraw_id');
    }
}
