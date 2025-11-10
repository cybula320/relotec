<?php

namespace App\Filament\Resources\Handlowiecs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Handlowiec;
use App\Models\Firma;
use Filament\Tables\Filters\Filter;
use Filament\Actions\RestoreAction;

class HandlowiecsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('imie')
                    ->label('Imię')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nazwisko')
                    ->label('Nazwisko')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('firma.nazwa')
                    ->label('Firma')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->copyable()
                    ->copyMessage('Skopiowano adres e-mail!')
                    ->copyMessageDuration(1500)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('telefon')
                    ->label('Telefon')
                    ->searchable(),
            ])
            ->defaultSort('nazwisko')
            ->filters([
                 // 🔹 Filtrowanie po firmie
                 SelectFilter::make('firma_id')
                 ->label('Firma')
                 ->options(Firma::query()->pluck('nazwa', 'id')->toArray())
                 ->searchable()
                 ->preload(),

             // 🔹 Filtrowanie po fragmencie e-maila
             Filter::make('email')
                 ->form([
                     \Filament\Forms\Components\TextInput::make('value')
                         ->label('Adres e-mail zawiera')
                         ->placeholder('np. @gmail.com'),
                 ])
                 ->query(function ($query, array $data) {
                     return $query
                         ->when($data['value'], fn ($q, $value) => $q->where('email', 'like', "%{$value}%"));
                 }),

                TrashedFilter::make(),
            ], layout: FiltersLayout::AboveContent)
   
            ->recordActions([
                EditAction::make()
                ->label('Edytuj')
                ->icon('heroicon-o-pencil')
                ->color('primary'),

            DeleteAction::make()
                ->label('Usuń')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation(),

                RestoreAction::make()
                ->label('Przywróć')
                 ->color('success'),
 

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
