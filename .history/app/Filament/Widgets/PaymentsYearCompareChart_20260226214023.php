<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;

class PaymentsYearCompareChart extends ChartWidget
{
    protected static ?string $heading = 'Payments by Month (This Year vs Last Year)';
    protected static ?int $sort = 10;

    // ✅ half width (2 columns on md+)
    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
    ];

    /**
     * Cambodia exchange rate (KHR per 1 USD).
     * You can change it by adding `khr_per_usd` to config/app.php or .env -> APP_KHR_PER_USD
     */
    protected function getKhrPerUsd(): float
    {
        // supports either config('app.khr_per_usd') or env('APP_KHR_PER_USD')
        return (float) (config('app.khr_per_usd') ?? env('APP_KHR_PER_USD', 4100));
    }

    protected function getData(): array
    {
        $year     = now()->year;
        $lastYear = $year - 1;
        $rate     = $this->getKhrPerUsd();

        // ✅ map: month => sum(amount) (assumes "amount" stored as USD)
        $thisYearMap = Payment::query()
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as m, SUM(amount) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $lastYearMap = Payment::query()
            ->whereYear('payment_date', $lastYear)
            ->selectRaw('MONTH(payment_date) as m, SUM(amount) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $thisYearUsd = collect(range(1, 12))
            ->map(fn($m) => (float) ($thisYearMap[$m] ?? 0))
            ->all();

        $lastYearUsd = collect(range(1, 12))
            ->map(fn($m) => (float) ($lastYearMap[$m] ?? 0))
            ->all();

        // ✅ Convert to KHR (real data from USD amounts)
        $thisYearKhr = collect($thisYearUsd)->map(fn($v) => (float) ($v * $rate))->all();
        $lastYearKhr = collect($lastYearUsd)->map(fn($v) => (float) ($v * $rate))->all();

        return [
            'labels' => $months,
            'datasets' => [
                // USD (bars)
                [
                    'label' => $year . ' (USD)',
                    'data' => $thisYearUsd,
                    'backgroundColor' => '#3B82F6', // blue
                    'borderRadius' => 10,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => $lastYear . ' (USD)',
                    'data' => $lastYearUsd,
                    'backgroundColor' => '#CBD5E1', // gray
                    'borderRadius' => 10,
                    'yAxisID' => 'y',
                ],

                // KHR (lines) — second axis on the right
                [
                    'type' => 'line',
                    'label' => $year . ' (KHR)',
                    'data' => $thisYearKhr,
                    'borderColor' => '#22C55E', // green
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'pointRadius' => 2,
                    'tension' => 0.3,
                    'yAxisID' => 'y1',
                ],
                [
                    'type' => 'line',
                    'label' => $lastYear . ' (KHR)',
                    'data' => $lastYearKhr,
                    'borderColor' => '#94A3B8', // slate
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'pointRadius' => 2,
                    'tension' => 0.3,
                    'yAxisID' => 'y1',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * ✅ IMPORTANT:
     * Do NOT use JS callback functions here (Filament v2 serializes to JSON and breaks Chart.js).
     * We use two Y-axes to show both USD & KHR without custom JS callbacks.
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
            'scales' => [
                // Left axis (USD)
                'y' => [
                    'beginAtZero' => true,
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
                        'text' => 'KHR (៛)  —  Rate 1$ ≈ ' . number_format($rate) . '៛',
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
