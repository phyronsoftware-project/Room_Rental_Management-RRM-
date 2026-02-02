<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Tenant;
use App\Models\Room;

class TenantRoomMonthlyChart extends ChartWidget
{
    protected static ?string $heading = 'ស្ថិតិប្រចាំខែ (៦ខែចុងក្រោយ)';
    protected int|string|array $columnSpan = 2; // ✅ one column (full width)

    protected function getData(): array
    {
        // ✅ last 6 months (including current month)
        $months = collect(range(5, 0))->map(function ($i) {
            $d = now()->subMonths($i)->startOfMonth();
            return [
                'key'   => $d->format('Y-m'), // ex: 2026-01
                'label' => $d->format('M'),   // Jan, Feb, ...
            ];
        });

        $start = now()->subMonths(5)->startOfMonth();
        $end   = now()->endOfMonth();

        // ✅ Tenant count by month
        $tenantMap = Tenant::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
            ->groupBy('ym')
            ->pluck('total', 'ym');

        // ✅ Room count by month
        $roomMap = Room::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = $months->pluck('label')->all();

        $tenantData = $months->map(fn($m) => (int) ($tenantMap[$m['key']] ?? 0))->all();
        $roomData   = $months->map(fn($m) => (int) ($roomMap[$m['key']] ?? 0))->all();

        return [
            'datasets' => [
                [
                    'label' => 'ចំនួនអ្នកជួលថ្មី',
                    'data'  => $tenantData,
                    // ✅ color like your image (blue)
                    'backgroundColor' => '#3B82F6',
                ],
                [
                    'label' => 'ចំនួនបន្ទប់ថ្មី',
                    'data'  => $roomData,
                    // ✅ color like your image (gray)
                    'backgroundColor' => '#D1D5DB',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    // ✅ (Optional) បើអ្នកចង់ legend នៅក្រោម ដូចរូប
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0, // no decimals
                    ],
                ],
            ],
        ];
    }
}
