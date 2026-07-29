<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $modelLabel = 'sản phẩm trong đơn';

    protected static ?string $pluralModelLabel = 'Sản phẩm trong đơn';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                Select::make('product_id')
                    ->label('Sản phẩm liên kết')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('product_variant_id')
                    ->label('Biến thể liên kết')
                    ->relationship('productVariant', 'sku')
                    ->searchable()
                    ->preload(),
                TextInput::make('product_name')
                    ->label('Tên sản phẩm snapshot')
                    ->required()
                    ->maxLength(255),
                TextInput::make('variant_label')
                    ->label('Biến thể snapshot')
                    ->maxLength(255),
                TextInput::make('product_sku')
                    ->label('SKU snapshot')
                    ->required()
                    ->maxLength(255),
                TextInput::make('quantity')
                    ->label('Số lượng')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                TextInput::make('unit_price_vnd')
                    ->label('Đơn giá')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix('VND'),
                TextInput::make('line_total_vnd')
                    ->label('Thành tiền')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix('VND'),
            ]),
        ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('product_name')->label('Tên snapshot'),
            TextEntry::make('variant_label')->label('Biến thể')->placeholder('-'),
            TextEntry::make('product_sku')->label('SKU'),
            TextEntry::make('quantity')->label('Số lượng')->numeric(),
            TextEntry::make('unit_price_vnd')->label('Đơn giá')->money('VND', locale: 'vi'),
            TextEntry::make('line_total_vnd')->label('Thành tiền')->money('VND', locale: 'vi'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')->label('Tên sản phẩm')->searchable(),
                TextColumn::make('variant_label')->label('Biến thể')->placeholder('-'),
                TextColumn::make('product_sku')->label('SKU')->searchable(),
                TextColumn::make('quantity')->label('SL')->numeric()->sortable(),
                TextColumn::make('unit_price_vnd')->label('Đơn giá')->money('VND', locale: 'vi')->sortable(),
                TextColumn::make('line_total_vnd')->label('Thành tiền')->money('VND', locale: 'vi')->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
