<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Tên'),
            TextEntry::make('slug')->label('Slug'),
            TextEntry::make('description')->label('Mô tả')->placeholder('-')->columnSpanFull(),
            TextEntry::make('seo_title')->label('SEO title')->placeholder('-'),
            TextEntry::make('seo_description')->label('SEO description')->placeholder('-'),
            IconEntry::make('is_active')->label('Đang hiển thị')->boolean(),
            TextEntry::make('sort_order')->label('Thứ tự')->numeric(),
            TextEntry::make('updated_at')->label('Cập nhật')->dateTime()->placeholder('-'),
        ]);
    }
}
