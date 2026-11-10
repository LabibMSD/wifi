<?php

namespace App\Filament\Resources\Hamlets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HamletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('general.fields.name'))
                    ->required(),
                Select::make('village_id')
                    ->label(__('hamlets.fields.village'))
                    ->relationship('village', 'name')
                    ->required(),
            ]);
    }
}
