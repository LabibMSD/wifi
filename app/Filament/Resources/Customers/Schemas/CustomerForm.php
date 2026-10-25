<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\District;
use App\Models\Hamlet;
use App\Models\Package;
use App\Models\Village;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->options(fn () => District::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->afterStateUpdated(function (Set $set) {
                        $set('village_id', null);
                        $set('hamlet_id', null);
                    })
                    ->required()
                    ->live(),
                Select::make('village_id')
                    ->label('Village')
                    ->options(fn (Get $get) => Village::where('district_id', $get('district_id'))
                        ->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->afterStateUpdated(fn (Set $set) => $set('hamlet_id', null))
                    ->disabled(fn (Get $get) => blank($get('district_id')))
                    ->required()
                    ->live(),
                Select::make('hamlet_id')
                    ->label('Hamlet')
                    ->options(fn (Get $get) => Hamlet::where('village_id', $get('village_id'))
                        ->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->disabled(fn (Get $get) => blank($get('village_id')))
                    ->required(),
                Select::make('package_id')
                    ->label('Package')
                    ->relationship('package')
                    ->getOptionLabelFromRecordUsing(fn (Package $record) => $record->display_name)
                    ->required(),
                Select::make('system_id')
                    ->label('System')
                    ->relationship('system', 'name')
                    ->required(),
            ]);
    }
}
