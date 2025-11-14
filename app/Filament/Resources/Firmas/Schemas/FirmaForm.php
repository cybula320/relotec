<?php

namespace App\Filament\Resources\Firmas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;


class FirmaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nazwa')
                    ->required(),
                TextInput::make('nip')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('telefon')
                    ->tel()
                    ->default(null),
                TextInput::make('adres')
                    ->default(null),
                TextInput::make('miasto')
                    ->default(null),


  Select::make('payment_method_id')
    ->label('Domyślna metoda płatności')
    ->relationship('paymentMethod', 'nazwa')
    ->searchable()
    ->preload()
    ->placeholder('Wybierz metodę płatności')
    ->helperText('Metoda płatności będzie automatycznie przypisywana do nowej oferty lub zamówienia.')
    ->columnSpan(1)

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
    }),


                Textarea::make('uwagi')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
