<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingSettingResource\Pages;
use App\Models\BillingSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BillingSettingResource extends Resource
{
    protected static ?string $model = BillingSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $navigationGroup = 'System';

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()?->role === 'super_admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess(); // hide menu for non-super_admin
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit($record): bool
    {
        return static::canAccess();
    }

    public static function canDelete($record): bool
    {
        return static::canAccess();
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Billing Prices')
                ->schema([
                    Forms\Components\TextInput::make('water_unit_price')
                        ->label('Water price (per m³)')
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\TextInput::make('electricity_unit_price')
                        ->label('Electricity price (per kWh)')
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\TextInput::make('car_parking_price')
                        ->label('Car parking (per month)')
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\TextInput::make('motorbike_parking_price')
                        ->label('Motorbike parking (per month)')
                        ->numeric()
                        ->prefix('$')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('water_unit_price')->label('Water')->money('USD'),
                Tables\Columns\TextColumn::make('electricity_unit_price')->label('Electricity')->money('USD'),
                Tables\Columns\TextColumn::make('car_parking_price')->label('Car')->money('USD'),
                Tables\Columns\TextColumn::make('motorbike_parking_price')->label('Motorbike')->money('USD'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Updated'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                // ❌ no create (one row only)
            ])
            ->bulkActions([
                // ❌ no delete
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillingSettings::route('/'),
            'edit'  => Pages\EditBillingSetting::route('/{record}/edit'),
        ];
    }
}
