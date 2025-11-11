<?php

namespace App\Filament\Resources\Zamowienies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ZamowienieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numer'),
                TextInput::make('firma_id')
                    ->numeric(),
                TextInput::make('handlowiec_id')
                    ->numeric(),
                TextInput::make('waluta')
                    ->required()
                    ->default('PLN'),
                TextInput::make('payment_terms_days')
                    ->numeric(),
                DatePicker::make('due_date'),
                Textarea::make('uwagi')
                    ->columnSpanFull(),
                TextInput::make('total_net')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_gross')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options([
            'new' => 'New',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('new')
                    ->required(),
            ]);
    }
}
