<?php

namespace App\Filament\Resources\ItemStandardResource\Pages;

use App\Filament\Resources\ItemStandardResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditItemStandard extends EditRecord
{
    protected static string $resource = ItemStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
