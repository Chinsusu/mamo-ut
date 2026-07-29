<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Schemas;

use App\Domain\Content\Enums\BannerPosition;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nội dung banner')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Tiêu đề')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('subtitle')
                                ->label('Mô tả ngắn')
                                ->maxLength(255),
                            FileUpload::make('image_path')
                                ->label('Ảnh banner')
                                ->image()
                                ->disk((string) config('commerce.media.public_disk'))
                                ->directory('banners')
                                ->visibility('public')
                                ->columnSpanFull(),
                            TextInput::make('link_url')
                                ->label('Đường dẫn')
                                ->rule('starts_with:/,https://')
                                ->maxLength(255),
                        ]),
                    ]),
                Section::make('Lịch hiển thị')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('position')
                                ->label('Vị trí')
                                ->options(BannerPosition::class)
                                ->default('home_hero')
                                ->required(),
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
                            DateTimePicker::make('starts_at')
                                ->label('Bắt đầu'),
                            DateTimePicker::make('ends_at')
                                ->label('Kết thúc'),
                        ]),
                    ]),
            ]);
    }
}
