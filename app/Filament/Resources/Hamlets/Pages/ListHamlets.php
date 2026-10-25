<?php

namespace App\Filament\Resources\Hamlets\Pages;

use App\Filament\Resources\Hamlets\HamletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHamlets extends ListRecords
{
    protected static string $resource = HamletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
