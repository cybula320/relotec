<?php

namespace App\Filament\Resources\Zamowienies\Pages;

use App\Filament\Resources\Zamowienies\ZamowienieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListZamowienies extends ListRecords
{
    protected static string $resource = ZamowienieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
