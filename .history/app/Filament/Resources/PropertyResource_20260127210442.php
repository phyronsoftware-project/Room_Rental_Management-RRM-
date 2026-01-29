<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Properties';
    protected static ?string $modelLabel = 'Property';
    protected static ?string $pluralModelLabel = 'Properties';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Property Info')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Property Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\FileUpload::make('image_url')
                        ->label('Property Image')
                        ->image()
                        ->directory('properties')
                        ->disk('public')
                        ->imageEditor()
                        ->maxSize(2048) // 2MB
                        ->nullable(),

                    Forms\Components\Textarea::make('address')
                        ->label('Address')
                        ->rows(4)
                        ->nullable()
                        ->columnSpanFull(),

                    // ✅ created_at exists in table but we usually don't edit it
                    Forms\Components\DateTimePicker::make('created_at')
                        ->label('Created At')
                        ->disabled()
                        ->dehydrated(false)
                        ->visibleOn('edit'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->height(40)
                    ->circular(),
            ])
            ->actions([
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
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
