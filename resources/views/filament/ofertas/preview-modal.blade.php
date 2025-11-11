<div class="space-y-6">
    {{-- Nagłówek oferty --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-gray-500 dark:text-gray-400">Numer oferty</p>
            <p class="font-semibold text-primary-600 dark:text-primary-400">{{ $oferta->numer }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Firma</p>
            <p class="font-semibold">{{ $oferta->firma->nazwa ?? '—' }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Handlowiec</p>
            <p>{{ $oferta->handlowiec->imie ?? '' }} {{ $oferta->handlowiec->nazwisko ?? '' }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Status</p>
            <p>
                @php
                    $statusColors = [
                        'draft' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                        'sent' => 'bg-blue-200 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                        'accepted' => 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-100',
                        'rejected' => 'bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-100',
                        'converted' => 'bg-amber-200 text-amber-800 dark:bg-amber-800 dark:text-amber-100',
                    ];
                    $label = [
                        'draft' => '📝 Szkic',
                        'sent' => '📤 Wysłana',
                        'accepted' => '✅ Zaakceptowana',
                        'rejected' => '❌ Odrzucona',
                        'converted' => '🔁 Zamówienie',
                    ];
                @endphp
                <span class="px-2 py-1 text-xs rounded {{ $statusColors[$oferta->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $label[$oferta->status] ?? $oferta->status }}
                </span>
            </p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Waluta</p>
            <p>{{ $oferta->waluta }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Termin płatności</p>
            <p>{{ $oferta->due_date ? \Carbon\Carbon::parse($oferta->due_date)->format('d.m.Y') : '—' }}</p>
        </div>
    </div>

    {{-- Tabela pozycji --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="font-semibold text-lg mb-3">🧾 Pozycje oferty</h3>

        @if($oferta->pozycje->isEmpty())
            <p class="text-gray-500 italic">Brak pozycji w tej ofercie.</p>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-gray-800 dark:text-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-3 py-2 text-left">Nazwa</th>
                            <th class="px-3 py-2 text-right">Ilość</th>
                            <th class="px-3 py-2 text-right">Cena netto</th>
                            <th class="px-3 py-2 text-right">VAT (%)</th>
                            <th class="px-3 py-2 text-right">Netto</th>
                            <th class="px-3 py-2 text-right">Brutto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($oferta->pozycje as $pozycja)
                            <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2">{{ $pozycja->nazwa }}</td>
                                <td class="px-3 py-2 text-right">{{ $pozycja->ilosc }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($pozycja->unit_price_net, 2) }} {{ $oferta->waluta }}</td>
                                <td class="px-3 py-2 text-right">{{ $pozycja->vat_rate }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($pozycja->total_net, 2) }} {{ $oferta->waluta }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($pozycja->total_gross, 2) }} {{ $oferta->waluta }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Podsumowanie --}}
    <div class="flex justify-end gap-6 text-sm pt-4 border-t border-gray-200 dark:border-gray-700">
        <div>
            <p class="text-gray-500">Suma netto</p>
            <p class="font-semibold text-blue-600 dark:text-blue-400">
                {{ number_format($oferta->total_net, 2) }} {{ $oferta->waluta }}
            </p>
        </div>
        <div>
            <p class="text-gray-500">Suma brutto</p>
            <p class="font-semibold text-green-700 dark:text-green-400">
                {{ number_format($oferta->total_gross, 2) }} {{ $oferta->waluta }}
            </p>
        </div>
    </div>

    {{-- Uwagi --}}
    @if($oferta->uwagi)
        <div class="mt-6">
            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-1">💬 Uwagi</h4>
            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $oferta->uwagi }}</p>
        </div>
    @endif
</div>