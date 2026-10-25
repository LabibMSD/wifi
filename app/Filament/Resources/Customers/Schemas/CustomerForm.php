<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Package;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->hiddenOn(Operation::Create)
                    ->disabled(),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Select::make('district_id')
                    ->label('District')
                    ->relationship('district', 'name')
                    ->required(),
                Select::make('village_id')
                    ->label('Village')
                    ->relationship('village', 'name')
                    ->required(),
                Select::make('hamlet_id')
                    ->label('Hamlet')
                    ->relationship('hamlet', 'name')
                    ->required(),
                Select::make('package_id')
                    ->label('Package')
                    ->relationship('package', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Package $record) => "{$record->speed_mbps} Mbps - Rp ".number_format($record->price, 0, ',', '.'))
                    ->required(),
                Select::make('system_id')
                    ->label('System')
                    ->relationship('system', 'name')
                    ->required(),
            ]);
    }
}
