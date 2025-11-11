<?php

namespace App\Filament\Resources\Zamowienies;

use App\Filament\Resources\Zamowienies\Pages\CreateZamowienie;
use App\Filament\Resources\Zamowienies\Pages\EditZamowienie;
use App\Filament\Resources\Zamowienies\Pages\ListZamowienies;
use App\Filament\Resources\Zamowienies\Schemas\ZamowienieForm;
use App\Filament\Resources\Zamowienies\Tables\ZamowieniesTable;
use App\Models\Zamowienie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ZamowienieResource extends Resource
{
    protected static ?string $model = Zamowienie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Zamówienie';
    protected static ?string $pluralModelLabel = 'zamówienia';
    // public static function getNavigationGroup(): ?string
    // {
    //     return 'Zamówienia';
    // }

    public static function form(Schema $schema): Schema
    {
        return ZamowienieForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ZamowieniesTable::configure($table);
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
            'index' => ListZamowienies::route('/'),
            'create' => CreateZamowienie::route('/create'),
            'edit' => EditZamowienie::route('/{record}/edit'),
        ];
    }
}
