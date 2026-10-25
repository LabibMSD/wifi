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
                Select::make('village_id')
                    ->label('Village')
                    ->relationship('village', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
            ]);
    }
}
