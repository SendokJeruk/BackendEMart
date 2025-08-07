<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailIncome extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function income()
    {
       return $this->belongsTo(Income::class);
    }

    public function detailTransaction()
    {
       return $this->belongsTo(DetailTransaction::class);
    }
}
