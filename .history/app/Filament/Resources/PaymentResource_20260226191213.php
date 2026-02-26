<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Payments';

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
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            // Reset room + amount when property changes
                            $set('room_id', null);
                            $set('amount', null);
                        })
                        ->required(),

                    Forms\Components\Select::make('room_id')
                        ->label('Room')
                        ->options(function (Get $get) {
                            $propertyId = $get('property_id');
                            if (blank($propertyId)) {
                                return [];
                            }

                            return Room::query()
                                ->where('property_id', $propertyId)
                                ->orderBy('room_number')
                                ->pluck('room_number', 'room_id')
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            // Auto-fill amount from selected room price
                            if (blank($state)) {
                                $set('amount', null);
                                return;
                            }

                            $propertyId = $get('property_id');

                            $price = Room::query()
                                ->where('room_id', $state)
                                ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
                                ->value('price');

                            $set('amount', $price);
                        })
                        ->disabled(function (Get $get) {
                            $propertyId = $get('property_id');
                            if (blank($propertyId)) {
                                return true;
                            }

                            return Room::query()
                                ->where('property_id', $propertyId)
                                ->doesntExist();
                        })
                        // Backend validation: room must belong to selected property
                        ->rules([
                            fn(Get $get) => Rule::exists('rooms', 'room_id')
                                ->where(fn($q) => $q->where('property_id', $get('property_id'))),
                        ])
                        ->required(),

                    Forms\Components\Select::make('tenant_id')
                        ->label('Tenant')
                        ->options(fn() => Tenant::query()
                            ->orderBy('full_name')
                            ->pluck('full_name', 'tenant_id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->prefix('$')
                        ->readOnly()          // ✅ not editable
                        ->dehydrated(true)    // ✅ still save value to DB
                        ->required(),

                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('payment_method')
                        ->options([
                            'Cash' => 'Cash',
                            'Bank Transfer' => 'Bank Transfer',
                            'ABA' => 'ABA',
                            'KHQR' => 'KHQR',
                            'Card' => 'Card',
                            'Other' => 'Other',
                        ])
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->nullable(),
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
            ->query(Payment::query())
            ->columns([
                Tables\Columns\TextColumn::make('payment_id')->label('ID')->sortable(),

                Tables\Columns\TextColumn::make('property.name')
                    ->label('Property')
                    ->formatStateUsing(fn($state, $record) => $record->property?->name ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->formatStateUsing(fn($state, $record) => $record->room?->room_number ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tenant.full_name')
                    ->label('Tenant')
                    ->formatStateUsing(fn($state, $record) => $record->tenant?->full_name ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('payment_date')->date()->sortable(),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Method')
                    ->colors([
                        'success' => 'Cash',
                        'info' => 'Bank Transfer',
                        'warning' => 'ABA',
                        'primary' => 'KHQR',
                        'gray' => 'Card',
                        'danger' => 'Other',
                    ])
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
