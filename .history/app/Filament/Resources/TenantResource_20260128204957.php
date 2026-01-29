<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use App\Models\Room;
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

    // ✅ IMPORTANT: Base query must come from parent so Filament always has model
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('room');
    }

    public static function getFormSchema(string $operation = 'create'): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('full_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->maxLength(255)->nullable(),
                Forms\Components\TextInput::make('phone_number')->label('Phone')->maxLength(50)->nullable(),
                Forms\Components\TextInput::make('age')->numeric()->nullable(),

                Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->options(fn() => Room::query()->orderBy('room_number')->pluck('room_number', 'room_id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Past' => 'Past',
                        'Evicted' => 'Evicted',
                    ])
                    ->default('Active')
                    ->required(),

                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\DatePicker::make('end_date')->nullable(),

                Forms\Components\TextInput::make('payment_term')
                    ->label('Payment Term')
                    ->placeholder('Monthly / Weekly / ...')
                    ->maxLength(50)
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                    ->helperText('Leave blank to keep current password')
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
            // ❌ IMPORTANT: DO NOT set ->query() here
            ->columns([
                Tables\Columns\TextColumn::make('tenant_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('full_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label('Phone')->toggleable(),

                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->searchable(),

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
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Tenant')
                    ->modalWidth('xl')
                    ->fillForm(fn(Tenant $record) => [
                        'full_name' => $record->full_name,
                        'email' => $record->email,
                        'phone_number' => $record->phone_number,
                        'age' => $record->age,
                        'room_id' => $record->room_id,
                        'status' => $record->status,
                        'start_date' => $record->start_date,
                        'end_date' => $record->end_date,
                        'payment_term' => $record->payment_term,
                        'password' => null,
                    ])
                    ->form(self::getFormSchema('edit'))
                    ->action(function (Tenant $record, array $data): void {
                        if (blank($data['password'] ?? null)) unset($data['password']);
                        $record->update($data);
                    }),

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
