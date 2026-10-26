<?php

namespace App\Filament\Resources\PrefixAreas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Support\Str;

class PrefixAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('prefix')
                    ->label('Prefix')
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => Str::upper(trim($state)))
                    ->disabledOn(Operation::Edit)
                    ->helperText('Starting letters of customer codes (e.g. BW, PT, SK). Cannot be changed after saving.'),
            ]);
    }
}
