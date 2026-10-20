<?php

namespace App\Models;

use Hatchyu\Sequence\Support\SequenceColumnCollection;
use Hatchyu\Sequence\Support\SequenceConfig;
use Hatchyu\Sequence\Traits\HasSequence;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable(['name', 'district_id', 'village_id', 'hamlet_id', 'package_id', 'system_id'])]
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, HasSequence;
    #[Override]
    protected function sequenceColumns(): SequenceColumnCollection
    {
        $prefix = $this->village->prefixArea->prefix;

        return SequenceColumnCollection::collection()
            ->column(
                'code',
                SequenceConfig::create()
                    ->prefix($prefix)
                    ->groupBy($prefix)
                    ->padLength(5)
            );
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function hamlet(): BelongsTo
    {
        return $this->belongsTo(Hamlet::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
}
