<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShopSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShopSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cấu hình')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('group')
                                ->label('Nhóm')
                                ->required()
                                ->maxLength(255)
                                ->default('general'),
                            TextInput::make('key')
                                ->label('Khóa')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('label')
                                ->label('Nhãn hiển thị')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('type')
                                ->label('Kiểu dữ liệu')
                                ->required()
                                ->maxLength(255)
                                ->default('string'),
                            Textarea::make('value')
                                ->label('Giá trị')
                                ->rows(5)
                                ->columnSpanFull(),
                            Toggle::make('is_public')
                                ->label('Cho phép hiển thị công khai')
                                ->default(false)
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
