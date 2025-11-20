<?php

namespace App\Filament\Resources\Ofertas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

// Akcje
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

// Komponenty formularza
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Summarizers\Sum;

// Kolumny tabeli
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use App\Models\Oferta;

class OfertaPozycjeRelationManager extends RelationManager
{
    protected static string $relationship = 'pozycje';
    protected static ?string $title = 'Pozycje oferty';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // 🧾 Dane pozycji
            Section::make('🧾 Dane produktu')
                ->description('Podstawowe informacje o pozycji oferty')
                ->schema([
                    TextInput::make('nazwa')
                        ->label('Nazwa produktu')
                        ->placeholder('np. Usługa CNC, zestaw śrub, produkt A')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('opis')
                        ->label('Opis / szczegóły')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Krótki opis pozycji, parametry, specyfikacja...'),
                ])
                ->columns(1)
                ->columnSpanFull()
                ->collapsible(),

            // 💰 Ceny z automatycznym przeliczeniem
            Section::make('💰 Ceny i wartości')
                ->description('Wprowadź dane do kalkulacji — system automatycznie przeliczy wartości netto i brutto.')
                ->schema([
                    // 🧮 Dane wejściowe
                    Section::make('🧮 Dane wejściowe')
                        ->schema([
                            TextInput::make('ilosc')
                                ->label('Ilość')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->live(onBlur: true)
                                ->helperText('Podaj ilość sztuk / jednostek')
                                ->suffix('szt.')
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    static::przelicz($get, $set);
                                }),

                            TextInput::make('unit_price_net')
                                ->label('Cena jednostkowa netto')
                                ->numeric()
                                ->step(0.01)
                                ->placeholder('np. 125.50')
                                ->required()
                                ->live(onBlur: true)
                                ->helperText('Cena netto za jedną sztukę')
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    static::przelicz($get, $set);
                                }),

                            TextInput::make('vat_rate')
                                ->label('Stawka VAT')
                                ->numeric()
                                ->default(23)
                                ->step(1)
                                ->suffix('%')
                                ->helperText('Standardowa stawka VAT to 23%')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    static::przelicz($get, $set);
                                }),
                        ])
                        ->columns(3)
                        ->icon('heroicon-o-calculator')
                        ->columnSpanFull(),

                    // 💰 Automatyczne wyniki kalkulacji
                    Section::make('📊 Wyniki kalkulacji')
                        ->schema([
                            TextInput::make('total_net')
                                ->label('Wartość netto')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated(true)
                                ->extraAttributes(['class' => 'font-semibold text-blue-700 dark:text-blue-400'])
                                ->helperText('Suma netto (ilość × cena jednostkowa)'),

                            TextInput::make('total_gross')
                                ->label('Wartość brutto')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated(true)
                                ->extraAttributes(['class' => 'font-semibold text-green-700 dark:text-green-400'])
                                ->helperText('Suma brutto (netto + VAT)'),
                        ])
                        ->columns(2)
                        ->icon('heroicon-o-banknotes')
                        ->columnSpanFull()
                        ->collapsible(),
                ])
                ->columnSpanFull()
                ->collapsible(),

            // 🖼️ Zdjęcie i uwagi
            Section::make('🖼️ Zdjęcie i uwagi')
                ->description('Dodatkowe informacje do pozycji')
                ->schema([
                    FileUpload::make('zdjecie')
                        ->label('Zdjęcie (opcjonalne)')
                        ->image()
                        ->directory('oferty/pozycje')
                        ->columnSpanFull(),

                    Textarea::make('uwagi')
                        ->label('Uwagi / komentarz')
                        ->rows(3)
                        ->placeholder('Dodatkowe informacje, uwagi do realizacji...')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->columnSpanFull()
                ->collapsible(),
        ]);
    }

    /**
     * 💡 Przelicz wartości netto i brutto w locie
     */
    private static function przelicz(callable $get, callable $set): void
    {
        $ilosc = (float) $get('ilosc') ?: 0;
        $cena = (float) $get('unit_price_net') ?: 0;
        $vat = (float) $get('vat_rate') ?: 0;

        $netto = $ilosc * $cena;
        $brutto = $netto * (1 + ($vat / 100));

        $set('total_net', round($netto, 2));
        $set('total_gross', round($brutto, 2));

        // Usuwamy notyfikacje przy każdej zmianie, żeby nie spamować użytkownika
        // Notification::make()
        //     ->title('💰 Zaktualizowano wartości pozycji')
        //     ->success()
        //     ->duration(1000)
        //     ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nazwa')
            ->columns([
                TextColumn::make('nazwa')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ilosc')
                    ->label('Ilość')
                    ->sortable(),

                TextColumn::make('unit_price_net')
                    ->label('Cena netto')
                    ->money('PLN', true)
                    ->summarize(Sum::make())// <— tutaj suma netto wszystkich wiersz
                    ->sortable(),

                TextColumn::make('vat_rate')
                ->summarize(Sum::make())
                    ->label('VAT (%)'),

                TextColumn::make('total_net')
                ->summarize(Sum::make())
                    ->label('Wartość netto')
                    ->money('PLN', true)
                    ->sortable(),

                TextColumn::make('total_gross')
                ->summarize(Sum::make())
                    ->label('Wartość brutto')
                    ->money('PLN', true)
                    ->sortable(),

                TextColumn::make('uwagi')
                    ->label('Uwagi')
                    ->limit(40)
                    ->wrap(),
            ])
            
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj pozycję')
                    ->after(function ($record, $livewire) {
                        // $record to nowa OfertaPozycja, $livewire to ten RelationManager
                        static::updateOfertaTotalsOnParent($record->oferta_id, $livewire);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edytuj')
                    ->after(function ($record, $livewire) {
                        static::updateOfertaTotalsOnParent($record->oferta_id, $livewire);
                    }),

                DeleteAction::make()
                    ->label('Usuń')
                    ->after(function ($record, $livewire) {
                        static::updateOfertaTotalsOnParent($record->oferta_id, $livewire);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Usuń zaznaczone'),
                ]),
            ])
            ->defaultSort('id', 'asc');
    }

    private static function updateOfertaTotalsOnParent(int $ofertaId, $livewire): void
    {
        $oferta = Oferta::with('pozycje')->find($ofertaId);
        if (! $oferta) {
            return;
        }

        // użyj istniejącej logiki przeliczania, jeśli jest
        if (method_exists($oferta, 'recalculateTotals')) {
            $oferta->recalculateTotals();
            $oferta->refresh();
        } else {
            $oferta->total_net = $oferta->pozycje->sum('total_net');
            $oferta->total_gross = $oferta->pozycje->sum('total_gross');
            $oferta->save();
        }

        // Wyślij event do strony EditOferta, żeby odświeżyła formularz
        $livewire->dispatch('totals-updated');

        // ustaw wartości bezpośrednio w formularzu nadrzędnym (EditOferta)
        if (method_exists($livewire, 'getOwnerRecord')) {
            // Filament 4: RelationManager ma ownerRecord i ownerForm
            $owner = $livewire->getOwnerRecord();
            if (method_exists($livewire, 'getOwnerForm')) {
                $form = $livewire->getOwnerForm();
                $form->fill([
                    'total_net' => round((float) $oferta->total_net, 2),
                    'total_gross' => round((float) $oferta->total_gross, 2),
                ]);
            } else {
                // awaryjnie spróbuj ustawić na samym ownerze
                $owner->total_net = $oferta->total_net;
                $owner->total_gross = $oferta->total_gross;
            }
        }
    }
}