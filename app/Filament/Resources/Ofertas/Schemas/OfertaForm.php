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
use Livewire\Attributes\On;
use Filament\Forms\Components\Actions\Action;


class OfertaForm
{

    #[\Livewire\Attributes\On('refreshSummary')]
    public function refreshSummary(): void
    {
        if (! isset($this->record)) {
            return;
        }
    
        $oferta = \App\Models\Oferta::with('pozycje')->find($this->record->id);
        if (! $oferta) {
            return;
        }
    
        $oferta->recalculateTotals();
    
        $this->fill([
            'total_net' => round($oferta->total_net, 2),
            'total_gross' => round($oferta->total_gross, 2),
        ]);
    
        \Filament\Notifications\Notification::make()
            ->title('🔄 Podsumowanie zaktualizowane')
            ->body("{$oferta->total_net} PLN netto / {$oferta->total_gross} PLN brutto")
            ->success()
            ->duration(1500)
            ->send();
    }
    

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Hidden::make('recalculate_trigger')
    ->default(now()->timestamp)
    ->reactive()
    ->afterStateUpdated(function (callable $get, callable $set) {
        $ofertaId = $get('id');

        if (! $ofertaId) {
            return;
        }

        $oferta = \App\Models\Oferta::with('pozycje')->find($ofertaId);
        if ($oferta) {
            $set('total_net', round($oferta->pozycje->sum('total_net'), 2));
            $set('total_gross', round($oferta->pozycje->sum('total_gross'), 2));
        }
    }),


    
            // 💼 DANE OFERTY
            Section::make('💼 Dane oferty')
                ->description('Podstawowe informacje o ofercie handlowej')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('numer')
                            ->label('Numer oferty')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->default(fn() => \App\Helpers\OfferNumberHelper::generate())
                            ->readOnly()
                            ->dehydrated(true) // ważne: żeby został zapisany do bazy
                            ->required()
                            ->hint('Automatycznie generowany przy tworzeniu nowej oferty')
                            ->extraAttributes([
                                'class' => 'font-semibold text-primary-600 dark:text-primary-400',
                            ]),



                            TextInput::make('payment_terms_days')
                                ->label('Termin ważności oferty (dni)')
                                ->numeric()
                                ->default(14)
                                ->minValue(0)
                                ->maxValue(120)
                                ->suffix('dni'),



        TextInput::make('email_handlowca')
        ->label('E-mail handlowca')
        ->placeholder('np. jan.kowalski@firma.pl')
        ->suffixIcon('heroicon-o-magnifying-glass')
        ->columnSpanFull()
        ->helperText('Podaj e-mail handlowca — system spróbuje przypisać firmę automatycznie.')
        ->reactive()
        ->afterStateUpdated(function (callable $set, $state) {
            if (empty($state)) return;

            $handlowiec = \App\Models\Handlowiec::with('firma')->where('email', $state)->first();

            if ($handlowiec) {
                $set('handlowiec_id', $handlowiec->id);
                $set('firma_id', $handlowiec->firma_id);

                Notification::make()
                    ->title('✅ Handlowiec rozpoznany')
                    ->body("Znaleziono: **{$handlowiec->imie} {$handlowiec->nazwisko}** (firma: **{$handlowiec->firma->nazwa}**)")
                    ->success()
                    ->duration(4000)
                    ->send();
            } else {
                $set('handlowiec_id', null);
                Notification::make()
                    ->title('ℹ️ Brak handlowca w bazie')
                    ->body('Nie znaleziono handlowca o tym adresie e-mail. Możesz dodać go ręcznie poniżej.')
                    ->info()
                    ->send();
            }
        }),

    Select::make('firma_id')
        ->label('Firma')
        ->relationship('firma', 'nazwa')
        ->searchable()
        ->preload()
        ->required()
        ->createOptionForm([
            TextInput::make('nazwa')->label('Nazwa firmy')->required(),
            TextInput::make('email')->label('E-mail')->email(),
            TextInput::make('telefon')->label('Telefon'),
            TextInput::make('nip')->label('NIP'),
            TextInput::make('adres')->label('Adres'),
            TextInput::make('miasto')->label('Miasto'),
            Textarea::make('uwagi')->label('Uwagi'),
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


        TextInput::make('converted_order_id')
        ->label('Powiązane zamówienie')
        ->placeholder('Jeśli oferta została przekształcona')
        ->disabled()
        ->dehydrated(false),
                 
                        ])
                        ->columns(1),
                ])
                 ->collapsible(),



                 





                            // 💰 PODSUMOWANIE
            Section::make('💰 Podsumowanie wartości')
            ->description('Suma wartości z pozycji oferty (zmiana nie jest możliwa ręcznie)')
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


                            DatePicker::make('due_date')
                                ->label('Data płatności')
                                ->hint('Jeśli pusta – zostanie obliczona automatycznie'),


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