<?php

namespace App\Filament\Resources\Handlowiecs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Models\Firma;

class HandlowiecForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('firma_id')
                ->label('Firma')
                ->relationship('firma', 'nazwa')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('nazwa')->label('Nazwa firmy')->required(),

                            TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->default(null),
                                    TextInput::make('nip')
                                    ->default(null),
                            TextInput::make('telefon')
                                    ->tel()
                                    ->default(null),
                            TextInput::make('adres')
                                     ->default(null),
                            TextInput::make('miasto')
                                   ->default(null),
                            Textarea::make('uwagi')
                                ->default(null)
                                ->columnSpanFull(),
                    
                ])
                ->required(),

            TextInput::make('imie')
                ->label('Imię')
                ->required(),

            TextInput::make('nazwisko')
                ->label('Nazwisko')
                ->required(),

            TextInput::make('email')
                ->label('Adres e-mail')
                ->email()
                ->unique(ignoreRecord: true)
                ->required(),

            TextInput::make('telefon')
                ->label('Telefon')
                ->tel()
                ->maxLength(20),
            ]);
    }
}
