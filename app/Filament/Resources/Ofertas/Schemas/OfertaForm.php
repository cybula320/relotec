<?php

namespace App\Filament\Resources\Ofertas\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class OfertaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // 💼 DANE OFERTY
            Section::make('💼 Dane oferty')
                ->description('Podstawowe informacje o ofercie handlowej')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('numer')
                                ->label('Numer oferty')
                                ->placeholder('np. OF/2025/011')
                                ->prefixIcon('heroicon-o-hashtag')
                                ->required(),

                            Select::make('firma_id')
                                ->label('Firma')
                                ->relationship('firma', 'nazwa')
                                ->searchable()
                                ->preload()
                                ->placeholder('Wybierz firmę')
                                ->required(),

                            Select::make('handlowiec_id')
                                ->label('Handlowiec')
                                ->relationship('handlowiec', 'nazwisko')
                                ->searchable()
                                ->preload()
                                ->placeholder('Wybierz osobę odpowiedzialną')
                                ->required(),
                        ])
                        ->columns(1),
                ])
                ->columnSpanFull()
                ->collapsible(),

            // 🧾 WARUNKI PŁATNOŚCI
            Section::make('🧾 Warunki płatności')
                ->description('Określ warunki finansowe dla tej oferty')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('waluta')
                                ->label('Waluta')
                                ->default('PLN')
                                ->maxLength(3)
                                ->required()
                                ->prefixIcon('heroicon-o-currency-dollar'),

                            TextInput::make('payment_terms_days')
                                ->label('Termin płatności (dni)')
                                ->numeric()
                                ->default(14)
                                ->minValue(0)
                                ->maxValue(120)
                                ->suffix('dni'),

                            DatePicker::make('due_date')
                                ->label('Data płatności')
                                ->hint('Jeśli pusta – zostanie obliczona automatycznie'),

                            TextInput::make('converted_order_id')
                                ->label('Powiązane zamówienie')
                                ->placeholder('Jeśli oferta została przekształcona')
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(1),
                ])
                ->columnSpanFull()
                ->collapsible(),

            // 💰 PODSUMOWANIE
            Section::make('💰 Podsumowanie wartości')
                ->description('Suma wartości z pozycji oferty')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('total_net')
                                ->label('Suma netto')
                                ->prefix('PLN')
                                ->numeric()
                                ->default(0.00)
                                ->required()
                                ->readOnly()
                                ->extraAttributes([
                                    'class' => 'font-semibold text-green-700 dark:text-green-400',
                                ]),

                            TextInput::make('total_gross')
                                ->label('Suma brutto')
                                ->prefix('PLN')
                                ->numeric()
                                ->default(0.00)
                                ->required()
                                ->readOnly()
                                ->extraAttributes([
                                    'class' => 'font-semibold text-green-700 dark:text-green-400',
                                ]),
                        ])
                        ->columns(1),
                ])
                ->columnSpanFull()
                ->collapsible(),

            // 🧠 STATUS I UWAGI
            Section::make('🧠 Status i notatki')
                ->description('Zarządzaj statusem i uwagami dla tej oferty')
                ->schema([
                    Section::make()
                        ->schema([
                            Select::make('status')
                                ->label('Status oferty')
                                ->options([
                                    'draft' => '📝 Szkic',
                                    'sent' => '📤 Wysłana',
                                    'accepted' => '✅ Zaakceptowana',
                                    'rejected' => '❌ Odrzucona',
                                    'converted' => '🔁 Przekształcona w zamówienie',
                                ])
                                ->default('draft')
                                ->required(),

                            Textarea::make('uwagi')
                                ->label('Uwagi wewnętrzne / komentarze')
                                ->rows(3)
                                ->placeholder('Notatki dotyczące tej oferty...')
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->collapsible(),
        ]);
    }
}