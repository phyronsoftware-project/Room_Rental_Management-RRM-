<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTenantsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Tenants';

    // ✅ if your dashboard is 2 columns, this makes table full width
    protected int|string|array $columnSpan = '';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()->latest() // ✅ recent first
            )
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tenant')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->label('')
                    ->tooltip('Actions'),
            ]);
    }
}
