<?php

namespace App\Filament\Resources\Firmas;

use App\Filament\Resources\Firmas\Pages\CreateFirma;
use App\Filament\Resources\Firmas\Pages\EditFirma;
use App\Filament\Resources\Firmas\Pages\ListFirmas;
use App\Filament\Resources\Firmas\Schemas\FirmaForm;
use App\Filament\Resources\Firmas\Tables\FirmasTable;
use App\Models\Firma;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FirmaResource extends Resource
{
    protected static ?string $model = Firma::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;


    protected static ?string $recordTitleAttribute = 'Klienci';
    protected static ?string $pluralModelLabel = 'Dane firm';
    public static function getNavigationGroup(): ?string
    {
        return 'Zamówienia';
    }
    protected static ?int $navigationSort = -1;
    public static function form(Schema $schema): Schema
    {
        return FirmaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FirmasTable::configure($table);
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
            'index' => ListFirmas::route('/'),
            'create' => CreateFirma::route('/create'),
            'edit' => EditFirma::route('/{record}/edit'),
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
