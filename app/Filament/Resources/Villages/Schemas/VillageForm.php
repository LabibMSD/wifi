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
                    ->required(),
                Select::make('district_id')
                    ->label('District')
                    ->relationship('district', 'name')
                    ->required(),
                Select::make('prefix_area_id')
                    ->label('Prefix area')
                    ->relationship('prefixArea', 'prefix')
                    ->required(),
            ]);
    }
}
