<?php

namespace App\Filament\Resources\Ofertas\Pages;

use App\Filament\Resources\Ofertas\OfertaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;

class EditOferta extends EditRecord
{
    protected static string $resource = OfertaResource::class;

    // ⭐ Auto-save draft co 30 sekund
    protected static ?string $pollingInterval = '30s';

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
        $this->refreshTotals();
    }

    /**
     * Nasłuchiwanie eventu z RelationManager
     * Odświeża sumy po każdej zmianie pozycji
     */
    #[On('totals-updated')]
    public function refreshTotals(): void
    {
        $this->record->refresh();

        $this->form->fill([
            'total_net' => round((float) $this->record->total_net, 2),
            'total_gross' => round((float) $this->record->total_gross, 2),
        ]);
    }

    /**
     * 💾 Auto-save draft - zapisuje zmiany automatycznie
     */
    protected function afterFill(): void
    {
        // Włącz auto-save tylko dla szkiców
        if ($this->record->status === 'draft') {
            $this->dispatch('enable-autosave');
        }
    }

    /**
     * Customowy zapis z powiadomieniem o auto-save
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Oferta zapisana')
            ->body('Zmiany zostały automatycznie zapisane.')
            ->duration(2000);
    }

    /**
     * Wyłącz redirect po zapisie (pozostań na stronie edycji)
     */
    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
