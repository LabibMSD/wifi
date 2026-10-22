<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('speed_mbps')
                    ->label('Speed Mbps')
                    ->required()
                    ->numeric(),
                TextInput::make('price')
                    ->label('Price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
            ]);
    }
}
