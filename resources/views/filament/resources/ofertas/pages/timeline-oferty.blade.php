<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header z statystykami --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $groupedOfertas->sum('count') }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Wszystkie oferty
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($groupedOfertas->sum('total_net'), 2, ',', ' ') }} PLN
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Suma netto
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($groupedOfertas->sum('total_gross'), 2, ',', ' ') }} PLN
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Suma brutto
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Timeline --}}
        <div class="relative">
            {{-- Linia osi czasu --}}
            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            @foreach($groupedOfertas as $group)
                <div class="mb-8 relative">
                    {{-- Znacznik miesiąca --}}
                    <div class="flex items-center mb-4">
                        <div class="absolute left-5 w-7 h-7 bg-primary-500 rounded-full border-4 border-white dark:border-gray-900 z-10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-16 flex items-center justify-between w-full">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $group['month'] }}
                            </h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $group['count'] }} {{ $group['count'] === 1 ? 'oferta' : 'ofert' }} | 
                                {{ number_format($group['total_gross'], 2, ',', ' ') }} PLN
                            </div>
                        </div>
                    </div>

                    {{-- Karty ofert --}}
                    <div class="ml-16 space-y-3">
                        @foreach($group['ofertas'] as $oferta)
                            <x-filament::section class="hover:shadow-lg transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <a href="{{ route('filament.panel.resources.ofertas.edit', $oferta) }}" class="font-bold text-lg text-primary-600 dark:text-primary-400 hover:underline">
                                                {{ $oferta->numer }}
                                            </a>
                                            
                                            @php
                                                $statusConfig = match($oferta->status) {
                                                    'draft' => ['label' => '📝 Szkic', 'color' => 'gray'],
                                                    'sent' => ['label' => '📤 Wysłana', 'color' => 'info'],
                                                    'accepted' => ['label' => '✅ Zaakceptowana', 'color' => 'success'],
                                                    'rejected' => ['label' => '❌ Odrzucona', 'color' => 'danger'],
                                                    'converted' => ['label' => '🔁 Zamówienie', 'color' => 'warning'],
                                                    default => ['label' => $oferta->status, 'color' => 'gray']
                                                };
                                            @endphp
                                            
                                            <x-filament::badge :color="$statusConfig['color']">
                                                {{ $statusConfig['label'] }}
                                            </x-filament::badge>
                                        </div>

                                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                            <div>
                                                <span class="text-gray-600 dark:text-gray-400">Firma:</span>
                                                <span class="font-semibold">{{ $oferta->firma->nazwa ?? '—' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 dark:text-gray-400">Handlowiec:</span>
                                                <span class="font-semibold">
                                                    {{ $oferta->handlowiec ? $oferta->handlowiec->imie . ' ' . $oferta->handlowiec->nazwisko : '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 dark:text-gray-400">Opiekun:</span>
                                                <span class="font-semibold">{{ $oferta->user->name ?? '—' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 dark:text-gray-400">Data:</span>
                                                <span class="font-semibold">{{ $oferta->created_at->format('d.m.Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right ml-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Wartość brutto</div>
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                            {{ number_format($oferta->total_gross, 2, ',', ' ') }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $oferta->waluta }}</div>
                                    </div>
                                </div>

                                {{-- Pozycje --}}
                                @if($oferta->pozycje->count() > 0)
                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                            {{ $oferta->pozycje->count() }} {{ $oferta->pozycje->count() === 1 ? 'pozycja' : 'pozycji' }}
                                        </div>
                                    </div>
                                @endif
                            </x-filament::section>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
