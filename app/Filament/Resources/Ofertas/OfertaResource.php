<?php

namespace App\Filament\Resources\Ofertas;

use App\Filament\Resources\Ofertas\Pages\CreateOferta;
use App\Filament\Resources\Ofertas\Pages\EditOferta;
use App\Filament\Resources\Ofertas\Pages\ListOfertas;
use App\Filament\Resources\Ofertas\Schemas\OfertaForm;
use App\Filament\Resources\Ofertas\Tables\OfertasTable;
use App\Models\Oferta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfertaResource extends Resource
{
    protected static ?string $model = Oferta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Oferta';

    public static function form(Schema $schema): Schema
    {
        return OfertaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfertasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OfertaPozycjeRelationManager::class,
        
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfertas::route('/'),
            'create' => CreateOferta::route('/create'),
            'edit' => EditOferta::route('/{record}/edit'),
        ];
    }
}
