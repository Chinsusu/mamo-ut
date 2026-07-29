<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            ImageEntry::make('primaryImage.product_path')
                ->label('Ảnh chính')
                ->disk((string) config('commerce.media.public_disk'))
                ->placeholder('-'),
            TextEntry::make('category.name')->label('Danh mục')->placeholder('-'),
            TextEntry::make('name')->label('Tên'),
            TextEntry::make('slug')->label('Slug'),
            TextEntry::make('short_description')->label('Mô tả ngắn')->placeholder('-'),
            TextEntry::make('description')->label('Mô tả')->placeholder('-')->columnSpanFull(),
            TextEntry::make('ingredients')->label('Thành phần')->placeholder('-'),
            TextEntry::make('usage_instruction')->label('Cách dùng')->placeholder('-'),
            TextEntry::make('storage_instruction')->label('Bảo quản')->placeholder('-'),
            TextEntry::make('shelf_life_text')->label('Hạn dùng')->placeholder('-'),
            TextEntry::make('defaultVariant.sku')->label('SKU mặc định')->placeholder('-'),
            TextEntry::make('defaultVariant.price_vnd')->label('Giá')->money('VND', locale: 'vi')->placeholder('-'),
            TextEntry::make('defaultVariant.stock_quantity')->label('Tồn')->numeric()->placeholder('-'),
            TextEntry::make('status')->label('Trạng thái')->badge(),
            IconEntry::make('is_featured')->label('Nổi bật')->boolean(),
            TextEntry::make('sort_order')->label('Thứ tự')->numeric(),
            TextEntry::make('published_at')->label('Ngày đăng')->dateTime()->placeholder('-'),
            TextEntry::make('seo_title')->label('SEO title')->placeholder('-'),
            TextEntry::make('seo_description')->label('SEO description')->placeholder('-'),
        ]);
    }
}
