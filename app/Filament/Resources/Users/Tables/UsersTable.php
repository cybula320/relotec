<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
 
 class UsersTable
{
 

    public static function configure(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('name')->label('Imię')->searchable(),
            TextColumn::make('lastname')->label('Nazwisko')->searchable(),
            TextColumn::make('email')->label('E-mail')->searchable(),
            TextColumn::make('role')->label('Rola')->badge(),
            IconColumn::make('is_active')
                ->label('Aktywny')
                ->boolean(),
            // TextColumn::make('last_login_at')
            //     ->label('Ostatnie logowanie')
            //     ->dateTime()
            //     ->sortable()
            //     ->toggleable(),
            TextColumn::make('created_at')->label('Utworzono')->dateTime()->sortable(),
        ])
        ->filters([
           SelectFilter::make('role')
                ->label('Rola')
                ->options([
                    'admin' => 'Superadministrator',
                    'user' => 'Pracownik',
                    'viewer' => 'Podglądający',
                ]),
            TernaryFilter::make('is_active')->label('Status'),
        ])
        ->actions([
            DeleteAction::make(),
            //DeleteAction::make(),
            EditAction::make(),
        ])
        ->bulkActions([
 DeleteBulkAction::make(),
        ]);
    }
}
