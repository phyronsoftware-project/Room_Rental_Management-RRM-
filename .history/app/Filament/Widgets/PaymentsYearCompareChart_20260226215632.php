<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;

class PaymentsYearCompareChart extends ChartWidget
{
    protected static ?string $heading = 'Payments by Month (USD & KHR - This Year)';
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

        // ✅ Real data: sum USD by month
        $thisYearMap = Payment::query()
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as m, SUM(amount) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $usdData = collect(range(1, 12))
            ->map(fn($m) => (float) ($thisYearMap[$m] ?? 0))
            ->all();

        $khrData = collect($usdData)
            ->map(fn($v) => (float) ($v * $rate))
            ->all();

        return [
            'labels' => $months,
            'datasets' => [
                // ✅ USD line (blue)
                [
                    'label' => $year . ' (USD)',
                    'data' => $usdData,
                    'type' => 'line',
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 0,
                    'yAxisID' => 'y',
                ],

                // ✅ KHR line (green)
                [
                    'label' => $year . ' (KHR)',
                    'data' => $khrData,
                    'type' => 'line',
                    'borderColor' => '#22C55E',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 3,
                    'tension' => 0.35,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 0,
                    'yAxisID' => 'y1',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * ✅ No JS callbacks (stable on Chart.js 3.3.47 + Filament)
     * We show USD + KHR using two Y-axes.
     */
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
                'line' => [
                    'fill' => false,
                ],
            ],
            'scales' => [
                // Left axis (USD)
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'USD ($)',
                    ],
                ],
                // Right axis (KHR)
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
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
