<?php

namespace App\Filament\Resources\Villages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;


class VillagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('district.name')
                    ->label(__('villages.fields.district'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prefixArea.prefix')
                    ->label(__('villages.fields.prefix_area'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hamlets_count')
                    ->label(__('villages.fields.hamlets'))
                    ->counts('hamlets')
                    ->badge()
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
                SelectFilter::make('district')
                    ->label(__('villages.fields.district'))
                    ->relationship('district', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('prefix_area')
                    ->label(__('villages.fields.prefix_area'))
                    ->relationship('prefixArea', 'prefix')
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
