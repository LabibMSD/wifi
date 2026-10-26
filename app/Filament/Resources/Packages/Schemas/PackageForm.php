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
                    ->label(__('packages.fields.speed'))
                    ->suffix('Mbps')
                    ->required()
                    ->numeric(),
                TextInput::make('price')
                    ->label(__('packages.fields.price'))
                    ->prefix(config('business.currency_symbol'))
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
