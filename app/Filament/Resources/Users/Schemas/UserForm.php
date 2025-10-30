<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
        ->schema([
            TextInput::make('name')
                ->label('Imię')
                ->required(),

            TextInput::make('lastname')
                ->label('Nazwisko')
                ->nullable(),

        TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->required(),

        TextInput::make('phone')
                ->label('Telefon')
                ->tel()
                ->nullable(),

            Select::make('role')
                ->label('Rola')
                ->options([
                    'admin' => 'Superadministrator',
                    'user' => 'Pracownik',
                    'viewer' => 'Podglądający',
                ])
                ->default('user')
                ->required(),

            TextInput::make('password')
                ->label('Hasło')
                ->password()
                ->revealable()
                ->required(fn (string $context) => $context === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

            Toggle::make('is_active')
                ->label('Aktywny')
                ->default(true),
        ]);
    }
}
