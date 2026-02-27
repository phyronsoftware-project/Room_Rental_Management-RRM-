<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\ListRecords;

use App\Filament\Exports\PaymentExporter;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Payment')
                ->modalHeading('Create Payment')
                ->modalWidth('3xl')
                ->form(fn() => PaymentResource::getFormSchema())
                ->action(fn(array $data) => Payment::create($data))
                ->successNotificationTitle('Created successfully ✅'),

            ExportAction::make('export')
                ->label('Export Excel')
                ->exporter(PaymentExporter::class)
                ->formats([ExportFormat::Xlsx]),
        ];
    }
}
