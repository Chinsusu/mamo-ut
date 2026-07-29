<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin khách hàng')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('user_id')
                                ->label('Tài khoản liên kết')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload(),
                            TextInput::make('name')
                                ->label('Họ tên')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Số điện thoại')
                                ->tel()
                                ->maxLength(255),
                        ]),
                    ]),
                Section::make('Địa chỉ mặc định')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('address_line')
                                ->label('Địa chỉ')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            TextInput::make('ward')
                                ->label('Phường/Xã')
                                ->maxLength(255),
                            TextInput::make('district')
                                ->label('Quận/Huyện')
                                ->maxLength(255),
                            TextInput::make('province')
                                ->label('Tỉnh/Thành')
                                ->maxLength(255),
                            Textarea::make('notes')
                                ->label('Ghi chú')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
