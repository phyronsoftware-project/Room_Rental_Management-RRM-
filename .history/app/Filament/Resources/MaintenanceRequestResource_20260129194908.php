<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Maintenance';
    protected static ?int $navigationSort = 4;

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('property_id')
                        ->label('Property')
                        ->options(fn() => Property::query()
                            ->orderBy('name')
                            ->pluck('name', 'property_id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('room_id')
                        ->label('Room')
                        ->options(fn() => Room::query()
                            ->orderBy('room_number')
                            ->pluck('room_number', 'room_id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'Pending' => 'Pending',
                            'In Progress' => 'In Progress',
                            'Completed' => 'Completed',
                            'Cancelled' => 'Cancelled',
                        ])
                        ->default('Pending')
                        ->required(),

                    Forms\Components\TextInput::make('assigned_to')
                        ->label('Assigned To')
                        ->maxLength(100)
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('date_reported')
                        ->label('Date Reported')
                        ->seconds(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\Textarea::make('issue_reported')
                        ->label('Issue Reported')
                        ->rows(5)
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('request_id')
    //                 ->label('ID')
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('property.name')
    //                 ->label('Property')
    //                 ->formatStateUsing(fn($state, $record) => $record->property?->name ?? '-')
    //                 ->sortable()
    //                 ->searchable(false),

    //             Tables\Columns\TextColumn::make('room.room_number')
    //                 ->label('Room')
    //                 ->formatStateUsing(fn($state, $record) => $record->room?->room_number ?? '-')
    //                 ->sortable()
    //                 ->searchable(false),

    //             Tables\Columns\TextColumn::make('issue_reported')
    //                 ->label('Issue')
    //                 ->limit(50)
    //                 ->wrap(),

    //             Tables\Columns\BadgeColumn::make('status')
    //                 ->colors([
    //                     'warning' => 'Pending',
    //                     'info' => 'In Progress',
    //                     'success' => 'Completed',
    //                     'danger' => 'Cancelled',
    //                 ])
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('assigned_to')
    //                 ->label('Assigned')
    //                 ->toggleable(),

    //             Tables\Columns\TextColumn::make('date_reported')
    //                 ->label('Reported At')
    //                 ->dateTime()
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->dateTime()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //         ])
    //         ->actions([
    //             Tables\Actions\Action::make('edit')
    //                 ->label('Edit')
    //                 ->icon('heroicon-o-pencil-square')
    //                 ->modalHeading('Edit Maintenance Request')
    //                 ->modalWidth('3xl')
    //                 ->form(self::getFormSchema())
    //                 ->fillForm(fn(MaintenanceRequest $record) => [
    //                     'property_id' => $record->property_id,
    //                     'room_id' => $record->room_id,
    //                     'issue_reported' => $record->issue_reported,
    //                     'status' => $record->status,
    //                     'date_reported' => $record->date_reported,
    //                     'assigned_to' => $record->assigned_to,
    //                 ])
    //                 ->successNotificationTitle('Updated successfully ✅')
    //                 ->action(fn(MaintenanceRequest $record, array $data) => $record->update($data)),

    //             Tables\Actions\DeleteAction::make()
    //                 ->successNotificationTitle('Deleted successfully ✅'),
    //         ])
    //         ->filters([
    //             SelectFilter::make('status')
    //                 ->label('Status')
    //                 ->options([
    //                     'Pending' => 'Pending',
    //                     'In Progress' => 'In Progress',
    //                     'Completed' => 'Completed',
    //                     'Cancelled' => 'Cancelled',
    //                 ])
    //                 ->searchable()   // ✅ add search
    //                 ->preload()      // ✅ load options for search
    //                 ->placeholder('All'),

    //             SelectFilter::make('property_id')
    //                 ->label('Property')
    //                 ->options(fn() => Property::query()
    //                     ->orderBy('name')
    //                     ->pluck('name', 'property_id')
    //                     ->toArray())
    //                 ->searchable(false)
    //                 ->preload(),

    //             SelectFilter::make('room_id')
    //                 ->label('Room')
    //                 ->options(fn() => Room::query()
    //                     ->orderBy('room_number')
    //                     ->pluck('room_number', 'room_id')
    //                     ->toArray())
    //                 ->searchable(false)
    //                 ->preload(),
    //         ])
    //         ->filtersLayout(FiltersLayout::AboveContent)
    //         ->bulkActions([
    //             Tables\Actions\DeleteBulkAction::make(),
    //         ]);
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceRequests::route('/'),
            // ✅ modal only
        ];
    }
}
