<?php

namespace App\Filament\Widgets;

use App\Models\Oferta;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MojeOfertySkziceWidget extends TableWidget
{
    //protected static ?string $heading = null;

    protected static bool $isHeaderVisible = true;
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Oferty przypisane do Ciebie - Szkice';

 
    protected function getTableQuery(): ?Builder
    {
        return Oferta::query()
            ->with(['firma', 'user', 'paymentMethod'])
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->latest();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                // 🎯 1. NUMER OFERTY - główne info na początku
                TextColumn::make('numer')
                    ->label('Numer oferty')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                   // ->copyable()
                    ->copyMessage('Numer skopiowany!')
                    ->tooltip('Edytuj')
                    ->url(fn (Oferta $record): string => route('filament.panel.resources.ofertas.edit', $record))
                    ->openUrlInNewTab()
                    ->description('Edytuj ofertę')
                    ->icon('heroicon-o-pencil-square')
                    ->iconColor('gray'),

                // 🗓️ 2. DATA UTWORZENIA - jako druga najważniejsza info
                TextColumn::make('created_at')
                    ->label('Data utworzenia')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->since()
                    ->description(fn (Oferta $record): string => 'przez ' . ($record->user->name ?? 'System'))
                    ->icon('heroicon-o-plus-circle')
                    ->iconColor('green')
                    ->tooltip(fn (Oferta $record): string => 'Utworzona: ' . $record->created_at->format('d.m.Y H:i:s')),

                // 🏢 3. FIRMA - kluczowa info biznesowa
                TextColumn::make('firma.nazwa')
                    ->label('Firma')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Oferta $record): string => $record->firma->email ?? '')
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('gray')
                    ->weight('semibold')
                    ->limit(30)
                    ->tooltip(fn (Oferta $record): string => $record->firma->nazwa),

                // 🎯 4. STATUS - bardzo ważny dla workflow
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                    ])
                    ->icons([
                        'heroicon-o-pencil-square' => 'draft',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Szkic',
                        default => ucfirst($state),
                    }),

                // 💰 5. WARTOŚĆ BRUTTO - główna wartość finansowa
                TextColumn::make('total_gross')
                    ->label('Wartość')
                    ->money('PLN', true)
                    ->sortable()
                    ->alignment(Alignment::End)
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (Oferta $record): string => 'netto: ' . number_format($record->total_net, 2, ',', ' ') . ' PLN'),

                // 💳 6. WALUTA
                TextColumn::make('waluta')
                    ->label('Waluta')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'PLN' => 'success',
                        'EUR' => 'warning',
                        'USD' => 'info',
                        default => 'gray'
                    }),

                // 💳 7. SPOSÓB PŁATNOŚCI
                TextColumn::make('paymentMethod.name')
                    ->label('Płatność')
                    ->badge()
                    ->color('info')
                    ->placeholder('Nie ustawiono')
                    ->limit(20)
                    ->tooltip(fn (Oferta $record): string => $record->paymentMethod?->name ?? 'Nie ustawiono metody płatności'),

                // 📊 8. LICZBA POZYCJI
                TextColumn::make('pozycje_count')
                    ->label('Pozycje')
                    ->counts('pozycje')
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-list-bullet')
                    ->alignment(Alignment::Center),

                // ⏱️ 9. OSTATNIA MODYFIKACJA - dla info o aktualności
                TextColumn::make('updated_at')
                    ->label('Modyfikacja')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-pencil')
                    ->iconColor('amber')
                    ->tooltip(fn (Oferta $record): string => 'Ostatnia zmiana: ' . $record->updated_at->format('d.m.Y H:i:s')),

                // 📋 10. UWAGI - jeśli są
                TextColumn::make('uwagi')
                    ->label('Uwagi')
                    ->limit(30)
                    ->placeholder('Brak uwag')
                    ->color('gray')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->iconColor('blue')
                    ->tooltip(fn (Oferta $record): string => $record->uwagi ?? 'Brak uwag')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Podgląd')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Oferta $record): string => route('filament.panel.resources.ofertas.view', $record))
                        ->openUrlInNewTab(),
                        
                    Action::make('edit')
                        ->label('Edytuj')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->url(fn (Oferta $record): string => route('filament.panel.resources.ofertas.edit', $record))
                        ->openUrlInNewTab(),

                    Action::make('duplicate')
                        ->label('Duplikuj')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->action(function (Oferta $record) {
                            // Logika duplikowania oferty
                            $newOferta = $record->replicate();
                            $newOferta->numer = \App\Helpers\OfferNumberHelper::generate();
                            $newOferta->status = 'draft';
                            $newOferta->created_at = now();
                            $newOferta->updated_at = now();
                            $newOferta->save();

                            // Duplikuj pozycje
                            foreach ($record->pozycje as $pozycja) {
                                $newPozycja = $pozycja->replicate();
                                $newPozycja->oferta_id = $newOferta->id;
                                $newPozycja->save();
                            }

                            $newOferta->recalcTotals();

                            $this->redirect(route('filament.panel.resources.ofertas.edit', $newOferta));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Duplikuj ofertę')
                        ->modalDescription('Czy na pewno chcesz zduplikować tę ofertę? Zostanie utworzona nowa oferta z wszystkimi pozycjami.')
                        ->modalSubmitActionLabel('Tak, duplikuj'),

                    Action::make('delete')
                        ->label('Usuń')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(fn (Oferta $record) => $record->delete())
                        ->requiresConfirmation()
                        ->modalHeading('Usuń ofertę')
                        ->modalDescription('Czy na pewno chcesz usunąć tę ofertę? Tej operacji nie można cofnąć.')
                        ->modalSubmitActionLabel('Usuń'),
                ])
                ->label('Więcej')
                ->icon('heroicon-o-ellipsis-vertical')
                ->size('sm')
                ->button()
                ->outlined(),
            ])
            ->emptyStateHeading('🎯 Brak szkiców ofert')
            ->emptyStateDescription('Nie masz jeszcze żadnych szkiców ofert. Rozpocznij sprzedaż tworząc nową ofertę!')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Action::make('create_offer')
                    ->label('Utwórz pierwszą ofertę')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->size('lg')
                    ->url(route('filament.panel.resources.ofertas.create'))
                    ->button(),
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->reorderable('sort')
            ->searchable();
    }
}