<?php

namespace App\Filament\Exports;

use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PaymentExporter extends Exporter
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('payment_id'),
            ExportColumn::make('tenant_id'),
            ExportColumn::make('room_id'),
            ExportColumn::make('property_id'),
            ExportColumn::make('amount'),
            ExportColumn::make('water_fee'),
            ExportColumn::make('electricity_fee'),
            ExportColumn::make('payment_date'),
            ExportColumn::make('payment_method'),
            ExportColumn::make('notes'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('amount'),

            ExportColumn::make('grand_total')
                ->label('Grand Total (All Rows)')
                ->state(fn() => $grandTotal),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payment export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
