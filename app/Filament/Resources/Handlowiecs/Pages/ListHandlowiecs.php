<?php

namespace App\Filament\Resources\Handlowiecs\Pages;

use App\Filament\Resources\Handlowiecs\HandlowiecResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHandlowiecs extends ListRecords
{
    protected static string $resource = HandlowiecResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
