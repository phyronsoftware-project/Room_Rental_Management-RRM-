<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use App\Models\Property;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Rooms';

    // ✅ reuse schema for modal
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

                // ✅ IMPORTANT: reactive status
                Forms\Components\Select::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Occupied' => 'Occupied',
                        'Maintenance' => 'Maintenance',
                    ])
                    ->default('Available')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($state !== 'Occupied') {
                            $set('tenant_full_name', null);
                            $set('tenant_email', null);
                            $set('tenant_phone_number', null);
                            $set('tenant_age', null);
                            $set('tenant_status', 'Active');
                            $set('tenant_start_date', null);
                            $set('tenant_end_date', null);
                            $set('tenant_payment_term', null);
                            $set('tenant_password', null);
                        } else {
                            // ✅ only set default if empty (don’t override user choice)
                            if (blank($get('tenant_status'))) {
                                $set('tenant_status', 'Active');
                            }
                        }
                    })
            ]),

            // ✅ Tenant section appears only when creating AND status = Occupied
            Forms\Components\Section::make('Tenant Information')
                ->description('Because you selected "Occupied", please fill the tenant information for this room.')
                ->schema([
                    Forms\Components\TextInput::make('tenant_full_name')
                        ->label('Full Name')
                        ->maxLength(255)
                        ->required(fn(Get $get, string $operation) => $operation === 'create' && $get('status') === 'Occupied')
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\TextInput::make('tenant_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->nullable()
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\TextInput::make('tenant_phone_number')
                        ->label('Phone')
                        ->maxLength(50)
                        ->nullable()
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\TextInput::make('tenant_age')
                        ->label('Age')
                        ->numeric()
                        ->nullable()
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\Select::make('tenant_status')
                        ->label('Tenant Status')
                        ->options([
                            'Active' => 'Active',
                            'Past' => 'Past',
                            'Evicted' => 'Evicted',
                        ])
                        ->default('Active')
                        ->required(fn(Get $get, string $operation) => $operation === 'create' && $get('status') === 'Occupied')
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\DatePicker::make('tenant_start_date')
                        ->label('Start-Ing Date')
                        ->required(fn(Get $get, string $operation) => $operation === 'create' && $get('status') === 'Occupied')
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\DatePicker::make('tenant_end_date')
                        ->label('End Date')
                        ->nullable()
                        ->dehydrated(fn(string $operation) => $operation === 'create'),

                    Forms\Components\Select::make('tenant_payment_term')
                        ->label('Payment Term')
                        ->options([
                            'daily'   => 'Daily',
                            'weekly'  => 'Weekly',
                            'monthly' => 'Monthly',
                            'yearly'  => 'Yearly',
                        ])
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull()
                        ->dehydrated(fn(string $operation) => $operation === 'create'),
                    Forms\Components\TextInput::make('tenant_password')
                        ->label('Password')
                        ->password()
                        ->maxLength(255)
                        ->nullable()
                        ->helperText('Optional')
                        ->columnSpanFull()
                        ->dehydrated(fn(string $operation) => $operation === 'create'),
                ])
                ->columns(2)
                ->visible(fn(Get $get, string $operation) => $operation === 'create' && $get('status') === 'Occupied'),
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

                Tables\Columns\TextColumn::make('property_id')
                    ->label('Property')
                    ->formatStateUsing(fn($state) => $state
                        ? (Property::query()->where('property_id', $state)->value('name') ?? '-')
                        : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('room_number')->sortable(),
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

            // ✅ Create Room (Modal) + auto create Tenant when Occupied
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Room')
                    ->modalHeading('Create Room')
                    ->modalWidth('3xl')
                    ->form(self::getFormSchema())
                    ->using(function (array $data) {
                        $tenantKeys = [
                            'tenant_full_name',
                            'tenant_email',
                            'tenant_phone_number',
                            'tenant_age',
                            'tenant_status',
                            'tenant_start_date',
                            'tenant_end_date',
                            'tenant_payment_term',
                            'tenant_password',
                        ];

                        $tenantData = Arr::only($data, $tenantKeys);
                        $roomData = Arr::except($data, $tenantKeys);

                        if (($roomData['status'] ?? null) === 'Occupied') {
                            if (blank($tenantData['tenant_full_name'] ?? null) || blank($tenantData['tenant_start_date'] ?? null)) {
                                throw ValidationException::withMessages([
                                    'status' => 'You selected "Occupied". Please fill Tenant Information before saving.',
                                ]);
                            }
                        }

                        return DB::transaction(function () use ($roomData, $tenantData) {
                            /** @var \App\Models\Room $room */
                            $room = Room::create($roomData);

                            if (($roomData['status'] ?? null) === 'Occupied') {
                                $payload = [
                                    'room_id' => $room->room_id ?? $room->getKey(),
                                    'full_name' => $tenantData['tenant_full_name'],
                                    'email' => $tenantData['tenant_email'] ?? null,
                                    'phone_number' => $tenantData['tenant_phone_number'] ?? null,
                                    'age' => $tenantData['tenant_age'] ?? null,
                                    'status' => $tenantData['tenant_status'] ?? 'Active',
                                    'start_date' => $tenantData['tenant_start_date'],
                                    'end_date' => $tenantData['tenant_end_date'] ?? null,
                                    'payment_term' => $tenantData['tenant_payment_term'] ?? null,
                                ];

                                if (filled($tenantData['tenant_password'] ?? null)) {
                                    $payload['password'] = Hash::make($tenantData['tenant_password']);
                                }

                                Tenant::create($payload);
                            }

                            return $room;
                        });
                    })
                    ->successNotificationTitle('Room created successfully ✅'),
            ])

            ->filters([
                Filter::make('q')
                    ->form([
                        TextInput::make('q')
                            ->label('Search')
                            ->placeholder('Search ID / room number / floor / property...')
                            ->extraInputAttributes(['class' => 'w-full']),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['q'] ?? null;

                        return $query->when($value, function (Builder $q) use ($value) {
                            $q->where(function (Builder $qq) use ($value) {
                                $qq->where('room_id', 'like', "%{$value}%")
                                    ->orWhere('room_number', 'like', "%{$value}%")
                                    ->orWhere('floor', 'like', "%{$value}%");
                            })
                                ->orWhereHas('property', fn(Builder $p) => $p->where('name', 'like', "%{$value}%"));
                        });
                    }),

                SelectFilter::make('property_id')
                    ->label('Property')
                    ->options(fn() => Property::query()
                        ->orderBy('name')
                        ->pluck('name', 'property_id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->placeholder('All'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Available'   => 'Available',
                        'Occupied'    => 'Occupied',
                        'Maintenance' => 'Maintenance',
                    ])
                    ->searchable()
                    ->preload()
                    ->placeholder('All'),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
        ];
    }
}
