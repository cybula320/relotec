<?php

namespace App\Filament\Resources\Firmas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

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
                Textarea::make('uwagi')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
