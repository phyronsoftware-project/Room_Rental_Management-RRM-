<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Validation\Rule;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Payments';

    // ✅ Reuse schema for modal create/edit
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
                        ->live() // ✅ important: re-render dependent fields
                        ->afterStateUpdated(function (Set $set) {
                            // ✅ reset room when property changes
                            $set('room_id', null);
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
                        ->disabled(function (Get $get) {
                            $propertyId = $get('property_id');
                            if (blank($propertyId)) return true;

                            // ✅ if property has no rooms -> disable room select
                            return Room::query()->where('property_id', $propertyId)->doesntExist();
                        })
                        // ->helperText(function (Get $get) {
                        //     $propertyId = $get('property_id');
                        //     if (blank($propertyId)) return 'Please select a property first.';

                        //     if (Room::query()->where('property_id', $propertyId)->doesntExist()) {
                        //         return 'This property has no rooms. Please create rooms first.';
                        //     }

                        //     return null;
                        // })
                        // ✅ Backend validation: room must belong to selected property
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
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->default(now())     // ✅ auto today on create
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

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // ✅ Edit modal
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Payment')
                    ->modalWidth('3xl')
                    ->form(self::getFormSchema())
                    ->fillForm(fn(Payment $record) => [
                        'property_id' => $record->property_id,
                        'room_id' => $record->room_id,
                        'tenant_id' => $record->tenant_id,
                        'amount' => $record->amount,
                        'payment_date' => $record->payment_date,
                        'payment_method' => $record->payment_method,
                        'notes' => $record->notes,
                    ])
                    ->successNotificationTitle('Updated successfully ✅')
                    ->action(fn(Payment $record, array $data) => $record->update($data)),

                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Deleted successfully ✅'),
            ])
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
            'index' => Pages\ListPayments::route('/'),
            // ✅ modal only
        ];
    }
}
