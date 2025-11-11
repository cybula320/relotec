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

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Filament\Actions\Action;

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

                // 📅 Daty
                TextColumn::make('due_date')
                    ->label('Termin płatności')
                    ->date()
                    ->sortable()
                    ->color(fn($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Data utworzenia')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'draft' => '📝 Szkic',
                        'sent' => '📤 Wysłana',
                        'accepted' => '✅ Zaakceptowana',
                        'rejected' => '❌ Odrzucona',
                        'converted' => '🔁 Zamówienie',
                        default => ucfirst($state),
                    }),
            ])
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

                    
                // Zakres dat utworzenia
                Filter::make('created_at')
                    ->label('Data utworzenia')
                    ->columns(2)
                    ->columnSpanFull()
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('od')
                            ->label('Od')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
            
                        \Filament\Forms\Components\DatePicker::make('do')
                            ->label('Do')
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
            
                // Przekształcone w zamówienie
       
            ],
            layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)









            // ⚙️ AKCJE
            ->recordActions([
                EditAction::make()
                    ->label('Edytuj')
                    ->icon('heroicon-o-pencil-square'),

                Action::make('view')
                    ->label('Podgląd')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.panel.resources.ofertas.view', $record))
                    ->openUrlInNewTab(),

           


            ])

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