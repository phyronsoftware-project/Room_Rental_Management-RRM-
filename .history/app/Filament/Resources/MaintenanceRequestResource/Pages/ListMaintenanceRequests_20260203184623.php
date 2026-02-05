<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMaintenanceRequests extends ListRecords
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Request')
                ->onColor('primary')
                ->modalHeading('Create Maintenance Request')
                ->modalWidth('3xl')
                ->form(MaintenanceRequestResource::getFormSchema())
                ->action(fn(array $data) => MaintenanceRequest::create($data))
                ->successNotificationTitle('Created successfully ✅'),
        ];
    }

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('All')
    //             ->badge(MaintenanceRequest::query()->count()),

    //         'pending' => Tab::make('Pending')
    //             ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Pending'))
    //             ->badge(MaintenanceRequest::query()->where('status', 'Pending')->count())
    //             ->badgeColor('warning'),

    //         'in_progress' => Tab::make('In Progress')
    //             ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'In Progress'))
    //             ->badge(MaintenanceRequest::query()->where('status', 'In Progress')->count())
    //             ->badgeColor('info'),

    //         'completed' => Tab::make('Completed')
    //             ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Completed'))
    //             ->badge(MaintenanceRequest::query()->where('status', 'Completed')->count())
    //             ->badgeColor('success'),

    //         'cancelled' => Tab::make('Cancelled')
    //             ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'Cancelled'))
    //             ->badge(MaintenanceRequest::query()->where('status', 'Cancelled')->count())
    //             ->badgeColor('danger'),
    //     ];
    // }
}
