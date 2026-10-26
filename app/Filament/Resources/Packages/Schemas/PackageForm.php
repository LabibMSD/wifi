<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('speed_mbps')
                    ->label('Speed Mbps')
                    ->suffix('Mbps')
                    ->required()
                    ->numeric(),
                TextInput::make('price')
                    ->label('Price')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->mask(RawJs::make(<<<'JS'
                        $money($input, ',', '.', 0)
                    JS))
                    ->stripCharacters('.'),
            ]);
    }
}
