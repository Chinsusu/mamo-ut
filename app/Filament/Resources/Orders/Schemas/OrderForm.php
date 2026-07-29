<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin đơn hàng')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('code')
                            ->label('Mã đơn')
                            ->required()
                            ->maxLength(32)
                            ->unique(ignoreRecord: true)
                            ->default(fn (): string => Order::nextCode()),
                        Select::make('status')
                            ->label('Trạng thái đơn')
                            ->options(OrderStatus::class)
                            ->default(OrderStatus::New->value)
                            ->required(),
                        Select::make('payment_method')
                            ->label('Phương thức thanh toán')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::CashOnDelivery->value)
                            ->required(),
                        Select::make('payment_status')
                            ->label('Trạng thái thanh toán')
                            ->options(PaymentStatus::class)
                            ->default(PaymentStatus::Unpaid->value)
                            ->required(),
                        Select::make('customer_id')
                            ->label('Khách hàng đã lưu')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('payment_reference')
                            ->label('Mã tham chiếu thanh toán')
                            ->maxLength(255),
                        DateTimePicker::make('payment_expires_at')
                            ->label('Hạn xác nhận chuyển khoản'),
                        DateTimePicker::make('paid_at')
                            ->label('Đã thanh toán lúc'),
                        DateTimePicker::make('placed_at')
                            ->label('Thời điểm đặt')
                            ->default(now())
                            ->required(),
                    ]),
                ]),
            Section::make('Thông tin người nhận')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('customer_name')
                            ->label('Họ tên')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('customer_phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('shipping_province')
                            ->label('Tỉnh/Thành')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('shipping_district')
                            ->label('Quận/Huyện')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('shipping_ward')
                            ->label('Phường/Xã')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('shipping_address_line')
                            ->label('Địa chỉ chi tiết')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                ]),
            Section::make('Tổng tiền')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('subtotal_vnd')
                            ->label('Tạm tính')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('VND'),
                        TextInput::make('shipping_fee_vnd')
                            ->label('Phí giao hàng')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('VND'),
                        Textarea::make('shipping_fee_adjustment_reason')
                            ->label('Lý do điều chỉnh phí giao')
                            ->rows(2)
                            ->helperText('Bắt buộc khi thay đổi phí giao hàng của đơn đã lưu.')
                            ->columnSpanFull(),
                        TextInput::make('discount_vnd')
                            ->label('Giảm giá')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('VND'),
                        TextInput::make('total_vnd')
                            ->label('Tổng thanh toán')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('VND'),
                        Textarea::make('customer_note')
                            ->label('Ghi chú khách hàng')
                            ->rows(3)
                            ->columnSpan(2),
                        Textarea::make('internal_note')
                            ->label('Ghi chú nội bộ')
                            ->rows(3)
                            ->columnSpan(2),
                    ]),
                ]),
        ]);
    }
}
