<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,
            // \App\Filament\Widgets\BookingTrendChart::class,
        ];
    }
}
