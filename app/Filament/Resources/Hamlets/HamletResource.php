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

class HamletResource extends Resource
{
    protected static ?string $model = Hamlet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

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
