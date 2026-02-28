<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingSettingResource\Pages;
use App\Filament\Resources\BillingSettingResource\RelationManagers;
use App\Models\BillingSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillingSettingResource extends Resource
{
    protected static ?string $model = BillingSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillingSettings::route('/'),
            'create' => Pages\CreateBillingSetting::route('/create'),
            'edit' => Pages\EditBillingSetting::route('/{record}/edit'),
        ];
    }
}
