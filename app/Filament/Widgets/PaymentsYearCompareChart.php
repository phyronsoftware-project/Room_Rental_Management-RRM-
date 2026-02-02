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

    protected function getData(): array
    {
        $year = now()->year;
        $lastYear = $year - 1;

        // ✅ map: month => sum(amount)
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
                ],
            ],
        ];
    }
}
