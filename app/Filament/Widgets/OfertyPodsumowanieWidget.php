<?php

namespace App\Filament\Widgets;

use App\Models\Oferta;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfertyPodsumowanieWidget extends ChartWidget
{
    protected ?string $heading = '📈 Liczba ofert w miesiącach (wg statusu)';
   // protected int|string|array $columnSpan = 'full';
   protected static ?int $sort = 3;


    protected function getData(): array
    {
        // Zakładamy analizę z ostatnich 6 miesięcy
        $months = collect(range(0, 5))
            ->map(fn($i) => Carbon::now()->subMonths($i)->format('Y-m'))
            ->reverse()
            ->values();

        $statuses = [
            'draft' => 'Szkic',
            'sent' => 'Wysłana',
            'accepted' => 'Zaakceptowana',
            'rejected' => 'Odrzucona',
            'converted' => 'Zamówienie',
        ];

        $colors = [
            'draft' => '#9CA3AF',     // Szary
            'sent' => '#FACC15',      // Żółty
            'accepted' => '#22C55E',  // Zielony
            'rejected' => '#EF4444',  // Czerwony
            'converted' => '#3B82F6', // Niebieski
        ];

        $datasets = [];

        foreach ($statuses as $status => $label) {
            $data = $months->map(function ($month) use ($status) {
                return Oferta::where('status', $status)
                    ->whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->count();
            });

            $datasets[] = [
                'label' => $label,
                'data' => $data->toArray(),
                'backgroundColor' => $colors[$status],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->isoFormat('MMM Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        // 📊 Wykres słupkowy
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'font' => [
                            'size' => 13,
                            'weight' => '500',
                        ],
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}