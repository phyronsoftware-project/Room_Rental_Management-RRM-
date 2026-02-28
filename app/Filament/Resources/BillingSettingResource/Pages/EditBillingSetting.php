<?php

namespace App\Filament\Resources\BillingSettingResource\Pages;

use App\Filament\Resources\BillingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillingSetting extends EditRecord
{
    protected static string $resource = BillingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
