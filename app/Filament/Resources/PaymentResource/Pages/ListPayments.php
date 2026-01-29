<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Payment')
                ->modalHeading('Create Payment')
                ->modalWidth('3xl')
                ->form(PaymentResource::getFormSchema())
                ->action(function (array $data): void {
                    Payment::create($data);
                })
                ->successNotificationTitle('Created successfully ✅'),
        ];
    }
}
