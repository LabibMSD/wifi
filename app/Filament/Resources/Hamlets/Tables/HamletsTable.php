<?php

namespace App\Filament\Resources\Hamlets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HamletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('village.name')
                    ->label(__('hamlets.fields.village'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('village.district.name')
                    ->label(__('hamlets.fields.district'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('general.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('general.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('village')
                    ->label(__('hamlets.fields.village'))
                    ->relationship('village', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('district')
                    ->label(__('hamlets.fields.district'))
                    ->relationship('village.district', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
