<?php

namespace App\Filament\Resources\Hamlets;

use App\Filament\Resources\Hamlets\Pages\ListHamlets;
use App\Filament\Resources\Hamlets\Schemas\HamletForm;
use App\Filament\Resources\Hamlets\Tables\HamletsTable;
use App\Models\Hamlet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HamletResource extends Resource
{
    protected static ?string $model = Hamlet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('general.navigation.region');
    }

    public static function getModelLabel(): string
    {
        return __('hamlets.labels.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('hamlets.labels.plural_model');
    }

    public static function form(Schema $schema): Schema
    {
        return HamletForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HamletsTable::configure($table);
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
            'index' => ListHamlets::route('/'),
        ];
    }
}
