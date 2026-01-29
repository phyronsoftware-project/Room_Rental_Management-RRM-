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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Tenants';

    // ✅ reuse schema for modal (similar RoomResource)
    public static function getFormSchema(string $operation = 'create'): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->options(fn() => Room::query()
                        ->orderBy('room_number')
                        ->pluck('room_number', 'room_id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Phone')
                    ->maxLength(50)
                    ->nullable(),

                Forms\Components\TextInput::make('age')
                    ->numeric()
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Past' => 'Past',
                        'Evicted' => 'Evicted',
                    ])
                    ->default('Active')
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->nullable(),

                Forms\Components\TextInput::make('payment_term')
                    ->label('Payment Term')
                    ->placeholder('Monthly / Weekly / ...')
                    ->maxLength(50)
                    ->nullable()
                    ->columnSpanFull(),

                // ✅ password optional
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrated(fn($state) => filled($state)) // save only if filled
                    ->helperText($operation === 'edit'
                        ? 'Leave blank to keep current password'
                        : 'Optional')
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
            // ✅ SAME STYLE AS ROOM: set query here
            ->query(Tenant::query()->with('room'))
            ->columns([
                Tables\Columns\TextColumn::make('tenant_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('full_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label('Phone')->toggleable(),

                // ✅ show room number
                Tables\Columns\TextColumn::make('room_id')
                    ->label('Room')
                    ->formatStateUsing(fn($state) => $state
                        ? (Room::query()->where('room_id', $state)->value('room_number') ?? '-')
                        : '-')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
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
                // ✅ EDIT MODAL (same style RoomResource)
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Tenant')
                    ->modalWidth('3xl')
                    ->fillForm(fn(Tenant $record) => [
                        'room_id' => $record->room_id,
                        'full_name' => $record->full_name,
                        'email' => $record->email,
                        'phone_number' => $record->phone_number,
                        'age' => $record->age,
                        'status' => $record->status,
                        'start_date' => $record->start_date,
                        'end_date' => $record->end_date,
                        'payment_term' => $record->payment_term,
                        'password' => null,
                    ])
                    ->form(self::getFormSchema('edit'))
                    ->successNotificationTitle('Updated successfully ✅')
                    ->action(function (Tenant $record, array $data): void {
                        // ✅ keep old password if blank
                        if (blank($data['password'] ?? null)) {
                            unset($data['password']);
                        }
                        $record->update($data);
                    }),

                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Deleted successfully ✅'),
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
