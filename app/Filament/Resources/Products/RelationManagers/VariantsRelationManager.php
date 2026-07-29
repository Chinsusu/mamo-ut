<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $modelLabel = 'biến thể';

    protected static ?string $pluralModelLabel = 'Biến thể';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('label')
                    ->label('Nhãn biến thể')
                    ->required()
                    ->maxLength(255),
                TextInput::make('weight_gram')
                    ->label('Khối lượng')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('gram'),
                TextInput::make('price_vnd')
                    ->label('Giá bán')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix('VND'),
                TextInput::make('compare_at_price_vnd')
                    ->label('Giá gạch')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('VND'),
                TextInput::make('stock_quantity')
                    ->label('Tồn kho')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('sort_order')
                    ->label('Thứ tự')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Đang bán')
                    ->default(true)
                    ->required(),
                Toggle::make('is_default')
                    ->label('Biến thể mặc định')
                    ->default(false)
                    ->required(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('label')->label('Biến thể'),
                TextColumn::make('price_vnd')->label('Giá')->money('VND', locale: 'vi'),
                TextColumn::make('stock_quantity')->label('Tồn')->numeric(),
                TextColumn::make('weight_gram')->label('Gram')->numeric()->placeholder('-'),
                IconColumn::make('is_default')->label('Mặc định')->boolean(),
                IconColumn::make('is_active')->label('Đang bán')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
