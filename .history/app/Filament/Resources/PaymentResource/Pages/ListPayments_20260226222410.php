<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\PaymentExporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    /**
     * Filament v3
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->createPaymentModalAction(),
        ];
    }

    /**
     * Filament v2 fallback (safe to keep in v3 too)
     */
    protected function getActions(): array
    {
        return [
            $this->createPaymentModalAction(),
        ];
    }
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

    // protected function createPaymentModalAction()
    // {
    //     // Filament v3
    //     if (class_exists(\Filament\Actions\Action::class)) {
    //         return \Filament\Actions\Action::make('create')
    //             ->label('Create Payment')
    //             ->modalHeading('Create Payment')
    //             ->modalWidth('3xl')
    //             ->form(fn() => PaymentResource::getFormSchema())
    //             ->action(fn(array $data) => Payment::create($data))
    //             ->successNotificationTitle('Created successfully ✅');
    //     }
    //     ExportAction::make()
    //         ->exporter(PaymentExporter::class)
    //         ->formats([ExportFormat::Xlsx]); // ចង់បាន Excel តែប៉ុណ្ណោះ
    //     // Filament v2
    //     return \Filament\Pages\Actions\Action::make('create')
    //         ->label('Create Payment')
    //         ->modalHeading('Create Payment')
    //         ->modalWidth('3xl')
    //         ->form(fn() => PaymentResource::getFormSchema())
    //         ->action(fn(array $data) => Payment::create($data))
    //         ->successNotificationTitle('Created successfully ✅');
    // }
}
