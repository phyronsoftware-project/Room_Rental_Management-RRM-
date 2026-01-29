<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    // ✅ 2 columns for widgets (chart/table)
    public function getColumns(): int|array
    {
        return 2;
    }


    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,       // cards
            // \App\Filament\Widgets\BookingTrendChart::class,    // pie (half)
            // \App\Filament\Widgets\DashboardStaticTable::class, // table (half)
        ];
    }
}
