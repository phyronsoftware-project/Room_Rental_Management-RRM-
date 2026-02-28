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
use App\Filament\Exports\PaymentExporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;


class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Payments';

    /**
     * Recalculate total amount = room price + water_fee + electricity_fee
     */
    protected static function recalcAmount(Set $set, Get $get): void
    {
        $roomId = $get('room_id');

        $rent = 0;
        if (! blank($roomId)) {
            $rent = (float) (Room::query()->where('room_id', $roomId)->value('price') ?? 0);
        }

        $water = (float) ($get('water_fee') ?? 0);
        $electricity = (float) ($get('electricity_fee') ?? 0);

        $set('amount', $rent + $water + $electricity);
    }

    /**
     * Reuse one schema for Create (modal) + Edit (modal)
     */
    public static function getFormSchema(): array
    {
        // ✅ Tenant first (required). Selecting tenant auto-fills Property + Room + Amount
        $tenantSelect = Forms\Components\Select::make('tenant_id')
            ->label('Tenant')
            ->options(fn() => Tenant::query()
                ->orderBy('full_name')
                ->pluck('full_name', 'tenant_id')
                ->toArray())
            ->searchable()
            ->preload()
            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                if (blank($state)) {
                    $set('property_id', null);
                    $set('room_id', null);
                    $set('water_fee', 0);
                    $set('electricity_fee', 0);
                    $set('amount', null);
                    return;
                }

                // Assumption: tenants table has room_id
                $roomId = Tenant::query()
                    ->where('tenant_id', $state)
                    ->value('room_id');

                if (blank($roomId)) {
                    // Tenant not assigned to a room yet
                    $set('property_id', null);
                    $set('room_id', null);
                    $set('amount', null);
                    return;
                }

                $propertyId = Room::query()
                    ->where('room_id', $roomId)
                    ->value('property_id');

                $set('property_id', $propertyId);
                $set('room_id', $roomId);

                // Defaults (if empty)
                if (blank($get('water_fee'))) {
                    $set('water_fee', 0);
                }
                if (blank($get('electricity_fee'))) {
                    $set('electricity_fee', 0);
                }

                self::recalcAmount($set, $get);
            })
            ->required();

        if (method_exists($tenantSelect, 'live')) {
            $tenantSelect->live();
        } elseif (method_exists($tenantSelect, 'reactive')) {
            $tenantSelect->reactive();
        }

        // Property (auto-filled from tenant)
        $propertySelect = Forms\Components\Select::make('property_id')
            ->label('Property')
            ->options(fn() => Property::query()
                ->orderBy('name')
                ->pluck('name', 'property_id')
                ->toArray())
            ->searchable()
            ->preload()
            ->disabled()              // enforce: choose tenant first, and property comes from tenant
            ->dehydrated(true)        // still save even if disabled
            ->required();

        // Room (auto-filled from tenant)
        $roomSelect = Forms\Components\Select::make('room_id')
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
            ->disabled()              // enforce: room comes from tenant
            ->dehydrated(true)        // still save even if disabled
            // Backend validation: room must belong to selected property
            ->rules([
                fn(Get $get) => Rule::exists('rooms', 'room_id')
                    ->where(fn($q) => $q->where('property_id', $get('property_id'))),
            ])
            ->required();

        // Water fee
        $waterInput = Forms\Components\TextInput::make('water_fee')
            ->label('Water / month')
            ->numeric()
            ->prefix('$')
            ->default(0)
            ->afterStateUpdated(function (Set $set, Get $get) {
                self::recalcAmount($set, $get);
            });

        if (method_exists($waterInput, 'live')) {
            $waterInput->live();
        } elseif (method_exists($waterInput, 'reactive')) {
            $waterInput->reactive();
        }

        // Electricity fee
        $electricityInput = Forms\Components\TextInput::make('electricity_fee')
            ->label('Electricity / month')
            ->numeric()
            ->prefix('$')
            ->default(0)
            ->afterStateUpdated(function (Set $set, Get $get) {
                self::recalcAmount($set, $get);
            });

        if (method_exists($electricityInput, 'live')) {
            $electricityInput->live();
        } elseif (method_exists($electricityInput, 'reactive')) {
            $electricityInput->reactive();
        }

        return [
            Forms\Components\Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    $tenantSelect,

                    $propertySelect,
                    $roomSelect,

                    $waterInput,
                    $electricityInput,

                    Forms\Components\TextInput::make('amount')
                        ->label('Total Amount')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated(true)
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
            // ->headerActions([
            // ExportAction::make()
            //     ->exporter(PaymentExporter::class)
            //     ->formats([ExportFormat::Xlsx]),
            // ])
            ->columns([
                Tables\Columns\TextColumn::make('payment_id')
                    ->label('ID')
                    ->sortable(),

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


                Tables\Columns\TextColumn::make('water_fee')
                    ->label('Water')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('electricity_fee')
                    ->label('Electricity')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Method')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalWidth('3xl'),

                    // ✅ Edit in MODAL (uses same schema)
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Edit Payment')
                        ->modalWidth('3xl')
                        ->form(fn() => self::getFormSchema()),

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
            // ✅ only list page (create/edit are modals)
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
