<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;
use Filament\Support\RawJs;

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
     * Default Cambodia exchange rate.
     * You can override by setting config('app.khr_per_usd') (ex: 4100).
     */
    protected function getKhrPerUsd(): float
    {
        return (float) (config('app.khr_per_usd') ?? 4100);
    }

    protected function getData(): array
    {
        $year = now()->year;
        $lastYear = $year - 1;

        // ✅ map: month => sum(amount) (amount is assumed USD)
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

        $thisYearData = collect(range(1, 12))
            ->map(fn($m) => (float) ($thisYearMap[$m] ?? 0))
            ->all();

        $lastYearData = collect(range(1, 12))
            ->map(fn($m) => (float) ($lastYearMap[$m] ?? 0))
            ->all();

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => (string) $year,
                    'data' => $thisYearData,
                    'backgroundColor' => '#3B82F6', // blue
                    'borderRadius' => 10,
                ],
                [
                    'label' => (string) $lastYear,
                    'data' => $lastYearData,
                    'backgroundColor' => '#CBD5E1', // gray
                    'borderRadius' => 10,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                    'callbacks' => [
                        // Show both USD & KHR in tooltip
                        'label' => RawJs::make("
                            function(context) {
                                const rate = {$rate};
                                const v = (context.parsed && context.parsed.y !== undefined) ? context.parsed.y : context.raw;

                                const usd = new Intl.NumberFormat('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }).format(v);

                                const khr = new Intl.NumberFormat('km-KH', {
                                    style: 'currency',
                                    currency: 'KHR',
                                    maximumFractionDigits: 0
                                }).format(v * rate);

                                return context.dataset.label + ': ' + usd + ' (' + khr + ')';
                            }
                        "),
                        'footer' => RawJs::make("
                            function() {
                                const rate = {$rate};
                                const khrPerUsd = new Intl.NumberFormat('km-KH', {
                                    style: 'currency',
                                    currency: 'KHR',
                                    maximumFractionDigits: 0
                                }).format(rate);

                                return 'Rate: 1 USD ≈ ' + khrPerUsd;
                            }
                        "),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        // Show both USD & KHR on axis ticks
                        'callback' => RawJs::make("
                            function(value) {
                                const rate = {$rate};

                                // compact display
                                const usd = new Intl.NumberFormat('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    notation: 'compact',
                                    maximumFractionDigits: 1
                                }).format(value);

                                const khr = new Intl.NumberFormat('km-KH', {
                                    style: 'currency',
                                    currency: 'KHR',
                                    notation: 'compact',
                                    maximumFractionDigits: 1
                                }).format(value * rate);

                                return usd + ' | ' + khr;
                            }
                        "),
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
