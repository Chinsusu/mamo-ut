<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Domain\Catalog\Enums\ProductStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin sản phẩm')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('category_id')
                            ->label('Danh mục')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('short_description')
                            ->label('Mô tả ngắn')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('ingredients')
                            ->label('Thành phần')
                            ->rows(3),
                        Textarea::make('usage_instruction')
                            ->label('Cách dùng')
                            ->rows(3),
                        Textarea::make('storage_instruction')
                            ->label('Bảo quản')
                            ->rows(3),
                        TextInput::make('shelf_life_text')
                            ->label('Hạn dùng')
                            ->maxLength(255),
                    ]),
                ]),
            Section::make('Xuất bản')
                ->description('Giá, SKU, trọng lượng và tồn kho được quản lý trong mục Biến thể sau khi lưu sản phẩm.')
                ->schema([
                    Grid::make(4)->schema([
                        Select::make('status')
                            ->label('Trạng thái')
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::Draft->value)
                            ->required(),
                        Toggle::make('is_featured')
                            ->label('Nổi bật')
                            ->default(false)
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        DateTimePicker::make('published_at')
                            ->label('Ngày đăng bán'),
                    ]),
                ]),
            Section::make('SEO')
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO title')
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label('SEO description')
                        ->maxLength(320)
                        ->rows(3),
                ]),
        ]);
    }
}
