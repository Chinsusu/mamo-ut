<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nội dung bài viết')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Tiêu đề')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('excerpt')
                                ->label('Tóm tắt')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Textarea::make('body')
                                ->label('Nội dung')
                                ->rows(10)
                                ->columnSpanFull(),
                            FileUpload::make('image_path')
                                ->label('Ảnh đại diện')
                                ->image()
                                ->disk((string) config('commerce.media.public_disk'))
                                ->directory('posts')
                                ->visibility('public')
                                ->columnSpanFull(),
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
                    ]),                Section::make('Xuất bản')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_published')
                                ->label('Đã xuất bản')
                                ->default(false)
                                ->required(),
                            DateTimePicker::make('published_at')
                                ->label('Ngày xuất bản'),
                        ]),
                    ]),
            ]);
    }
}
