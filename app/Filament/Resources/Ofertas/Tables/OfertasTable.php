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
use Filament\Tables\Columns\Summarizers\Sum;
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
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\SelectColumn;

class OfertasTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->defaultPaginationPageOption(50)
        ->paginated([10, 25, 50, 100, 'all'])
        ->paginatedWhileReordering()
        ->description('Lista wszystkich ofert w systemie. Kliknij na ofertę, aby zobaczyć szczegóły.')
        
        // ⭐ REORDERING - Drag & Drop Sortowanie
        ->reorderable('id')
        ->defaultSort('created_at', 'desc')
        
        ->groups([
            // Grupowanie po numerze bazowym oferty (bez litery korekty)
            Group::make('numer', 'base_number')
                ->label('Oferta (z korektami)')
                ->getTitleFromRecordUsing(function (Oferta $record): string {
                    $base = $record->parentOferta?->numer ?? $record->numer;

                    if ($record->isCorrection()) {
                        [$first, $rest] = explode('/', $base, 2);
                        $first = preg_replace('/[A-Z]+$/', '', $first);
                        $base = $first . '/' . $rest;
                    }

                    return $base;
                })
                ->collapsible(),

            Group::make('status')
                ->label('Status oferty')
                ->collapsible(),
                
            Group::make('firma.nazwa')
                ->label('Firma')
                ->collapsible(),
        ])
       
            ->columns([
                // 📄 Numer oferty
                TextColumn::make('numer')
                    ->label('Numer oferty')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-hashtag')
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Numer skopiowany!')
                    ->tooltip('Kliknij aby skopiować'),

                // 🧩 Status - INLINE EDITABLE
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => '📝 Szkic',
                        'sent' => '📤 Wysłana',
                        'accepted' => '✅ Zaakceptowana',
                        'rejected' => '❌ Odrzucona',
                        'converted' => '🔁 Zamówienie',
                    ])
                    ->sortable()
                    ->selectablePlaceholder(false)
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->success()
                            ->title('Status zaktualizowany')
                            ->body("Oferta {$record->numer} ma teraz status: {$state}")
                            ->send();
                    }),

                // 🏢 Firma
                TextColumn::make('firma.nazwa')
                    ->label('Firma')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-building-office')
                    ->tooltip(fn($record) => $record->firma?->email ?? 'Brak e-maila')
                    ->limit(30)
                    ->wrap(),

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
                    ->icon('heroicon-o-user')
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Opiekun')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-user-circle')
                    ->formatStateUsing(fn($state, $record) => $record->user?->name ?? '—')
                    ->toggleable(),

                // 💰 Kwoty z SUMAMI w stopce
                TextColumn::make('total_net')
                    ->label('Netto')
                    ->money(fn($record) => $record->waluta ?? 'PLN')
                    ->sortable()
                    ->alignRight()
                    ->summarize([
                        Sum::make()
                            ->label('Suma netto')
                            ->money('PLN')
                            ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' PLN'),
                    ])
                    ->extraAttributes(['class' => 'text-blue-600 dark:text-blue-400 font-semibold']),

                TextColumn::make('total_gross')
                    ->label('Brutto')
                    ->money(fn($record) => $record->waluta ?? 'PLN')
                    ->sortable()
                    ->alignRight()
                    ->summarize([
                        Sum::make()
                            ->label('Suma brutto')
                            ->money('PLN')
                            ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' PLN'),
                    ])
                    ->extraAttributes(['class' => 'text-green-700 dark:text-green-400 font-semibold']),

                // 💳 Waluta
                TextColumn::make('waluta')
                    ->label('Waluta')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => 'Utworzono: ' . $record->created_at->diffForHumans()),

                TextColumn::make('parentOferta.numer')
                    ->label('Korekta do')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('correction_letter')
                    ->label('Litera korekty')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            
            ->filters([
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
                    ->relationship('paymentMethod', 'nazwa')
                    ->searchable()
                    ->preload()
                    ->placeholder('Dowolna metoda')
                    ->indicator('Metoda płatności'),

                SelectFilter::make('user_id')
                    ->label('Opiekun (użytkownik)')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->indicator('Opiekun'),

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

                    Action::make('downloadPdf')
                        ->label('Pobierz PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->url(fn ($record) => route('oferta.pdf.download', $record))
                        ->openUrlInNewTab(),

                    Action::make('viewPdf')
                        ->label('Podgląd PDF')
                        ->icon('heroicon-o-document')
                        ->color('info')
                        ->url(fn ($record) => route('oferta.pdf.view', $record))
                        ->openUrlInNewTab(),

                    Action::make('sendEmail')
                        ->label('Wyślij email')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->form([
                            \Filament\Forms\Components\Toggle::make('attach_pdf')
                                ->label('Załącz PDF do emaila')
                                ->default(true)
                                ->helperText('PDF zostanie automatycznie załączony do wiadomości'),
                        ])
                        ->action(function (Oferta $record, array $data) {
                            // Przygotuj dane do mailto
                            $to = $record->handlowiec?->email ?? $record->firma?->email ?? '';
                            $subject = "Oferta handlowa nr {$record->numer} - {$record->firma?->nazwa}";
                            
                            // Sformatowana treść emaila
                            $body = "Dzień dobry,\n\n";
                            $body .= "Przesyłam ofertę handlową z następującymi szczegółami:\n\n";
                            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $body .= "OFERTA NR {$record->numer}\n";
                            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                            
                            $body .= "Klient:        {$record->firma?->nazwa}\n";
                            if ($record->handlowiec) {
                                $body .= "Osoba kontaktowa: {$record->handlowiec->imie} {$record->handlowiec->nazwisko}\n";
                            }
                            $body .= "Data wystawienia: " . $record->created_at->format('d.m.Y') . "\n";
                            $body .= "Status:        " . match($record->status) {
                                'draft' => 'Szkic',
                                'sent' => 'Wysłana',
                                'accepted' => 'Zaakceptowana',
                                'rejected' => 'Odrzucona',
                                'converted' => 'Przekształcona w zamówienie',
                                default => $record->status
                            } . "\n\n";
                            
                            $body .= "\nPODSUMOWANIE:\n";
                            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $body .= "Suma netto:  " . number_format($record->total_net, 2, ',', ' ') . " {$record->waluta}\n";
                            $body .= "Suma brutto: " . number_format($record->total_gross, 2, ',', ' ') . " {$record->waluta}\n";
                            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                            
                            if ($record->paymentMethod) {
                                $body .= "Metoda płatności: {$record->paymentMethod->nazwa}\n";
                                if ($record->paymentMethod->termin) {
                                    $body .= "Termin płatności: {$record->paymentMethod->termin} dni\n";
                                }
                                $body .= "\n";
                            }
                            
                            if ($data['attach_pdf']) {
                                $body .= "📎 W załączniku znajdą Państwo szczegółową ofertę w formacie PDF.\n\n";
                                $body .= "Link do pobrania PDF: " . route('oferta.pdf.download', $record) . "\n\n";
                            }
                            
                            $body .= "W przypadku pytań proszę o kontakt.\n\n";
                            $body .= "Pozdrawiam,\n";
                            $body .= auth()->user()->name;
                            
                            if (auth()->user()->email) {
                                $body .= "\nEmail: " . auth()->user()->email;
                            }
                            
                            // Zakoduj parametry dla mailto
                            $mailtoUrl = 'mailto:' . urlencode($to) 
                                . '?subject=' . urlencode($subject)
                                . '&body=' . urlencode($body);
                            
                            // Powiadomienie dla użytkownika
                            Notification::make()
                                ->success()
                                ->title('Email przygotowany')
                                ->body($data['attach_pdf'] ? 
                                    'Email z załącznikiem PDF został przygotowany. Sprawdź link do pobierania w treści.' :
                                    'Email został przygotowany bez załącznika PDF.'
                                )
                                ->send();
                            
                            // Otwórz mailto w nowej karcie
                            return redirect($mailtoUrl);
                        })
                        ->disabled(fn (Oferta $record) => !$record->handlowiec?->email && !$record->firma?->email)
                        ->tooltip(fn (Oferta $record) => 
                            (!$record->handlowiec?->email && !$record->firma?->email) 
                                ? 'Brak adresu email handlowca lub firmy' 
                                : 'Wyślij email do: ' . ($record->handlowiec?->email ?? $record->firma?->email)
                        ),

                    Action::make('duplicate')
                        ->label('Duplikuj')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Duplikuj ofertę')
                        ->modalDescription('Czy na pewno chcesz utworzyć kopię tej oferty?')
                        ->action(function (Oferta $record) {
                            $duplicate = $record->replicate();
                            $duplicate->numer = OfferNumberHelper::generate();
                            $duplicate->status = 'draft';
                            $duplicate->converted_order_id = null;
                            $duplicate->push();

                            foreach ($record->pozycje as $pozycja) {
                                $newPosition = $pozycja->replicate();
                                $newPosition->oferta_id = $duplicate->id;
                                $newPosition->save();
                            }

                            $duplicate->recalculateTotals();

                            Notification::make()
                                ->success()
                                ->title('Oferta zduplikowana')
                                ->body("Utworzono kopię oferty z numerem: {$duplicate->numer}")
                                ->send();

                            return redirect()->route('filament.panel.resources.ofertas.edit', $duplicate);
                        }),

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

            // ⚒️ BULK ACTIONS
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeStatus')
                        ->label('Zmień status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label('Nowy status')
                                ->options([
                                    'draft' => '📝 Szkic',
                                    'sent' => '📤 Wysłana',
                                    'accepted' => '✅ Zaakceptowana',
                                    'rejected' => '❌ Odrzucona',
                                    'converted' => '🔁 Zamówienie',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                                $count++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Status zaktualizowany')
                                ->body("Zmieniono status {$count} ofert.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Usuń zaznaczone')
                        ->requiresConfirmation(),
                ]),
            ])

            ->striped()
            ->emptyStateHeading('Brak ofert')
            ->emptyStateDescription('Dodaj pierwszą ofertę, aby rozpocząć pracę.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }
}