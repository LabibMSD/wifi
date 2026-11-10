<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use App\Models\Package;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('customers.fields.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('general.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hamlet.village.district.name')
                    ->label(__('customers.fields.district'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hamlet.village.name')
                    ->label(__('customers.fields.village'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hamlet.name')
                    ->label(__('customers.fields.hamlet'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('package.display_name')
                    ->label(__('customers.fields.package'))
                    ->searchable(),
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
                SelectFilter::make('district')
                    ->label(__('customers.fields.district'))
                    ->relationship('hamlet.village.district', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('village')
                    ->label(__('customers.fields.village'))
                    ->relationship('hamlet.village', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('hamlet')
                    ->label(__('customers.fields.hamlet'))
                    ->relationship('hamlet', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('package')
                    ->label(__('customers.fields.package'))
                    ->column('package_id')
                    ->options(fn (): array => Package::query()
                        ->orderBy('speed_mbps')
                        ->get()
                        ->mapWithKeys(fn (Package $package) => [$package->id => $package->display_name])
                        ->all())
                    ->searchable()
                    ->multiple(),
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
