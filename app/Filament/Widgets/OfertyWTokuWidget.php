<?php

namespace App\Filament\Widgets;

use App\Models\Oferta;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;

class OfertyWTokuWidget extends TableWidget
{
    protected static ?string $heading = '🧾 Oferty w toku';
   // protected int|string|array $columnSpan = 'full';

   protected static ?int $sort = 4;


    protected function getTableQuery(): ?Builder
    {
        return Oferta::query()
            ->with('firma')
            ->whereIn('status', ['draft', 'sent'])
            ->latest()
            ->limit(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numer')
                    ->label('Numer')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->alignment(Alignment::Start),

                TextColumn::make('firma.nazwa')
                    ->label('Firma')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('total_gross')
                    ->label('Wartość brutto')
                    ->money('PLN', true)
                    ->sortable()
                    ->alignment(Alignment::End),

                // BadgeColumn::make('status')
                //     ->label('Status')
                //     ->colors([
                //         'gray' => 'draft',
                //         'warning' => 'sent',
                //     ])
                //     ->icons([
                //         'heroicon-o-pencil-square' => 'draft',
                //         'heroicon-o-paper-airplane' => 'sent',
                //     ])
                //     ->formatStateUsing(fn (string $state): string => match ($state) {
                //         'draft' => 'Szkic',
                //         'sent' => 'Wysłana',
                //         default => ucfirst($state),
                //     }),
            ])
            ->recordActions([
                 ViewAction::make(),
                 EditAction::make()
                    ->label('Edytuj')
                    ->color('primary')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Oferta $record): string => route('filament.panel.resources.ofertas.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Brak ofert w toku')
            ->emptyStateDescription('Nie masz jeszcze szkiców ani wysłanych ofert.')
            ->paginated(false)
            ->defaultSort('created_at', 'desc');
    }
}