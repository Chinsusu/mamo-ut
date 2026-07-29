<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã đơn')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                TextColumn::make('shipping_province')
                    ->label('Tỉnh/Thành')
                    ->searchable(),
                TextColumn::make('customer.email')
                    ->label('Hồ sơ khách')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label('Thanh toán')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('TT thanh toán')
                    ->badge()
                    ->searchable(),
                TextColumn::make('subtotal_vnd')
                    ->label('Tạm tính')
                    ->money('VND', locale: 'vi')
                    ->sortable(),
                TextColumn::make('shipping_fee_vnd')
                    ->label('Phí ship')
                    ->money('VND', locale: 'vi')
                    ->sortable(),
                TextColumn::make('discount_vnd')
                    ->label('Giảm')
                    ->money('VND', locale: 'vi')
                    ->sortable(),
                TextColumn::make('total_vnd')
                    ->label('Tổng')
                    ->money('VND', locale: 'vi')
                    ->sortable(),
                TextColumn::make('placed_at')
                    ->label('Đặt lúc')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(OrderStatus::class),
                SelectFilter::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options(PaymentMethod::class),
                SelectFilter::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options(PaymentStatus::class),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
