<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Tenant;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Tenant')
                ->modalHeading('Create Tenant')
                ->modalWidth('xl')
                ->form(TenantResource::getFormSchema('create'))
                ->action(function (array $data): void {
                    // ✅ if password blank, don't set
                    if (blank($data['password'] ?? null)) {
                        unset($data['password']);
                    }
                    Tenant::create($data);
                })
                ,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')->badge(Tenant::query()->count()),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Active'))
                ->badge(Tenant::query()->where('status', 'Active')->count())
                ->badgeColor('success'),

            'past' => Tab::make('Past')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Past'))
                ->badge(Tenant::query()->where('status', 'Past')->count())
                ->badgeColor('warning'),

            'evicted' => Tab::make('Evicted')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Evicted'))
                ->badge(Tenant::query()->where('status', 'Evicted')->count())
                ->badgeColor('danger'),
        ];
    }
}
