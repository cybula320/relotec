<?php

namespace App\Filament\Resources\Handlowiecs\Pages;

use App\Filament\Resources\Handlowiecs\HandlowiecResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditHandlowiec extends EditRecord
{
    protected static string $resource = HandlowiecResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
