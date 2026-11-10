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
            'price' => 'decimal:0',
        ];
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => __('packages.values.display_name', [
                'speed' => $this->speed_mbps,
                'price' => config('business.currency_symbol').' '.number_format($this->price, 0, ',', '.'),
            ])
        );
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
