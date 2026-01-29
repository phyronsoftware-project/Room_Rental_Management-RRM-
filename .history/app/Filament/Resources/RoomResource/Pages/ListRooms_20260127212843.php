<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\RoomResource;
use App\Models\Room;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make()->label('Create Room'),
    //     ];
    // }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Room::query()->count()),

            'available' => Tab::make('Available')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Available'))
                ->badge(Room::query()->where('status', 'Available')->count())
                ->badgeColor('success'),

            'occupied' => Tab::make('Occupied')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Occupied'))
                ->badge(Room::query()->where('status', 'Occupied')->count())
                ->badgeColor('warning'),

            'maintenance' => Tab::make('Maintenance')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Maintenance'))
                ->badge(Room::query()->where('status', 'Maintenance')->count())
                ->badgeColor('danger'),
            Actions\CreateAction::make()->label('Create Room'),
        ];
    }
}
