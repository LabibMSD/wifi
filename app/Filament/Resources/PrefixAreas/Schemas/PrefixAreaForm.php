<?php

namespace App\Filament\Resources\PrefixAreas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrefixAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('prefix')
                    ->label('Prefix')
                    ->required(),
                Textarea::make('descriptions')
                    ->label('Descriptions')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
