<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Rooms';

    // ✅ reuse schema for modal (no sub box)
    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('property_id')
                    ->label('Property')
                    ->options(fn() => Property::query()->orderBy('name')->pluck('name', 'property_id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('room_number')
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('floor')
                    ->maxLength(50)
                    ->nullable(),

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('$'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Occupied' => 'Occupied',
                        'Maintenance' => 'Maintenance',
                    ])
                    ->default('Available')
                    ->required(),
            ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Room::query())
            ->columns([
                Tables\Columns\TextColumn::make('room_id')->label('ID')->sortable(),

                // ✅ show property name
                Tables\Columns\TextColumn::make('property_id')
                    ->label('Property')
                    ->formatStateUsing(fn($state) => $state
                        ? (Property::query()->where('property_id', $state)->value('name') ?? '-')
                        : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('room_number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('floor')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('USD')->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Available',
                        'warning' => 'Occupied',
                        'danger' => 'Maintenance',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Room')
                    ->modalWidth('xl') // ✅ same as create
                    ->form(self::getFormSchema()) // ✅ same schema as create
                    ->fillForm(fn(Room $record) => [
                        'property_id'  => $record->property_id,
                        'room_number'  => $record->room_number,
                        'floor'        => $record->floor,      // ✅ add
                        'price'        => $record->price,
                        'status'       => $record->status,     // ✅ add
                    ])
                    ->successNotificationTitle('Updated successfully ✅')
                    ->action(fn(Room $record, array $data) => $record->update($data)),

                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Deleted successfully ✅'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            // ✅ modal only (no create/edit pages)
        ];
    }
}
