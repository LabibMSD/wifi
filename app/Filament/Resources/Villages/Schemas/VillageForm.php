<?php

namespace App\Filament\Resources\Villages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VillageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('general.fields.name'))
                    ->required(),
                Select::make('district_id')
                    ->label(__('villages.fields.district'))
                    ->relationship('district', 'name')
                    ->required(),
                Select::make('prefix_area_id')
                    ->label(__('villages.fields.prefix_area'))
                    ->relationship('prefixArea', 'prefix')
                    ->required(),
            ]);
    }
}
