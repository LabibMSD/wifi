<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable(['speed_mbps', 'price'])]
class Package extends Model
{
    /** @use HasFactory<\Database\Factories\PackageFactory> */
    use HasFactory;

    #[Override]
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->speed_mbps} Mbps - Rp ".number_format($this->price, 0, ',', '.')
        );
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
