<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('code')->label('Mã đơn'),
            TextEntry::make('customer.name')->label('Hồ sơ khách')->placeholder('Guest'),
            TextEntry::make('customer_name')->label('Người nhận'),
            TextEntry::make('customer_email')->label('Email')->placeholder('-'),
            TextEntry::make('customer_phone')->label('Số điện thoại'),
            TextEntry::make('shipping_address_line')->label('Địa chỉ chi tiết'),
            TextEntry::make('shipping_ward')->label('Phường/Xã'),
            TextEntry::make('shipping_district')->label('Quận/Huyện'),
            TextEntry::make('shipping_province')->label('Tỉnh/Thành'),
            TextEntry::make('customer_note')->label('Ghi chú khách')->placeholder('-')->columnSpanFull(),
            TextEntry::make('internal_note')->label('Ghi chú nội bộ')->placeholder('-')->columnSpanFull(),
            TextEntry::make('status')->label('Trạng thái')->badge(),
            TextEntry::make('payment_method')->label('Thanh toán')->badge(),
            TextEntry::make('payment_status')->label('TT thanh toán')->badge(),
            TextEntry::make('payment_reference')->label('Mã tham chiếu')->placeholder('-'),
            TextEntry::make('subtotal_vnd')->label('Tạm tính')->money('VND', locale: 'vi'),
            TextEntry::make('shipping_fee_vnd')->label('Phí giao')->money('VND', locale: 'vi'),
            TextEntry::make('discount_vnd')->label('Giảm giá')->money('VND', locale: 'vi'),
            TextEntry::make('total_vnd')->label('Tổng')->money('VND', locale: 'vi'),
            TextEntry::make('payment_expires_at')->label('Hạn chuyển khoản')->dateTime()->placeholder('-'),
            TextEntry::make('paid_at')->label('Thanh toán lúc')->dateTime()->placeholder('-'),
            TextEntry::make('inventory_released_at')->label('Hoàn kho lúc')->dateTime()->placeholder('-'),
            TextEntry::make('placed_at')->label('Đặt lúc')->dateTime(),
        ]);
    }
}
