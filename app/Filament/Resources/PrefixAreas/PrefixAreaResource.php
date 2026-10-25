<?php

namespace App\Filament\Resources\PrefixAreas;

use App\Filament\Resources\PrefixAreas\Pages\ListPrefixAreas;
use App\Filament\Resources\PrefixAreas\Schemas\PrefixAreaForm;
use App\Filament\Resources\PrefixAreas\Tables\PrefixAreasTable;
use App\Models\PrefixArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrefixAreaResource extends Resource
{
    protected static ?string $model = PrefixArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'prefix';

    public static function form(Schema $schema): Schema
    {
        return PrefixAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrefixAreasTable::configure($table);
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
            'index' => ListPrefixAreas::route('/'),
        ];
    }
}
