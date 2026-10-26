<?php

namespace App\Filament\Resources\Districts;

use App\Filament\Resources\Districts\Pages\ListDistricts;
use App\Filament\Resources\Districts\Schemas\DistrictForm;
use App\Filament\Resources\Districts\Tables\DistrictsTable;
use App\Models\District;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Region';
    }

    public static function getModelLabel(): string
    {
        return 'District';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Districts';
    }

    public static function form(Schema $schema): Schema
    {
        return DistrictForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DistrictsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDistricts::route('/'),
        ];
    }
}
