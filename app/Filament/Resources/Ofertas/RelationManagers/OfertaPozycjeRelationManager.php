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

// Kolumny tabeli
use Filament\Tables\Columns\TextColumn;

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
                        ->maxLength(255),

                    TextInput::make('ilosc')
                        ->numeric()
                        ->label('Ilość')
                        ->default(1)
                        ->minValue(1),

                    Textarea::make('opis')
                        ->label('Opis / szczegóły')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Krótki opis pozycji, parametry, specyfikacja...'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->collapsible(),

            // 💰 Ceny
            Section::make('💰 Ceny i wartości')
                ->description('Wprowadź ceny netto, stawkę VAT oraz kwoty brutto')
                ->schema([
                    TextInput::make('unit_price_net')
                        ->label('Cena netto')
                        ->prefix('PLN')
                        ->numeric()
                        ->step(0.01)
                        ->placeholder('np. 100.00'),

                    TextInput::make('vat_rate')
                        ->label('VAT (%)')
                        ->numeric()
                        ->default(23)
                        ->step(1),

                    TextInput::make('unit_price_gross')
                        ->label('Cena brutto')
                        ->prefix('PLN')
                        ->numeric()
                        ->step(0.01)
                        ->placeholder('np. 123.00'),
                ])
                ->columns(3)
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
                    ->sortable(),

                TextColumn::make('vat_rate')
                    ->label('VAT (%)'),

                TextColumn::make('total_net')
                    ->label('Wartość netto')
                    ->money('PLN', true)
                    ->sortable(),

                TextColumn::make('total_gross')
                    ->label('Wartość brutto')
                    ->money('PLN', true)
                    ->sortable(),

                TextColumn::make('uwagi')
                    ->label('Uwagi')
                    ->limit(40)
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()->label('Dodaj pozycję'),
            ])
            ->recordActions([
                EditAction::make()->label('Edytuj'),
                DeleteAction::make()->label('Usuń'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Usuń zaznaczone'),
                ]),
            ])
            ->defaultSort('id', 'asc');
    }
}