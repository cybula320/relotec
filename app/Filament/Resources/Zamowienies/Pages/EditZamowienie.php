<?php

namespace App\Filament\Resources\Zamowienies\Pages;

use App\Filament\Resources\Zamowienies\ZamowienieResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditZamowienie extends EditRecord
{
    protected static string $resource = ZamowienieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
