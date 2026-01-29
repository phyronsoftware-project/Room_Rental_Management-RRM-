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

    protected function getTableQuery(): Builder
    {
        // ✅ IMPORTANT: always start from parent so model is never null
        return parent::getTableQuery();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Active'))
                ->badgeColor('success'),

            'past' => Tab::make('Past')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Past'))
                ->badgeColor('warning'),

            'evicted' => Tab::make('Evicted')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Evicted'))
                ->badgeColor('danger'),
        ];
    }
}
