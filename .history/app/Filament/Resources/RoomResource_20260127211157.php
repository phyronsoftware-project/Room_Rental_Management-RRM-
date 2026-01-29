<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
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
    protected static ?string $modelLabel = 'Room';
    protected static ?string $pluralModelLabel = 'Rooms';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Room Info')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('property_id')
                        ->label('Property')
                        ->relationship('property', 'name')
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('property_id')->label('Property')->sortable(),
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
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
