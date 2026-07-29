<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin danh mục')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Tên danh mục')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('seo_title')
                            ->label('SEO title')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->label('SEO description')
                            ->maxLength(320)
                            ->rows(3),
                        Toggle::make('is_active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
                ]),
        ]);
    }
}
