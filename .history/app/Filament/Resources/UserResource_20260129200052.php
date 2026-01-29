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
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\TextInput;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    // public static function form(Form $form): Form
    // {
    //     return $form->schema([
    //         Forms\Components\Section::make('User Information')
    //             ->columns(2)
    //             ->schema([
    //                 Forms\Components\TextInput::make('full_name')
    //                     ->label('Full Name')
    //                     ->required()
    //                     ->maxLength(255),

    //                 Forms\Components\TextInput::make('email')
    //                     ->email()
    //                     ->required()
    //                     ->maxLength(255)
    //                     ->unique(ignoreRecord: true),

    //                 Forms\Components\TextInput::make('password')
    //                     ->password()
    //                     ->maxLength(255)
    //                     ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
    //                     ->required(fn(string $operation): bool => $operation === 'create')
    //                     ->hiddenOn('edit'), // ងាយៗ: edit មិនបង្ហាញ password

    //                 Forms\Components\Select::make('role')
    //                     ->options([
    //                         'super_admin' => 'Super Admin',
    //                         'owner' => 'Owner',
    //                         'manager' => 'Manager',
    //                     ])
    //                     ->default('owner')
    //                     ->required(),

    //                 Forms\Components\Select::make('property_id')
    //                     ->label('Property')
    //                     ->relationship('property', 'name')
    //                     ->searchable()
    //                     ->preload()
    //                     ->nullable(),

    //                 Forms\Components\FileUpload::make('profile_image_url')
    //                     ->label('Profile Image')
    //                     ->image()
    //                     ->disk('public')
    //                     ->directory('users')
    //                     ->imageEditor()
    //                     ->maxSize(2048)
    //                     ->nullable(),
    //             ]),
    //     ]);
    // }


    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }
    public static function getFormSchema(): array
    {
        return [
            // Forms\Components\Section::make('User Information')
            //     ->columns(2)
            Forms\Components\Grid::make(2)
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
                        ->required()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null),

                    Forms\Components\Select::make('role')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'owner' => 'Owner',
                            'manager' => 'Manager',
                        ])
                        ->default('owner')
                        ->required(),

                    Forms\Components\Select::make('property_id')
                        ->label('Property')
                        ->relationship('property', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\FileUpload::make('profile_image_url')
                        ->label('Profile Image')
                        ->image()
                        ->disk('public')
                        ->directory('users')
                        ->imageEditor()
                        ->maxSize(2048)
                        ->nullable(),
                ]),
        ];
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

                Tables\Columns\TextColumn::make('property.name')
                    ->label('Property')
                    ->formatStateUsing(fn($state, $record) => $record->property?->name ?? '-')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('profile_image_url')
                    ->label('Photo')
                    ->disk('public')
                    ->height(40)
                    ->circular(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit User')
                    ->modalWidth('xl')
                    ->fillForm(fn(User $record): array => [
                        'full_name' => $record->full_name,
                        'email' => $record->email,
                        'role' => $record->role,
                        'property_id' => $record->property_id,
                        'profile_image_url' => $record->profile_image_url,
                    ])
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('full_name')->required()->maxLength(255),
                            Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),

                            // ✅ optional: allow change password in modal (nullable)
                            Forms\Components\TextInput::make('password')
                                ->password()
                                ->maxLength(255)
                                ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                                ->helperText('Leave blank to keep current password'),

                            Forms\Components\Select::make('role')
                                ->options([
                                    'super_admin' => 'Super Admin',
                                    'owner' => 'Owner',
                                    'manager' => 'Manager',
                                ])
                                ->required(),

                            Forms\Components\Select::make('property_id')
                                ->label('Property')
                                ->relationship('property', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable(),

                            Forms\Components\FileUpload::make('profile_image_url')
                                ->label('Profile Image')
                                ->image()
                                ->disk('public')
                                ->directory('users')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->nullable(),
                        ])
                    ])
                    ->action(function (User $record, array $data): void {
                        // ✅ if password empty, don't update it
                        if (blank($data['password'] ?? null)) {
                            unset($data['password']);
                        }

                        $record->update($data);
                    }),
            ])
            ->filters([
                // ✅ Search filter row (ដូច Maintenance)
                Filter::make('q')
                    ->form([
                        TextInput::make('q')
                            ->label('Search')
                            ->placeholder('Search ID / name / email / property...')
                            ->extraInputAttributes(['class' => 'w-full']),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['q'] ?? null;

                        return $query->when($value, function (Builder $q) use ($value) {
                            $q->where(function (Builder $qq) use ($value) {
                                $qq->where('user_id', 'like', "%{$value}%")
                                    ->orWhere('full_name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%");
                            })
                                ->orWhereHas('property', fn(Builder $p) => $p->where('name', 'like', "%{$value}%"));
                        });
                    }),

                // ✅ Role filter
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'owner'       => 'Owner',
                        'manager'     => 'Manager',
                    ])
                    ->searchable()
                    ->preload()
                    ->placeholder('All'),

                // ✅ Property filter
                SelectFilter::make('property_id')
                    ->label('Property')
                    ->options(fn() => Property::query()
                        ->orderBy('name')
                        ->pluck('name', 'property_id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->placeholder('All'),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
