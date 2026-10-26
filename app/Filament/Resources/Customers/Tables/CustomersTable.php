<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
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
                    ->label(__('customers.fields.code'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('general.fields.name'))
                    ->searchable(),
                TextColumn::make('hamlet.village.district.name')
                    ->label(__('customers.fields.district'))
                    ->searchable(),
                TextColumn::make('hamlet.village.name')
                    ->label(__('customers.fields.village'))
                    ->searchable(),
                TextColumn::make('hamlet.name')
                    ->label(__('customers.fields.hamlet'))
                    ->searchable(),
                TextColumn::make('package.display_name')
                    ->label(__('customers.fields.package')),
                TextColumn::make('system')
                    ->label(__('customers.fields.system'))
                    ->state(Customer::SYSTEM),
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
