<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DashboardStaticTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Users & Payments';

    protected int|string|array $columnSpan = 1; // half width

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('type')->label('Type'),
            Tables\Columns\TextColumn::make('name')->label('Name'),
            Tables\Columns\TextColumn::make('amount')->label('Amount'),
            Tables\Columns\TextColumn::make('date')->label('Date'),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
        ];
    }

    // ✅ IMPORTANT: no ": array" here
    public function getTableRecords()
    {
        $rows = [
            ['type' => 'User',    'name' => 'John Doe',  'amount' => '-',       'date' => '2026-01-26', 'status' => 'Active'],
            ['type' => 'Payment', 'name' => 'Tenant A',  'amount' => '$120.00', 'date' => '2026-01-25', 'status' => 'Paid'],
            ['type' => 'Payment', 'name' => 'Tenant B',  'amount' => '$80.00',  'date' => '2026-01-24', 'status' => 'Pending'],
            ['type' => 'User',    'name' => 'Sok Dara',  'amount' => '-',       'date' => '2026-01-23', 'status' => 'Active'],
        ];

        // ✅ return Eloquent Collection (compatible with Filament)
        return new EloquentCollection($rows);
    }
}
