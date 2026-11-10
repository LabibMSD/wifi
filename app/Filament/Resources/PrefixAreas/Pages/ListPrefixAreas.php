<?php

namespace App\Filament\Resources\PrefixAreas\Pages;

use App\Filament\Resources\PrefixAreas\PrefixAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrefixAreas extends ListRecords
{
    protected static string $resource = PrefixAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
