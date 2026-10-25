<?php

namespace App\Models;

use DB;
use Hatchyu\Sequence\Support\SequenceColumnCollection;
use Hatchyu\Sequence\Support\SequenceConfig;
use Hatchyu\Sequence\Traits\HasSequence;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable(['name', 'hamlet_id', 'package_id'])]
class Customer extends Model
{
    public const SYSTEM = 'PPOE';

    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, HasSequence;
    #[Override]
    protected function sequenceColumns(): SequenceColumnCollection
    {
        $prefix = $this->hamlet->village->prefixArea->prefix;

        return SequenceColumnCollection::collection()
            ->column(
                'code',
                SequenceConfig::create()
                    ->prefix($prefix)
                    ->groupBy($prefix)
                    ->padLength(5)
            );
    }

    public function save(array $options = []): bool
    {
        return DB::transaction(fn () => parent::save($options));
    }

    public function hamlet(): BelongsTo
    {
        return $this->belongsTo(Hamlet::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
