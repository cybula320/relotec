<?php

namespace App\Filament\Resources\Ofertas\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Tabs;
use Filament\Notifications\Notification;
use App\Models\Firma;
use App\Models\Handlowiec;
use App\Models\User;
use Filament\Forms\Components\Toggle;

use App\Models\Oferta;
use App\Helpers\OfferNumberHelper;

class OfertaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Hidden::make('parent_oferta_id')
                ->default(null),

            Hidden::make('handlowiec_not_found_notified')
                ->default(false),

            // 📑 TABS - Organizacja w zakładki
            Tabs::make('Tabs')
                ->tabs([
                    
                    // 🔹 TAB 1: DANE PODSTAWOWE
                    Tabs\Tab::make('Dane podstawowe')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('💼 Informacje o ofercie')
                                ->description('Numer oferty i podstawowe dane kontaktowe')
                                ->schema([
                                    TextInput::make('numer')
                                        ->label('Numer oferty')
                                        ->prefixIcon('heroicon-o-hashtag')
                                        ->readOnly()
                                        ->required()
                                        ->dehydrated(true)
                                        ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                            if ($record instanceof \App\Models\Oferta && $record->numer) {
                                                $component->state($record->numer);
                                                return;
                                            }
                                            if (blank($state)) {
                                                $component->state(\App\Helpers\OfferNumberHelper::generate());
                                            }
                                        })
                                        ->hint('Automatycznie generowany przy tworzeniu')
                                        ->hintIcon('heroicon-o-information-circle')
                                        ->hintColor('primary')
                                        ->extraAttributes([
                                            'class' => 'font-semibold text-primary-600 dark:text-primary-400',
                                        ]),

                                    Textarea::make('tytul')
                                        ->label('Tytuł oferty')
                                        ->placeholder('np. Dostawa sprzętu biurowego dla działu księgowości, Projekt kompleksowej strony internetowej z systemem CMS')
                                        ->columnSpanFull()
                                        ->rows(3)
                                        ->autosize()
                                        ->helperText('Opisz przedmiot oferty - może być dłuższy opis, który ułatwi wyszukiwanie i identyfikację')
                                        ->hint('Opcjonalne, ale zalecane')
                                        ->hintIcon('heroicon-o-light-bulb')
                                        ->hintColor('info')
                                        ->maxLength(500),

                                    TextInput::make('email_handlowca')
                                        ->label('E-mail handlowca')
                                        ->placeholder('np. jan.kowalski@firma.pl')
                                        ->suffixIcon('heroicon-o-magnifying-glass')
                                        ->columnSpanFull()
                                        ->helperText('Podaj e-mail handlowca — system spróbuje przypisać firmę automatycznie.')
                                        ->hintIcon('heroicon-o-light-bulb')
                                        ->hint('Wpisz pełny email, następnie kliknij poza pole')
                                        ->hintColor('warning')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                            if (empty($state)) {
                                                $set('handlowiec_id', null);
                                                $set('handlowiec_not_found_notified', false);
                                                return;
                                            }

                                            $handlowiec = \App\Models\Handlowiec::with('firma')->where('email', $state)->first();

                                            if ($handlowiec) {
                                                $set('handlowiec_id', $handlowiec->id);
                                                $set('firma_id', $handlowiec->firma_id);
                                                $set('payment_method_id', $handlowiec->firma->payment_method_id);
                                                $set('handlowiec_not_found_notified', false);

                                                Notification::make()
                                                    ->title('✅ Handlowiec rozpoznany')
                                                    ->body("Znaleziono: **{$handlowiec->imie} {$handlowiec->nazwisko}** (firma: **{$handlowiec->firma->nazwa}**)")
                                                    ->success()
                                                    ->duration(4000)
                                                    ->send();

                                                Notification::make()
                                                    ->title('Domyślna metoda płatności ustawiona')
                                                    ->body("Ustawiono metodę płatności firmy: **{$handlowiec->firma->paymentMethod->nazwa}**")
                                                    ->success()
                                                    ->send();
                                            } else {
                                                $set('handlowiec_id', null);

                                                if (! $get('handlowiec_not_found_notified')) {
                                                    $set('handlowiec_not_found_notified', true);

                                                    Notification::make()
                                                        ->title('ℹ️ Brak handlowca w bazie')
                                                        ->body('Nie znaleziono handlowca o tym adresie e-mail. Możesz dodać go ręcznie poniżej.')
                                                        ->info()
                                                        ->duration(2000)
                                                        ->send();
                                                }
                                            }
                                        }),

                                    Select::make('firma_id')
                                        ->label('Firma')
                                        ->relationship('firma', 'nazwa')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->hint('Wybór firmy automatycznie ustawi metodę płatności')
                                        ->hintIcon('heroicon-o-building-office')
                                        ->hintColor('info')
                                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                            if (!$state) {
                                                return;
                                            }
                                        
                                            $firma = \App\Models\Firma::with('paymentMethod')->find($state);
                                        
                                            if (!$firma) {
                                                return;
                                            }
                                        
                                            // Ustawiamy metodę płatności z firmy
                                            if ($firma->paymentMethod) {
                                                $set('payment_method_id', $firma->payment_method_id);
                                        
                                                // Jeśli metoda ma termin — nadpisujemy payment_terms_days
                                                if ($firma->paymentMethod->termin) {
                                                    $set('payment_terms_days', $firma->paymentMethod->termin);
                                                }
                                        
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Domyślna metoda płatności ustawiona')
                                                    ->body("Ustawiono: **{$firma->paymentMethod->nazwa}**")
                                                    ->success()
                                                    ->send();
                                        
                                            } else {
                                                // Firma nie ma domyślnej metody płatności — informacja
                                                $set('payment_method_id', null);
                                        
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Brak domyślnej metody płatności')
                                                    ->body('Ta firma nie ma ustawionej metody płatności — ustaw ją ręcznie.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        })
                                        ->createOptionForm([
                                            TextInput::make('nazwa')
                                                ->label('Nazwa firmy')
                                                ->required(),

                                            TextInput::make('email')
                                                ->label('E-mail')
                                                ->email(),

                                            TextInput::make('telefon')
                                                ->label('Telefon'),

                                            TextInput::make('nip')
                                                ->label('NIP'),

                                            TextInput::make('adres')
                                                ->label('Adres'),

                                            TextInput::make('miasto')
                                                ->label('Miasto'),

                                            Textarea::make('uwagi')
                                                ->label('Uwagi'),

                                            // ⭐ NOWE — domyślna metoda płatności firmy
                                            Select::make('payment_method_id')
                                                ->label('Domyślna metoda płatności')
                                                ->relationship('paymentMethod', 'nazwa')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->helperText('Automatycznie przepisywana do nowych ofert i zamówień.')
                                                ->createOptionForm([
                                                    TextInput::make('nazwa')
                                                        ->label('Nazwa metody')
                                                        ->required(),

                                                    TextInput::make('opis')
                                                        ->label('Opis')
                                                        ->nullable(),

                                                    TextInput::make('termin')
                                                        ->label('Termin płatności (dni)')
                                                        ->numeric()
                                                        ->nullable(),

                                                    \Filament\Forms\Components\Toggle::make('aktywny')
                                                        ->label('Aktywna')
                                                        ->default(true),
                                                ]),
                                        ]),

                                    Select::make('handlowiec_id')
                                        ->label('Handlowiec')
                                        ->options(function (callable $get, $record) {
                                            $firmaId = $get('firma_id');
                                            if (!$firmaId) return [];
                                            return \App\Models\Handlowiec::where('firma_id', $firmaId)
                                                ->get()
                                                ->mapWithKeys(fn($h) => [$h->id => "{$h->imie} {$h->nazwisko} ({$h->email})"])
                                                ->toArray();
                                        })
                                        ->disabled(fn (callable $get) => !$get('firma_id'))
                                        ->hint(fn (callable $get) => !$get('firma_id') ? 'Najpierw wybierz firmę' : 'Osoba kontaktowa w firmie klienta')
                                        ->hintIcon('heroicon-o-user')
                                        ->hintColor(fn (callable $get) => !$get('firma_id') ? 'warning' : 'info')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->dehydrated()
                                        ->afterStateHydrated(function (Select $component, $state, $record) {
                                            if ($record instanceof \App\Models\Oferta && $record->handlowiec_id) {
                                                $component->state($record->handlowiec_id);
                                            }
                                        })
                                        ->createOptionForm([
                                            TextInput::make('imie')->label('Imię')->required(),
                                            TextInput::make('nazwisko')->label('Nazwisko')->required(),
                                            TextInput::make('email')->label('E-mail')->email()->required(),
                                            TextInput::make('telefon')->label('Telefon'),
                                        ])
                                        ->createOptionUsing(function (array $data, callable $get) {
                                            $firmaId = $get('firma_id');
                                            if (!$firmaId) {
                                                Notification::make()
                                                    ->title('❌ Najpierw wybierz firmę')
                                                    ->danger()
                                                    ->send();
                                                return null;
                                            }
                                            $data['firma_id'] = $firmaId;
                                            return \App\Models\Handlowiec::create($data)->getKey();
                                        }),

                                    Select::make('user_id')
                                        ->label('Opiekun oferty (użytkownik systemu)')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->helperText('Osoba odpowiedzialna za ofertę po stronie firmy.')
                                        ->hint('Domyślnie zalogowany użytkownik')
                                        ->hintIcon('heroicon-o-user-circle')
                                        ->hintColor('success')
                                        ->default(auth()->id())
                                        ->live()
                                        ->dehydrated(),

                                    TextInput::make('converted_order_id')
                                        ->label('Powiązane zamówienie')
                                        ->placeholder('Jeśli oferta została przekształcona')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->hint('Wypełniane automatycznie po konwersji')
                                        ->hintIcon('heroicon-o-arrow-path')
                                        ->hintColor('gray'),
                                ])
                                ->columns(1)
                                ->collapsible(),
                        ]),

                    // 🔹 TAB 2: WARTOŚCI I PŁATNOŚCI
                    Tabs\Tab::make('Wartości i płatności')
                        ->icon('heroicon-o-banknotes')
                        ->badge(fn ($record) => $record ? number_format($record->total_gross, 2, ',', ' ') . ' PLN' : null)
                        ->schema([
                            Section::make('💰 Podsumowanie wartości')
                                ->description('Suma wartości z pozycji oferty (aktualizowana automatycznie)')
                                ->schema([
                                    // Ukryte pola do zapisu w bazie
                                    Hidden::make('total_net')
                                        ->default(0.00)
                                        ->dehydrated(true),

                                    Hidden::make('total_gross')
                                        ->default(0.00)
                                        ->dehydrated(true),

                                    // Live wyświetlanie sum
                                    Placeholder::make('display_total_net')
                                        ->label('Suma netto')
                                        ->content(function ($record, callable $get) {
                                            if (!$record) {
                                                return '0,00 PLN';
                                            }
                                            $value = $get('total_net') ?? $record->total_net ?? 0;
                                            return number_format((float) $value, 2, ',', ' ') . ' PLN';
                                        })
                                        ->hint('Suma wartości netto wszystkich pozycji')
                                        ->hintIcon('heroicon-o-calculator')
                                        ->hintColor('info')
                                        ->extraAttributes([
                                            'class' => 'text-2xl font-bold text-green-600 dark:text-green-400',
                                        ]),

                                    Placeholder::make('display_total_gross')
                                        ->label('Suma brutto')
                                        ->content(function ($record, callable $get) {
                                            if (!$record) {
                                                return '0,00 PLN';
                                            }
                                            $value = $get('total_gross') ?? $record->total_gross ?? 0;
                                            return number_format((float) $value, 2, ',', ' ') . ' PLN';
                                        })
                                        ->hint('Suma wartości brutto (z VAT)')
                                        ->hintIcon('heroicon-o-receipt-percent')
                                        ->hintColor('success')
                                        ->extraAttributes([
                                            'class' => 'text-2xl font-bold text-blue-600 dark:text-blue-400',
                                        ]),

                                    Select::make('payment_method_id')
                                        ->label('Metoda płatności')
                                        ->relationship('paymentMethod', 'nazwa')
                                        ->preload()
                                        ->searchable()
                                        ->placeholder('Wybierz metodę płatności')
                                        ->hint('Pobierana automatycznie z ustawień firmy')
                                        ->hintIcon('heroicon-o-credit-card')
                                        ->hintColor('primary')
                                        ->default(fn (callable $get) =>
                                            \App\Models\Firma::find($get('firma_id'))?->payment_method_id
                                        )
                                        ->createOptionForm([
                                            TextInput::make('nazwa')
                                                ->label('Nazwa metody płatności')
                                                ->required()
                                                ->placeholder('np. Przelew 14 dni'),

                                            TextInput::make('opis')
                                                ->label('Opis (opcjonalnie)')
                                                ->placeholder('np. standardowy termin płatności'),

                                            TextInput::make('termin')
                                                ->label('Termin płatności (dni)')
                                                ->numeric()
                                                ->placeholder('np. 14'),

                                            Toggle::make('aktywny')
                                                ->label('Aktywna metoda')
                                                ->default(true)
                                                ->required(),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            return \App\Models\PaymentMethod::create($data)->id;
                                        })
                                        ->helperText('Domyślna metoda płatności pobierana z kontrahenta.'),

                                    Select::make('waluta')
                                        ->label('Waluta')
                                        ->options([
                                            'PLN' => 'PLN — Polski Złoty',
                                            'EUR' => 'EUR — Euro',
                                            'USD' => 'USD — Dolar Amerykański',
                                            'GBP' => 'GBP — Funt Brytyjski',
                                            'CHF' => 'CHF — Frank Szwajcarski',
                                            'CZK' => 'CZK — Korona Czeska',
                                        ])
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->selectablePlaceholder(false)
                                        ->prefixIcon('heroicon-o-currency-dollar')
                                        ->hint('Waluta oferty - użyta w sumach i pozycjach')
                                        ->hintIcon('heroicon-o-banknotes')
                                        ->hintColor('warning')
                                        ->afterStateHydrated(function (\Filament\Forms\Components\Select $component, $state, $record) {
                                            if ($record instanceof \App\Models\Oferta && $record->waluta) {
                                                $component->state($record->waluta);
                                                return;
                                            }
                                            if (blank($state)) {
                                                $component->state('PLN');
                                            }
                                        })
                                        ->helperText('Wybierz walutę, w której wystawiona jest oferta.'),
                                ])
                                ->columns(1)
                                ->collapsible(),
                        ]),

                    // 🔹 TAB 3: STATUS I UWAGI
                    Tabs\Tab::make('Status i uwagi')
                        ->icon('heroicon-o-flag')
                        ->badge(fn ($record) => $record?->status ? match($record->status) {
                            'draft' => 'Szkic',
                            'sent' => 'Wysłana',
                            'accepted' => 'Zaakceptowana',
                            'rejected' => 'Odrzucona',
                            'converted' => 'Zamówienie',
                            default => $record->status
                        } : null)
                        ->schema([
                            Section::make('📊 Status oferty')
                                ->description('Aktualny stan oferty w procesie sprzedaży')
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
                                        ->required()
                                        ->live()
                                        ->dehydrated()
                                        ->hint('Zmiana statusu śledzona w historii')
                                        ->hintIcon('heroicon-o-clock')
                                        ->hintColor('info')
                                        ->afterStateHydrated(function (Select $component, $state, $record) {
                                            if ($record instanceof \App\Models\Oferta && $record->status) {
                                                $component->state($record->status);
                                            }
                                        }),

                                    Textarea::make('uwagi')
                                        ->label('Uwagi wewnętrzne / komentarze')
                                        ->rows(4)
                                        ->placeholder('Notatki dotyczące tej oferty...')
                                        ->hint('Widoczne tylko w systemie, nie w wydruku')
                                        ->hintIcon('heroicon-o-eye-slash')
                                        ->hintColor('warning')
                                        ->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->collapsible(),
                        ]),

                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ]);
    }
}