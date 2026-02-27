<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;

class PaymentsYearCompareChart extends ChartWidget
{
    // Keep the same heading (you can change if you want)
    protected static ?string $heading = 'Payments by Month (KHR - This Year)';
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
    ];

    /**
     * Cambodia exchange rate (KHR per 1 USD).
     * Change by config('app.khr_per_usd') or .env APP_KHR_PER_USD=4100
     */
    protected function getKhrPerUsd(): float
    {
        return (float) (config('app.khr_per_usd') ?? env('APP_KHR_PER_USD', 4100));
    }

    protected function getData(): array
    {
        $year = now()->year;
        $rate = $this->getKhrPerUsd();

        // Sum USD by month (real data) then convert to KHR
        $thisYearMap = Payment::query()
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as m, SUM(amount) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $khrData = collect(range(1, 12))
            ->map(fn($m) => (float) (($thisYearMap[$m] ?? 0) * $rate))
            ->all();

        return [
            'labels' => $months,
            'datasets' => [
                [
                    // ✅ ONLY GREEN LINE
                    'label' => $year . ' (KHR)',
                    'data' => $khrData,
                    'type' => 'line',
                    'borderColor' => '#22C55E',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 3,
                    'tension' => 0.35,
                    // remove dots
                    'pointRadius' => 0,
                    'pointHoverRadius' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        $rate = $this->getKhrPerUsd();

        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'elements' => [
                'point' => [
                    'radius' => 0,
                    'hoverRadius' => 0,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'KHR (៛) — Rate 1$ ≈ ' . number_format($rate) . '៛',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'autoSkip' => false,
                        'maxRotation' => 0,
                        'minRotation' => 0,
                    ],
                ],
            ],
        ];
    }
}
