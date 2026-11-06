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
                    ->range($this->nextSequenceNumber($prefix))
                    ->padLength(5)
            );
    }

    private function nextSequenceNumber(string $prefix): int
    {
        $highestNumber = static::query()
            ->where('code', 'like', "{$prefix}%")
            ->selectRaw('MAX(CAST(SUBSTRING(code, ?) AS UNSIGNED)) AS max_sequence', [strlen($prefix) + 1])
            ->value('max_sequence');

        return ((int) $highestNumber) + 1;
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
