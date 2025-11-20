<?php

namespace App\Filament\Resources\Ofertas\Pages;

use App\Filament\Resources\Ofertas\OfertaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOferta extends EditRecord
{
    protected static string $resource = OfertaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function mount(string|int $record): void
    {
        parent::mount($record);

        // Uzupełnij podsumowanie przy starcie
        $this->pollTotals();
    }

    /**
     * Proste auto-odświeżanie sum co 5 sekund poprzez polling.
     * Bierzemy aktualne wartości z bazy bez dodatkowych eventów.
     */
    public function pollTotals(): void
    {
        $this->record->refresh();

        $this->form->fill([
            'total_net' => round((float) $this->record->total_net, 2),
            'total_gross' => round((float) $this->record->total_gross, 2),
        ]);
    }
}
