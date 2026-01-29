<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Houses', 20)->icon('heroicon-o-home')->color('primary'),
            Stat::make('Total Tenants', 14)->icon('heroicon-o-user-group')->color('warning'),
            Stat::make('Payments This Month', '1,300.00')->icon('heroicon-o-document-text')->color('success'),

            Stat::make('Total Reports', 14)->icon('heroicon-o-clipboard-document-list')->color('danger'),
            Stat::make('Total House Type', 14)->icon('heroicon-o-building-office-2')->color('info'),
            Stat::make('Total Users', 14)->icon('heroicon-o-users')->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // 3 cards per row
    }
}
