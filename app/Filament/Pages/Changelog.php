<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Changelog extends Page
{
    protected static ?string $title = 'LOG zmian';
    public static function getNavigationGroup(): string
    {
        return 'System';
    }
    public function getContent(): string
    {
        $path = base_path('CHANGELOG.md');

        return file_exists($path)
            ? \Illuminate\Support\Str::markdown(file_get_contents($path))
            : 'Brak changeloga 😢';
    }
    
    protected string $view = 'filament.pages.changelog';
}
