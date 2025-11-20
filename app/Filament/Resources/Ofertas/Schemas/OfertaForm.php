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

    
            // 💼 DANE OFERTY
            Section::make('💼 Dane oferty')
                ->description('Podstawowe informacje o ofercie handlowej')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('numer')
                                ->label('Numer oferty')
                                ->prefixIcon('heroicon-o-hashtag')
                                ->readOnly()
                                ->required()
                                ->dehydrated(true)
                                ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                    // Jeśli edycja/podgląd i w modelu jest numer, ustawiamy go.
                                    if ($record instanceof \App\Models\Oferta && $record->numer) {
                                        $component->state($record->numer);
                                        return;
                                    }

                                    // Jeśli tworzenie i brak numeru w stanie, generujemy nowy.
                                    if (blank($state)) {
                                        $component->state(\App\Helpers\OfferNumberHelper::generate());
                                    }
                                })
                                ->hint('Automatycznie generowany przy tworzeniu nowej oferty')
                                ->extraAttributes([
                                    'class' => 'font-semibold text-primary-600 dark:text-primary-400',
                                ]),

                            // TextInput::make('payment_terms_days')
                            //     ->label('Termin ważności oferty (dni)')
                            //     ->numeric()
                            //     ->default(14)
                            //     ->minValue(0)
                            //     ->maxValue(120)
                            //     ->suffix('dni'),



        TextInput::make('email_handlowca')
        ->label('E-mail handlowca')
        ->placeholder('np. jan.kowalski@firma.pl')
        ->suffixIcon('heroicon-o-magnifying-glass')
        ->columnSpanFull()
        ->helperText('Podaj e-mail handlowca — system spróbuje przypisać firmę automatycznie.')
        ->reactive()
        ->afterStateUpdated(function (callable $get, callable $set, $state) {
            if (empty($state)) {
                // przy czyszczeniu pola resetujemy stan i nie pokazujemy powiadomień
                $set('handlowiec_id', null);
                $set('handlowiec_not_found_notified', false);
                return;
            }

            $handlowiec = \App\Models\Handlowiec::with('firma')->where('email', $state)->first();

            if ($handlowiec) {
                $set('handlowiec_id', $handlowiec->id);
                $set('firma_id', $handlowiec->firma_id);
                $set('payment_method_id', $handlowiec->firma->payment_method_id);

                // resetujemy flagę, bo handlowiec został znaleziony
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

                // pokazujemy powiadomienie tylko raz, dopóki użytkownik nie zmieni e-maila
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
        ->options(function (callable $get) {
            $firmaId = $get('firma_id');
            if (!$firmaId) return [];
            return \App\Models\Handlowiec::where('firma_id', $firmaId)
                ->get()
                ->mapWithKeys(fn($h) => [$h->id => "{$h->imie} {$h->nazwisko} ({$h->email})"])
                ->toArray();
        })
        ->disabled(fn (callable $get) => !$get('firma_id'))
        ->hint(fn (callable $get) => !$get('firma_id') ? 'Najpierw wybierz firmę.' : null)
        ->searchable()
        ->preload()
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
            ->helperText('Osoba odpowiedzialna za ofertę po stronie firmy.')
            ->default(auth()->id()),

        TextInput::make('converted_order_id')
        ->label('Powiązane zamówienie')
        ->placeholder('Jeśli oferta została przekształcona')
        ->disabled()
        ->dehydrated(false),
                 
                        ])
                        ->columns(1),
                ])
                 ->collapsible(),



                 





                            // 💰 Podsumowanie wartości
            Section::make('💰 Podsumowanie wartości')
            ->description('Suma wartości z pozycji oferty (zmiana nie jest możliwa ręcznie)')
            ->extraAttributes([
                'x-data' => '{}',
                'x-init' => 'setInterval(() => { $wire.pollTotals() }, 5000)',
            ])
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


                            // DatePicker::make('due_date')
                            //     ->label('Data płatności')
                            //     ->hint('Jeśli pusta – zostanie obliczona automatycznie'),

                            Select::make('payment_method_id')
                            ->label('Metoda płatności')
                            ->relationship('paymentMethod', 'nazwa')
                            ->preload()
                            ->searchable()
                            ->placeholder('Wybierz metodę płatności')
                            ->default(fn (callable $get) =>
                                \App\Models\Firma::find($get('firma_id'))?->payment_method_id
                            )

                            // 🔥 POZWÓL UTWORZYĆ NOWĄ METODĘ PŁATNOŚCI
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

                            // 🔥 CO ZROBIĆ PRZY ZAPISIE NOWEJ OPCJI
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
                            ->default('PLN')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->selectablePlaceholder(false)
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->helperText('Wybierz walutę, w której wystawiona jest oferta.')
                            ->hint('💡 Domyślnie używana waluta: PLN')
                            ->hintColor('primary'),




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
                            ->rows(2)
                            ->placeholder('Notatki dotyczące tej oferty...')
                            ->columnSpanFull(),


                    ])
                    ->columns(1),
            ])
             ->collapsible(),
 


 
 
        ]);
    }




}