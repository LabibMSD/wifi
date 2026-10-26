<?php

namespace App\Filament\Resources\Villages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VillagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.fields.name'))
                    ->searchable(),
                TextColumn::make('district.name')
                    ->label(__('villages.fields.district'))
                    ->searchable(),
                TextColumn::make('prefixArea.prefix')
                    ->label(__('villages.fields.prefix_area'))
                    ->searchable(),
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
                //
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
