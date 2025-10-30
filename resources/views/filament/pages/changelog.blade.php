<x-filament::page>
    <div class="max-w-5xl mx-auto w-full space-y-6">

        {{-- 🔹 Nagłówek --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <x-filament::icon
                    icon="heroicon-o-clock"
                    class="w-6 h-6 text-primary-600 dark:text-primary-400"
                />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                    📜 Historia zmian systemu
                </h1>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Ostatnia aktualizacja: 
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ now()->format('d.m.Y H:i') }}
                </span>
            </div>
        </div>

        {{-- 🔸 Zawartość changeloga --}}
        <div
            class="prose dark:prose-invert max-w-none bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 p-6 transition-all duration-200 hover:shadow-md"
        >
            @if ($this->getContent() !== 'Brak changeloga 😢')
                {!! $this->getContent() !!}
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    😢 Brak changeloga – jeszcze nic nie zostało zapisane.
                </div>
            @endif
        </div>
    </div>
</x-filament::page>