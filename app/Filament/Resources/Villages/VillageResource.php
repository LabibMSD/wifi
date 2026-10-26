<?php

namespace App\Filament\Resources\Villages;

use App\Filament\Resources\Villages\Pages\ListVillages;
use App\Filament\Resources\Villages\Schemas\VillageForm;
use App\Filament\Resources\Villages\Tables\VillagesTable;
use App\Models\Village;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Region';
    }

    public static function getModelLabel(): string
    {
        return 'Village';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Villages';
    }

    public static function form(Schema $schema): Schema
    {
        return VillageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillagesTable::configure($table);
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
            'index' => ListVillages::route('/'),
        ];
    }
}
