<?php

namespace App\Filament\Resources\ItemStandardResource\Pages;

use App\Filament\Resources\ItemStandardResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListItemStandards extends ListRecords
{
    protected static string $resource = ItemStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
