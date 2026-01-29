<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

// ✅ ប្ដូរ Models តាម project អ្នក
use App\Models\House;      // or Room
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Report;
use App\Models\HouseType;  // or RoomType
use App\Models\User;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $paymentsThisMonth = Payment::query()
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');

        return [
            Stat::make('Total Houses', House::count())
                ->icon('heroicon-o-home')
                ->color('primary'),

            Stat::make('Total Tenants', Tenant::count())
                ->icon('heroicon-o-user-group')
                ->color('warning'),

            Stat::make('Payments This Month', number_format((float) $paymentsThisMonth, 2))
                ->icon('heroicon-o-document-text')
                ->color('success'),

            Stat::make('Total Reports', Report::count())
                ->icon('heroicon-o-clipboard-document-list')
                ->color('danger'),

            Stat::make('Total House Type', HouseType::count())
                ->icon('heroicon-o-building-office-2')
                ->color('info'),

            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users')
                ->color('gray'),
        ];
    }

    // ✅ 3 cards per row (ដូចរូប)
    protected function getColumns(): int
    {
        return 3;
    }
}
