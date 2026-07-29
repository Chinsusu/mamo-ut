<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Domain\Catalog\Enums\ProductStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.product_path')
                    ->label('Ảnh')
                    ->disk((string) config('commerce.media.public_disk'))
                    ->square(),
                TextColumn::make('category.name')->label('Danh mục')->searchable(),
                TextColumn::make('name')->label('Tên')->searchable(),
                TextColumn::make('defaultVariant.sku')->label('SKU')->searchable()->placeholder('-'),
                TextColumn::make('defaultVariant.price_vnd')->label('Giá')->money('VND', locale: 'vi')->placeholder('-'),
                TextColumn::make('defaultVariant.stock_quantity')->label('Tồn')->numeric()->placeholder('-'),
                TextColumn::make('status')->label('Trạng thái')->badge(),
                IconColumn::make('is_featured')->label('Nổi bật')->boolean(),
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
                TextColumn::make('published_at')->label('Đăng bán')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->label('Danh mục')->relationship('category', 'name'),
                SelectFilter::make('status')->label('Trạng thái')->options(ProductStatus::class),
                TernaryFilter::make('is_featured')->label('Nổi bật'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
