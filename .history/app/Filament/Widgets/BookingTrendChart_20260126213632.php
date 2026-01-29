<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class BookingTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Booking Trend';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        return [
            'labels' => [
                'Single-Family Home',
                'Townhouse',
                'Condominium',
                'Duplex',
                'Tiny House',
            ],
            'datasets' => [
                [
                    'label' => 'Booking',
                    'data' => [45.8, 8.3, 8.3, 8.3, 29.2],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
