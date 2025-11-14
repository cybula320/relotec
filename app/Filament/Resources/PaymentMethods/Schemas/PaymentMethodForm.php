<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nazwa')
                    ->required(),
                TextInput::make('opis'),
                TextInput::make('termin')
                    ->numeric(),
                Toggle::make('aktywny')
                    ->required(),
            ]);
    }
}
