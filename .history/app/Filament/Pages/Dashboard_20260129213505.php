<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use App\Models\Property;

class Dashboard extends BaseDashboard
{
    /**
     * ✅ Global filters for widgets
     * Widgets will read: $this->filters['property_id']
     */
    protected function getFiltersFormSchema(): array
    {
        return [
            Select::make('property_id')
                ->label('Filter by Property')
                ->options(Property::query()->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->native(false),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\QuickActionsWidget::class,
            \App\Filament\Widgets\DashboardStatsEnhanced::class,

            \App\Filament\Widgets\RoomsPerMonthChart::class,
            \App\Filament\Widgets\TenantsPerMonthChart::class,
            \App\Filament\Widgets\RoomsByPropertyChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\VacantRoomsTable::class,
            \App\Filament\Widgets\RecentTenantsTable::class,
        ];
    }
}
