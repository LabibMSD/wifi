<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('district.name')
                    ->label('District')
                    ->searchable(),
                TextColumn::make('village.name')
                    ->label('Village')
                    ->searchable(),
                TextColumn::make('hamlet.name')
                    ->label('Hamlet')
                    ->searchable(),
                TextColumn::make('package.speed_mbps')
                    ->label('Speed')
                    ->suffix(' Mbps')
                    ->searchable(),
                TextColumn::make('package.price')
                    ->label('Price')
                    ->money('IDR', decimalPlaces: 0)
                    ->searchable(),
                TextColumn::make('system.name')
                    ->label('System')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
