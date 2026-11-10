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
                    ->label(__('prefix_areas.fields.prefix'))
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => Str::upper(trim($state)))
                    ->disabledOn(Operation::Edit)
                    ->helperText(__('prefix_areas.help.prefix')),
            ]);
    }
}
