<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Exports\CustomerExporter;
use App\Filament\Imports\CustomerImportAction;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            CustomerImportAction::make()
                ->importer(CustomerImporter::class),
            ExportAction::make()
                ->exporter(CustomerExporter::class)
                ->columnMapping(false)
                ->modifyQueryUsing(fn (Builder $query, array $options): Builder => CustomerExporter::applyFilters(
                    $query->with([
                        'hamlet.village.district',
                        'package',
                    ]),
                    $options,
                )),
        ];
    }
}
