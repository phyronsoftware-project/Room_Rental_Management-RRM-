<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentResource;
use App\Models\User;
use App\Models\Room;
use App\Models\Property;
use App\Models\Payment;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\RoomResource;
use App\Filament\Resources\TenantResource;
use App\Filament\Resources\PropertyResource;

class DashboardStats extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalUsers      = User::query()->count();
        $totalRooms      = Room::query()->count();
        $totalProperties = Property::query()->count();
        $totalTenants    = Tenant::query()->count();

        // ✅ Active Houses (REAL): count properties that have at least 1 occupied/rented room
        $activeHouses = (int) Room::query()
            ->whereIn('status', ['occupied', 'rented'])
            ->distinct('property_id')
            ->count('property_id');

        // ✅ Payments This Month/Year (sum by payment_date)
        $year = now()->year;

        // ✅ Total payments this year
        $paymentsThisYear = (float) Payment::query()
            ->whereBetween('payment_date', [
                now()->startOfYear()->toDateString(),
                now()->endOfYear()->toDateString(),
            ])
            ->sum('amount');

        // ✅ Total payments this month
        $paymentsThisMonth = (float) Payment::query()
            ->whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $paymentsYearDisplay  = '$' . number_format($paymentsThisYear, 2);
        $paymentsMonthDisplay = '$' . number_format($paymentsThisMonth, 2);

        // ✅ Chart = sums per month (Jan..Dec) for current year
        $monthlyMap = Payment::query()
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as m, SUM(amount) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $paymentsChart = collect(range(1, 12))
            ->map(fn($m) => (int) round((float) ($monthlyMap[$m] ?? 0)))
            ->all();

        $cardBase = 'dark:bg-gray-800 dark:text-gray-100';

        return [
            Stat::make('Total Properties', (string) $totalProperties)
                ->description('Buildings / properties')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->icon('heroicon-o-building-office-2')
                ->color('info')
                ->chart([1, 2, 2, 3, 3, 4, $totalProperties])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-sky-50 ring-sky-100',
                ])
                ->url(PropertyResource::getUrl('index')),

            Stat::make('Total Rooms', (string) $totalRooms)
                ->description('Rooms in system')
                ->descriptionIcon('heroicon-m-home-modern')
                ->icon('heroicon-o-home-modern')
                ->color('primary')
                ->chart([2, 4, 6, 8, 10, 12, $totalRooms])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-indigo-50 ring-indigo-100',
                ])
                ->url(RoomResource::getUrl('index')),

            // ✅ REAL value instead of hard-coded "20"
            Stat::make('Total Houses', (string) $activeHouses)
                ->description('Active houses in system')
                ->descriptionIcon('heroicon-m-home')
                ->icon('heroicon-o-home')
                ->color('primary')
                ->chart([12, 14, 13, 15, 18, 16, $activeHouses])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-blue-50 ring-blue-100',
                ]),

            Stat::make('Total Tenants', (string) $totalTenants)
                ->description('Currently renting')
                ->descriptionIcon('heroicon-m-user-group')
                ->icon('heroicon-o-user-group')
                ->color('warning')
                ->chart([8, 9, 10, 11, 12, 13, 14, $totalTenants])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-amber-50 ring-amber-100',
                ])
                ->url(TenantResource::getUrl('index')),

            Stat::make("Payments ({$year})", $paymentsYearDisplay)
                ->description("This month: {$paymentsMonthDisplay}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->chart($paymentsChart)
                ->extraAttributes(['class' => $cardBase . ' bg-emerald-50 ring-emerald-100'])
                ->url(PaymentResource::getUrl('index')),

            Stat::make('Total Users', (string) $totalUsers)
                ->description('Admins & staff')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-users')
                ->color('gray')
                ->chart([6, 7, 7, 8, 9, 12, $totalUsers])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-gray-50 ring-gray-100',
                ])
                ->url(UserResource::getUrl('index')),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
