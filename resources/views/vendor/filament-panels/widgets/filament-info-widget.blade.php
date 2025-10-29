<x-filament-widgets::widget>
    <x-filament::card>
        <div class="flex flex-col gap-4">
            {{-- Nagłówek z logo --}}
            <div class="flex items-center gap-4">
                <img
                    src="https://pankobido.pl/wp-content/uploads/2022/11/pankobido_wawa.png"
                    alt="Pan Kobido Logo"
                    class="w-16 h-auto"
                />

                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Witaj w CRM Pan Kobido 
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Zarządzaj szkoleniami, uczestnikami i dostępami w jednym miejscu.
                    </p>

                </div>

            </div>
            <div class="text-xs text-gray-500 text-center">
    Wersja aplikacji: {{ app_version() }}
</div>
            {{-- Informacja kontaktowa i stopka --}}
            <div class="text-sm text-gray-600 dark:text-gray-400">
                W razie problemów skontaktuj się: 
                <a href="mailto:kontakt@jancybulski.pl" class="text-primary-600 hover:underline">
                    kontakt@jancybulski.pl
                </a>
                <br>
                <span>
                    Zaprogramowane przez 
                    <a href="https://creospace.eu" target="_blank" class="text-primary-600 hover:underline">
                        Creospace
                    </a>
                </span>
            </div>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>