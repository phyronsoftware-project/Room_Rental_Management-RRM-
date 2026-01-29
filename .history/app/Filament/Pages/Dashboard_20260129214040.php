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
            \App\Filament\Widgets\DashboardStats::class,          // ✅ cards (set full span inside widget)

            // ✅ charts
            \App\Filament\Widgets\RoomsPerMonthChart::class,      // half (or full)
            \App\Filament\Widgets\TenantsPerMonthChart::class,    // half

            // ✅ tables
            \App\Filament\Widgets\VacantRoomsTable::class,        // full (recommended)
            \App\Filament\Widgets\RecentTenantsTable::class,      // full (recommended)
        ];
    }
}
