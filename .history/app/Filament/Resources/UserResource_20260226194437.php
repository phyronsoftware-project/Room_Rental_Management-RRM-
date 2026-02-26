<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';

    /**
     * ✅ Only super_admin can see this resource in sidebar
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->role === 'super_admin';
    }

    /**
     * ✅ (Optional but recommended) Prevent non-super_admin from accessing via URL
     */
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->role === 'super_admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    // ✅ only 2 roles allowed
                    Forms\Components\Select::make('role')
                        ->label('Role')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'manager'     => 'Manager',
                        ])
                        ->searchable()
                        ->preload()
                        ->required()
                        ->rules([
                            Rule::in(['super_admin', 'manager']),
                        ]),

                    /**
                     * ✅ Password behavior:
                     * - Create: required
                     * - Edit: NOT required
                     * - If filled: hash + update
                     * - If empty on edit: keep old password
                     */
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->helperText('Edit: Leave blank to keep current password.')
                        ->required(fn(string $context): bool => $context === 'create')
                        ->dehydrated(fn($state): bool => filled($state)) // only save if filled
                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('role')->label('Role')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                // ✅ Only 2 rules/options for role filter
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'manager'     => 'Manager',
                    ])
                    ->searchable()
                    ->preload()
                    ->placeholder('All'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
