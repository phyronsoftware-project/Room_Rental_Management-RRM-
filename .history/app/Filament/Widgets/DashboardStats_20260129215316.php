<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Room;
use App\Models\Property;
use App\Models\Payment;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\RoomResource;
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
        // ✅ Payments This Month (sum by payment_date)
        $paymentsThisMonth = (float) Payment::query()
            ->whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $paymentsDisplay = '$' . number_format($paymentsThisMonth, 2);


        // ✅ common UI classes for all cards
        $cardBase = 'rounded-2xl shadow-sm ring-1 hover:shadow-md transition';

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

            Stat::make('Total Houses', '20')
                ->description('Active houses in system')
                ->descriptionIcon('heroicon-m-home')
                ->icon('heroicon-o-home')
                ->color('primary')
                ->chart([12, 14, 13, 15, 18, 16, 20])
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
                ]),

            Stat::make('Payments This Month', $paymentsDisplay)
                ->description('Collected in current month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->chart([300, 420, 380, 510, 450, 560, (int) round($paymentsThisMonth)])
                ->extraAttributes(['class' => $cardBase . ' bg-emerald-50 ring-emerald-100'])
                ->url(Payment::getUrl('index')),

            Stat::make('Total Reports', '14')
                ->description('Maintenance & issues')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('danger')
                ->chart([2, 3, 4, 6, 8, 10, 14])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-rose-50 ring-rose-100',
                ]),

            Stat::make('Total House Types', '14')
                ->description('Categories available')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->icon('heroicon-o-building-office-2')
                ->color('info')
                ->chart([4, 5, 6, 7, 9, 11, 14])
                ->extraAttributes([
                    'class' => $cardBase . ' bg-cyan-50 ring-cyan-100',
                ]),

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
        return 4;
    }
}
