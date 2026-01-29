<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class DashboardStaticTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Users & Payments';

    // ✅ half width (2 columns layout)
    protected int|string|array $columnSpan = 1;

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('type')->label('Type'),
            Tables\Columns\TextColumn::make('name')->label('Name'),
            Tables\Columns\TextColumn::make('amount')->label('Amount'),
            Tables\Columns\TextColumn::make('date')->label('Date'),
            Tables\Columns\BadgeColumn::make('status')->label('Status'),
        ];
    }

    public function getTableRecords(): array
    {
        return [
            [
                'type' => 'User',
                'name' => 'John Doe',
                'amount' => '-',
                'date' => '2026-01-26',
                'status' => 'Active',
            ],
            [
                'type' => 'Payment',
                'name' => 'Tenant A',
                'amount' => '$120.00',
                'date' => '2026-01-25',
                'status' => 'Paid',
            ],
            [
                'type' => 'Payment',
                'name' => 'Tenant B',
                'amount' => '$80.00',
                'date' => '2026-01-24',
                'status' => 'Pending',
            ],
            [
                'type' => 'User',
                'name' => 'Sok Dara',
                'amount' => '-',
                'date' => '2026-01-23',
                'status' => 'Active',
            ],
        ];
    }
}
