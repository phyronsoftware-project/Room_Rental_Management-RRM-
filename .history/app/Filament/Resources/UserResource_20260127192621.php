<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('User Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('full_name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                        ->required(fn(string $operation): bool => $operation === 'create')
                        ->hiddenOn('edit'), // ងាយៗ: edit មិនបង្ហាញ password

                    Forms\Components\Select::make('role')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'owner' => 'Owner',
                            'manager' => 'Manager',
                        ])
                        ->default('owner')
                        ->required(),

                    Forms\Components\TextInput::make('property_id')
                        ->numeric()
                        ->label('Property ID')
                        ->nullable(),

                    Forms\Components\TextInput::make('profile_image_url')
                        ->label('Profile Image URL')
                        ->maxLength(255)
                        ->nullable(),
                ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')->label('ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('full_name')->label('Full Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'owner',
                        'info' => 'manager',
                    ]),
                Tables\Columns\TextColumn::make('property_id')->label('Property')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/createx'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
