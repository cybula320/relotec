<?php

namespace App\Filament\Resources\Ofertas\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\View\View;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\RecordActionsPosition;
 use Filament\Tables\Enums\RecordActionPosition;
use Filament\Actions\ActionGroup;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Oferta;
use App\Helpers\OfferNumberHelper;

class OfertasTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->defaultPaginationPageOption(50)
        ->paginated([10, 25, 50, 100, 'all'])
        ->paginatedWhileReordering()
        ->description('Lista wszystkich ofert w systemie. Kliknij na ofertę, aby zobaczyć szczegóły.')
        ->groups([
            // Grupowanie po numerze bazowym oferty (bez litery korekty)
            Group::make('numer', 'base_number')
                ->label('Oferta (z korektami)')
                ->getTitleFromRecordUsing(function (Oferta $record): string {
                    // numer bazowy to numer oferty głównej lub numer bez litery dla korekty
                    $base = $record->parentOferta?->numer ?? $record->numer;

                    // jeśli to korekta (np. 1A/10/2025), wyciągamy sam numer bazowy (1/10/2025)
                    if ($record->isCorrection()) {
                        [$first, $rest] = explode('/', $base, 2);
                        // usuwamy literę z końca pierwszego segmentu (np. 1A -> 1)
                        $first = preg_replace('/[A-Z]+$/', '', $first);
                        $base = $first . '/' . $rest;
                    }

                    return $base;
                }),

            Group::make('status')
                ->label('Status oferty'),
            Group::make('firma.nazwa')
                ->label('Firma'),
        ])
       
            ->columns([
                // 📄 Numer oferty
                TextColumn::make('numer')
                    ->label('Numer oferty')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-hashtag')
                    ->weight('bold'),

                                // 🧩 Status
                                BadgeColumn::make('status')
                                ->label('Status')
                                ->sortable()
                                ->colors([
                                    'gray' => 'draft',
                                    'info' => 'sent',
                                    'success' => 'accepted',
                                    'danger' => 'rejected',
                                    'warning' => 'converted',
                                ])
                                ->icons([
                                    'heroicon-o-pencil-square' => 'draft',
                                    'heroicon-o-paper-airplane' => 'sent',
                                    'heroicon-o-check-circle' => 'accepted',
                                    'heroicon-o-x-circle' => 'rejected',
                                    'heroicon-o-arrow-path' => 'converted',
                                ])
                                ->formatStateUsing(fn($state) => match ($state) {
                                    'draft' => 'Szkic',
                                    'sent' => 'Wysłana',
                                    'accepted' => 'Zaakceptowana',
                                    'rejected' => 'Odrzucona',
                                    'converted' => 'Zamówienie',
                                    default => ucfirst($state),
                                }),

                // 🏢 Firma
                TextColumn::make('firma.nazwa')
                    ->label('Firma')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-building-office')
                    ->tooltip(fn($record) => $record->firma?->email ?? 'Brak e-maila'),

                // 👤 Handlowiec
                TextColumn::make('handlowiec.nazwisko')
                    ->label('Handlowiec')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state, $record) =>
                        $record->handlowiec
                            ? "{$record->handlowiec->imie} {$record->handlowiec->nazwisko}"
                            : '—'
                    )
                    ->icon('heroicon-o-user'),

                TextColumn::make('user.name')
                    ->label('Opiekun (użytkownik)')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-user-circle')
                    ->formatStateUsing(fn($state, $record) => $record->user?->name ?? '—'),

                // 💰 Kwoty
                TextColumn::make('total_net')
                    ->label('Netto')
                    ->money(fn($record) => $record->waluta ?? 'PLN')
                    ->sortable()
                    ->alignRight()
                    ->extraAttributes(['class' => 'text-blue-600 dark:text-blue-400 font-semibold']),

                TextColumn::make('total_gross')
                    ->label('Brutto')
                    ->money(fn($record) => $record->waluta ?? 'PLN')
                    ->sortable()
                    ->alignRight()
                    ->extraAttributes(['class' => 'text-green-700 dark:text-green-400 font-semibold']),

                // 💳 Waluta
                TextColumn::make('waluta')
                    ->label('Waluta')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                // // 📅 Daty
                // TextColumn::make('due_date')
                //     ->label('Termin płatności')
                //     ->date()
                //     ->sortable()
                //     ->color(fn($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'gray'),

                TextColumn::make('paymentMethod.nazwa')
                ->label('Metoda płatności')
                ->sortable()
                ->searchable()
                ->icon('heroicon-o-credit-card')
                ->tooltip(fn($record) => $record->paymentMethod?->opis ?? null)
                ->formatStateUsing(fn($state) => $state ?? '—')
                ->color(fn ($state) => $state ? 'gray' : 'danger')
                ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Data utworzenia')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),



                TextColumn::make('parentOferta.numer')
                    ->label('Korekta do')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('correction_letter')
                    ->label('Litera korekty')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ] )
            ->filters([
                // Status oferty
                SelectFilter::make('status')
                    ->label('Status oferty')
                    ->options([
                        'draft'     => 'Szkic',
                        'sent'      => 'Wysłana',
                        'accepted'  => 'Zaakceptowana',
                        'rejected'  => 'Odrzucona',
                        'converted' => 'Zamówienie',
                    ])
                    ->indicator('Status')
                    ->placeholder('Wszystkie statusy')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('firma_id')
                    ->label('Firma')
                    ->relationship('firma', 'nazwa')
                    ->searchable()
                    ->preload()
                    ->placeholder('Wybierz firmę')
                    ->indicator('Firma'),
            
                // Waluta
                SelectFilter::make('waluta')
                    ->label('Waluta')
                    ->options([
                        'PLN' => 'PLN — Polski Złoty',
                        'EUR' => 'EUR — Euro',
                        'USD' => 'USD — Dolar Amerykański',
                        'GBP' => 'GBP — Funt Brytyjski',
                        'CHF' => 'CHF — Frank Szwajcarski',
                        'CZK' => 'CZK — Korona Czeska',
                    ])
                    ->indicator('Waluta')
                    ->placeholder('Dowolna waluta')
                    ->searchable()
                    ->preload(),
            

                    TernaryFilter::make('converted_order_id')
                    ->label('Przekształcone w zamówienie')
                    ->trueLabel('Tak')
                    ->falseLabel('Nie')
                    ->nullable()
                    ->indicator('Zamówienie'),

                    SelectFilter::make('payment_method_id')
                            ->label('Metoda płatności')
                            ->relationship('paymentMethod', 'nazwa') // pobiera listę metod płatności
                            ->searchable()
                            ->preload()
                            ->placeholder('Dowolna metoda')
                            ->indicator('Metoda płatności'),

                SelectFilter::make('user_id')
                    ->label('Opiekun (użytkownik)')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple() // pozwala zaznaczyć wielu opiekunów
                    ->indicator('Opiekun'),

                // Zakres dat utworzenia
                Filter::make('created_at')
                    ->label('Data utworzenia')
                    ->columns()
                    ->columnSpanFull()
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('od')
                            ->label('Data od:')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
            
                        \Filament\Forms\Components\DatePicker::make('do')
                            ->label('Data do:')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['od'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['do'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['od'] && ! $data['do']) {
                            return null;
                        }
                        return
                            ($data['od']  ? 'Od: ' . \Carbon\Carbon::parse($data['od'])->format('d.m.Y') : '')
                            . ($data['od'] && $data['do'] ? ' – ' : '')
                            . ($data['do'] ? 'Do: ' . \Carbon\Carbon::parse($data['do'])->format('d.m.Y') : '');
                    }),

                    Filter::make('total_net_range')
                    ->columns(2)
                    ->columnSpanFull()

                            ->form([
                                \Filament\Forms\Components\TextInput::make('min')
                        
                                    ->label('Cena od ')
                                    ->numeric()
                                    ->placeholder('np. 1000'),

                                \Filament\Forms\Components\TextInput::make('max')
                        
                                    ->label('Cena do')
                                    ->numeric()
                                    ->placeholder('np. 50000'),
                                    
                            ])
                                ->query(function (Builder $query, array $data): Builder {
                                    return $query
                                        ->when($data['min'], fn ($q, $min) => $q->where('total_net', '>=', $min))
                                        ->when($data['max'], fn ($q, $max) => $q->where('total_net', '<=', $max));
                                })
                                ->indicateUsing(function (array $data): ?string {
                                    if (! $data['min'] && ! $data['max']) {
                                        return null;
                                    }

                                    return 'Kwota: ' .
                                        ($data['min'] ? number_format($data['min'], 0, ',', ' ') . ' zł' : '—') .
                                        ' – ' .
                                        ($data['max'] ? number_format($data['max'], 0, ',', ' ') . ' zł' : '—');
                                }),

                    TernaryFilter::make('is_correction')
                    ->label('Korekty ofert')
                    ->indicator('Korekty')
                    ->trueLabel('Tylko korekty')
                    ->falseLabel('Tylko oferty główne')
                    ->placeholder('Wszystkie')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('parent_oferta_id'),
                        false: fn (Builder $query) => $query->whereNull('parent_oferta_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            
                // Przekształcone w zamówienie
       
            ],
            layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)









            // ⚙️ AKCJE
              ->recordActions([
                EditAction::make()
                    ->label('Edytuj')
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-o-pencil-square'),

                ActionGroup::make([
                    Action::make('view')
                        ->label('Podgląd')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => route('filament.panel.resources.ofertas.view', $record))
                        ->openUrlInNewTab(),

                    Action::make('createCorrection')
                        ->label('Korekta')
                        ->icon('heroicon-o-arrow-path')
                        ->visible(fn (Oferta $record) => ! $record->isCorrection())
                        ->requiresConfirmation()
                        ->action(function (Oferta $record) {
                            $letter = OfferNumberHelper::generateCorrectionLetter($record);
                            $correctionNumber = OfferNumberHelper::buildCorrectionNumber($record->numer, $letter);

                            $correction = $record->replicate();
                            $correction->numer = $correctionNumber;
                            $correction->parent_oferta_id = $record->id;
                            $correction->correction_letter = $letter;
                            $correction->status = 'draft';
                            $correction->converted_order_id = null;
                            $correction->push();

                            foreach ($record->pozycje as $pozycja) {
                                $newPosition = $pozycja->replicate();
                                $newPosition->oferta_id = $correction->id;
                                $newPosition->save();
                            }

                            $correction->recalculateTotals();

                            activity()
                                ->performedOn($correction)
                                ->withProperties([
                                    'type' => 'correction_created',
                                    'parent_oferta_id' => $record->id,
                                    'parent_numer' => $record->numer,
                                    'correction_numer' => $correction->numer,
                                    'correction_letter' => $letter,
                                ])
                                ->log('Utworzono korektę oferty');

                            Notification::make()
                                ->title('Korekta utworzona')
                                ->body("Utworzono korektę oferty {$record->numer} o numerze {$correction->numer}.")
                                ->success()
                                ->send();

                            return redirect()->route('filament.panel.resources.ofertas.edit', $correction);
                        }),
                ])
                    ->label('Więcej')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->button()
                    ->color('gray'),
            ], position: RecordActionsPosition::BeforeColumns)

            // ⚒️ TOOLBAR
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Usuń zaznaczone')
                        ->requiresConfirmation(),
                ]),
            ])

            // 📊 SORTOWANIE
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Brak ofert')
            ->emptyStateDescription('Dodaj pierwszą ofertę, aby rozpocząć pracę.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }
}