<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="fi-filament-info-widget-main">
            <a
                href="https://filamentphp.com"
                rel="noopener noreferrer"
                target="_blank"
            >
                <img
                    aria-label="Filament"
                    src="https://freztech.com.pl/wp-content/uploads/2025/10/Obszar-roboczy-1.png"
                    fill="currentColor"
                    role="img"
                    viewBox="0 0 303 61"
                    xmlns="http://www.w3.org/2000/svg"
                    class="fi-filament-info-widget-logo"
                >
      
</img>
            </a>

            <p class="fi-filament-info-widget-version">
            v{{ app_version() }}
            </p>
        </div>

        <div class="fi-filament-info-widget-links">
            <x-filament::link
                color="gray"
                href="kontakt@jancybulski.pl"
                :icon="\Filament\Support\Icons\Heroicon::BookOpen"
                :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_FILAMENT_INFO_OPEN_DOCUMENTATION_BUTTON"
                rel="noopener noreferrer"
                target="_blank"
            >
W razie problemów kontakt@jancybulski.pl            </x-filament::link>

            <x-filament::link
                color="gray"
                href="https://creospace.eu"
                :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_FILAMENT_INFO_OPEN_GITHUB_BUTTON"
                rel="noopener noreferrer"
                target="_blank"
            >
      Creospace

             </x-filament::link>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>