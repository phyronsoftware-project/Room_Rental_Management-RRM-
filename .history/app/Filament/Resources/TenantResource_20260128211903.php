<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Room;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Tenants';

    // ✅ MUST: take query from parent so builder never has null model

    public static function getEloquentQuery(): Builder
    {
        // ✅ Force builder to always have model attached
        return (new Tenant())->newQuery()->with('room');
    }
    // ✅ Reuse schema for Create/Edit modal
    public static function getFormSchema(string $operation = 'create'): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Phone')
                    ->maxLength(50)
                    ->nullable(),

                Forms\Components\TextInput::make('age')
                    ->label('Age')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->options(fn() => Room::query()
                        ->orderBy('room_number')
                        ->pluck('room_number', 'room_id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Active'  => 'Active',
                        'Past'    => 'Past',
                        'Evicted' => 'Evicted',
                    ])
                    ->default('Active')
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->nullable(),

                Forms\Components\TextInput::make('payment_term')
                    ->label('Payment Term')
                    ->placeholder('Monthly / Weekly / ...')
                    ->maxLength(50)
                    ->nullable()
                    ->columnSpanFull(),

                // ✅ Password
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrated(fn($state) => filled($state)) // only save if filled
                    ->helperText($operation === 'edit'
                        ? 'Leave blank to keep current password'
                        : 'Optional (leave blank if no login)')
                    ->columnSpanFull(),
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
            // ❌ DO NOT set ->query() here
            ->columns([
                Tables\Columns\TextColumn::make('tenant_id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->toggleable(),

                // ✅ Stable relation display
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->state(fn($record) => $record->room?->room_number ?? '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'Past',
                        'danger'  => 'Evicted',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // ✅ Edit as modal (built-in)
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Tenant')
                    ->modalWidth('')
                    ->form(self::getFormSchema('edit')),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
        ];
    }
}
