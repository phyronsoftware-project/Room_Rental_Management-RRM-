<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Properties';
    protected static ?string $modelLabel = 'Property';
    protected static ?string $pluralModelLabel = 'Properties';
    protected static ?int $navigationSort = 1;

    // public static function form(Form $form): Form
    // {
    //     return $form->schema([
    //         Forms\Components\Section::make('Property Info')
    //             ->columns(2)
    //             ->schema([
    //                 Forms\Components\TextInput::make('name')
    //                     ->label('Property Name')
    //                     ->required()
    //                     ->maxLength(255)
    //                     ->columnSpan(1),

    //                 Forms\Components\FileUpload::make('image_url')
    //                     ->label('Property Image')
    //                     ->image()
    //                     ->directory('properties')
    //                     ->disk('public')
    //                     ->imageEditor()
    //                     ->maxSize(2048) // 2MB
    //                     ->nullable(),

    //                 Forms\Components\Textarea::make('address')
    //                     ->label('Address')
    //                     ->rows(4)
    //                     ->nullable()
    //                     ->columnSpanFull(),

    //                 // ✅ created_at exists in table but we usually don't edit it
    //                 Forms\Components\DateTimePicker::make('created_at')
    //                     ->label('Created At')
    //                     ->disabled()
    //                     ->dehydrated(false)
    //                     ->visibleOn('edit'),
    //             ]),
    //     ]);
    // }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Property Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('image_url')
                    ->label('Property Image')
                    ->image()
                    ->disk('public')
                    ->directory('properties')
                    ->imageEditor()
                    ->maxSize(2048)
                    ->nullable(),

                Forms\Components\Textarea::make('address')
                    ->label('Address')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull(),
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
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->height(40)
                    ->circular(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Tables\Columns\TextColumn::make('updated_at')
                //     ->label('Updated')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),



            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Property')
                    ->modalWidth('3xl')
                    ->fillForm(fn(Property $record) => [
                        'name' => $record->name,
                        'address' => $record->address,
                        'image_url' => $record->image_url,
                    ])
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Property Name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\FileUpload::make('image_url')
                                ->label('Property Image')
                                ->image()
                                ->disk('public')
                                ->directory('properties')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->nullable(),

                            Forms\Components\Textarea::make('address')
                                ->label('Address')
                                ->rows(4)
                                ->nullable()
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->action(fn(Property $record, array $data) => $record->update($data)),

                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProperties::route('/'),
            // 'create' => Pages\CreateProperty::route('/create'),
            // 'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
