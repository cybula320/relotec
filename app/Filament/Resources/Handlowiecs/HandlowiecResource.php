<?php

namespace App\Filament\Resources\Handlowiecs;

use App\Filament\Resources\Handlowiecs\Pages\CreateHandlowiec;
use App\Filament\Resources\Handlowiecs\Pages\EditHandlowiec;
use App\Filament\Resources\Handlowiecs\Pages\ListHandlowiecs;
use App\Filament\Resources\Handlowiecs\Schemas\HandlowiecForm;
use App\Filament\Resources\Handlowiecs\Tables\HandlowiecsTable;
use App\Models\Handlowiec;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HandlowiecResource extends Resource
{
    protected static ?string $model = Handlowiec::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;


    protected static ?string $recordTitleAttribute = 'Handlowiec';
    protected static ?string $pluralModelLabel = 'handlowiec';
    public static function getNavigationGroup(): ?string
    {
        return 'Zamówienia';
    }

    public static function form(Schema $schema): Schema
    {
        return HandlowiecForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HandlowiecsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHandlowiecs::route('/'),
            'create' => CreateHandlowiec::route('/create'),
            'edit' => EditHandlowiec::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
