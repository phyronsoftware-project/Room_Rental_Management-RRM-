<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\BillingSetting;
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
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;


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
        $propertyId = null;

        if (! blank($roomId)) {
            $rent = (float) (Room::query()->where('room_id', $roomId)->value('price') ?? 0);
            $propertyId = Room::query()->where('room_id', $roomId)->value('property_id');
        }

        // Rates: property-specific first, fallback to global (property_id = null)
        $setting = BillingSetting::query()
            ->where('property_id', $propertyId)
            ->first()
            ?? BillingSetting::query()->whereNull('property_id')->first();

        $waterRate = (float) ($setting?->water_unit_price ?? 0);
        $electricityRate = (float) ($setting?->electricity_unit_price ?? 0);
        $carRate = (float) ($setting?->car_parking_price ?? 0);
        $motorbikeRate = (float) ($setting?->motorbike_parking_price ?? 0);

        // Usage / counts
        $waterM3 = (float) ($get('water_m3') ?? 0);
        $electricityKwh = (float) ($get('electricity_kwh') ?? 0);
        $carCount = (int) ($get('car_count') ?? 0);
        $motorbikeCount = (int) ($get('motorbike_count') ?? 0);

        // Calculated fees
        $waterFee = $waterM3 * $waterRate;
        $electricityFee = $electricityKwh * $electricityRate;
        $parkingFee = ($carCount * $carRate) + ($motorbikeCount * $motorbikeRate);

        $set('water_fee', $waterFee);
        $set('electricity_fee', $electricityFee);
        $set('parking_fee', $parkingFee);

        $set('amount', $rent + $waterFee + $electricityFee + $parkingFee);
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

                    $set('water_m3', 0);
                    $set('electricity_kwh', 0);
                    $set('car_count', 0);
                    $set('motorbike_count', 0);

                    $set('water_fee', 0);
                    $set('electricity_fee', 0);
                    $set('parking_fee', 0);

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



                // ✅ Auto-fill car/motorbike counts from tenant (read-only in payment form)
                $cars = (int) (Tenant::query()->where('tenant_id', $state)->value('car_count') ?? 0);
                $motos = (int) (Tenant::query()->where('tenant_id', $state)->value('motorbike_count') ?? 0);
                $set('car_count', $cars);
                $set('motorbike_count', $motos);
                // Defaults (if empty)
                if (blank($get('water_m3'))) {
                    $set('water_m3', 0);
                }
                if (blank($get('electricity_kwh'))) {
                    $set('electricity_kwh', 0);
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
        $waterM3Input = Forms\Components\TextInput::make('water_m3')
            ->label('Water (m³)')
            ->numeric()
            ->default(0)
            ->suffix('m³')
            ->afterStateUpdated(function (Set $set, Get $get) {
                self::recalcAmount($set, $get);
            });

        if (method_exists($waterM3Input, 'live')) {
            $waterM3Input->live();
        } elseif (method_exists($waterM3Input, 'reactive')) {
            $waterM3Input->reactive();
        }

        $electricityKwhInput = Forms\Components\TextInput::make('electricity_kwh')
            ->label('Electricity (kWh)')
            ->numeric()
            ->default(0)
            ->suffix('kWh')
            ->afterStateUpdated(function (Set $set, Get $get) {
                self::recalcAmount($set, $get);
            });

        if (method_exists($electricityKwhInput, 'live')) {
            $electricityKwhInput->live();
        } elseif (method_exists($electricityKwhInput, 'reactive')) {
            $electricityKwhInput->reactive();
        }

        $carCountInput = Forms\Components\TextInput::make('car_count')
            ->label('Cars')
            ->numeric()
            ->default(0)
            ->disabled()          // auto-filled from Tenant
            ->dehydrated(true)    // still save
            ->afterStateUpdated(function (Set $set, Get $get) {
                self::recalcAmount($set, $get);
            });

        if (method_exists($carCountInput, 'live')) {
            $carCountInput->live();
        } elseif (method_exists($carCountInput, 'reactive')) {
            $carCountInput->reactive();
        }

        $motorbikeCountInput = Forms\Components\TextInput::make('motorbike_count')
            ->label('Motorbikes')
            ->numeric()
            ->default(0)
            ->disabled()          // auto-filled from Tenant
            ->dehydrated(true)    // still save
            ->afterStateUpdated(function (Set $set, Get $get) {
                self::recalcAmount($set, $get);
            });

        if (method_exists($motorbikeCountInput, 'live')) {
            $motorbikeCountInput->live();
        } elseif (method_exists($motorbikeCountInput, 'reactive')) {
            $motorbikeCountInput->reactive();
        }

        // Calculated fees (read-only)
        $waterFeeField = Forms\Components\TextInput::make('water_fee')
            ->label('Water Fee')
            ->numeric()
            ->prefix('$')
            ->disabled()
            ->dehydrated(true);

        $electricityFeeField = Forms\Components\TextInput::make('electricity_fee')
            ->label('Electricity Fee')
            ->numeric()
            ->prefix('$')
            ->disabled()
            ->dehydrated(true);

        $parkingFeeField = Forms\Components\TextInput::make('parking_fee')
            ->label('Parking Fee')
            ->numeric()
            ->prefix('$')
            ->disabled()
            ->dehydrated(true);


        return [
            Forms\Components\Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    $tenantSelect,

                    $propertySelect,
                    $roomSelect,

                    $waterM3Input,
                    $electricityKwhInput,
                    $carCountInput,
                    $motorbikeCountInput,

                    $waterFeeField,
                    $electricityFeeField,
                    $parkingFeeField,

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


                Tables\Columns\TextColumn::make('water_m3')
                    ->label('Water (m³)')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('electricity_kwh')
                    ->label('Electricity (kWh)')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('car_count')
                    ->label('Cars')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('motorbike_count')
                    ->label('Motorbikes')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('water_fee')
                    ->label('Water Fee')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('electricity_fee')
                    ->label('Electricity Fee')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('parking_fee')
                    ->label('Parking Fee')
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
            ->filters([
                Filter::make('payment_month')
                    ->label('Month')
                    ->form([
                        Grid::make(2)->schema([
                            Select::make('month')
                                ->label('Month')
                                ->options([
                                    '01' => 'January',
                                    '02' => 'February',
                                    '03' => 'March',
                                    '04' => 'April',
                                    '05' => 'May',
                                    '06' => 'June',
                                    '07' => 'July',
                                    '08' => 'August',
                                    '09' => 'September',
                                    '10' => 'October',
                                    '11' => 'November',
                                    '12' => 'December',
                                ])
                                ->default(now()->format('m'))
                                ->required(),

                            Select::make('year')
                                ->label('Year')
                                ->options(collect(range(now()->year - 5, now()->year + 1))
                                    ->mapWithKeys(fn($y) => [(string) $y => (string) $y])
                                    ->toArray())
                                ->default((string) now()->year)
                                ->required(),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $month = $data['month'] ?? null;
                        $year  = $data['year'] ?? null;

                        if (! $month || ! $year) return $query;

                        return $query
                            ->whereYear('payment_date', (int) $year)
                            ->whereMonth('payment_date', (int) $month);
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
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
