<?php

namespace App\Filament\Resources\BillingSettingResource\Pages;

use App\Filament\Resources\BillingSettingResource;
use App\Models\BillingSetting;
use Filament\Resources\Pages\ListRecords;

class ListBillingSettings extends ListRecords
{
    protected static string $resource = BillingSettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $setting = BillingSetting::singleton();

        // direct go to edit (so user sees form immediately)
        $this->redirect(BillingSettingResource::getUrl('edit', ['record' => $setting]));
    }
}
