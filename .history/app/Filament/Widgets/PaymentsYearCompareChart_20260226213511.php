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
     * Cambodia exchange rate (KHR per 1 USD).
     * If you want to change rate: set config('app.khr_per_usd') in config/app.php
     */
    protected function getKhrPerUsd(): float
    {
        return (float) (config('app.khr_per_usd') ?? 4100);
    }

    protected function getData(): array
    {
        $year     = now()->year;
        $lastYear = $year - 1;

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
                    'backgroundColor' => '#3B82F6',
                    'borderRadius' => 10,
                ],
                [
                    'label' => (string) $lastYear,
                    'data' => $lastYearData,
                    'backgroundColor' => '#CBD5E1',
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

        // ✅ SAFE formatting (no Intl "notation: compact" to avoid JS errors)
        $formatJs = "
            function _compact(n) {
                n = Number(n) || 0;
                const abs = Math.abs(n);
                if (abs >= 1000000000) return (n / 1000000000).toFixed(1).replace(/\\.0$/, '') + 'B';
                if (abs >= 1000000)    return (n / 1000000).toFixed(1).replace(/\\.0$/, '') + 'M';
                if (abs >= 1000)       return (n / 1000).toFixed(1).replace(/\\.0$/, '') + 'K';
                return n.toFixed(0);
            }

            function _usd(n) {
                n = Number(n) || 0;
                // Show compact like $15K
                return '$' + _compact(n);
            }

            function _khr(n, rate) {
                n = Number(n) || 0;
                const k = Math.round(n * rate);
                // Show compact like ៛60M
                return '៛' + _compact(k);
            }

            function _usdFull(n) {
                n = Number(n) || 0;
                return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function _khrFullFromUsd(n, rate) {
                n = Number(n) || 0;
                const k = Math.round(n * rate);
                return '៛' + k.toLocaleString('en-US');
            }
        ";

        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => RawJs::make("
                            function(context) {
                                {$formatJs}
                                const rate = {$rate};
                                const v = (context.parsed && context.parsed.y !== undefined) ? context.parsed.y : context.raw;

                                const usd = _usdFull(v);
                                const khr = _khrFullFromUsd(v, rate);

                                return context.dataset.label + ': ' + usd + ' (' + khr + ')';
                            }
                        "),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        // Show BOTH currencies on axis ticks: "$15K | ៛61M"
                        'callback' => RawJs::make("
                            function(value) {
                                {$formatJs}
                                const rate = {$rate};
                                const usd = _usd(value);
                                const khr = _khr(value, rate);
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
