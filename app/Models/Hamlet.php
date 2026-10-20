<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['village_id', 'name'])]
class Hamlet extends Model
{
    /** @use HasFactory<\Database\Factories\HamletFactory> */
    use HasFactory;

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
