<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Packed = 'packed';
    case Shipping = 'shipping';
    case Delivered = 'delivered';
    case DeliveryFailed = 'delivery_failed';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Đơn mới',
            self::Confirmed => 'Đã xác nhận',
            self::Preparing => 'Đang chuẩn bị',
            self::Packed => 'Đã đóng gói',
            self::Shipping => 'Đang giao',
            self::Delivered => 'Đã giao',
            self::DeliveryFailed => 'Giao thất bại',
            self::Cancelled => 'Đã hủy',
            self::Returned => 'Đã trả hàng',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Confirmed => 'info',
            self::Preparing, self::Packed => 'warning',
            self::Shipping => 'primary',
            self::Delivered => 'success',
            self::DeliveryFailed, self::Cancelled => 'danger',
            self::Returned => 'gray',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Preparing, self::Cancelled],
            self::Preparing => [self::Packed, self::Cancelled],
            self::Packed => [self::Shipping, self::Cancelled],
            self::Shipping => [self::Delivered, self::DeliveryFailed],
            self::DeliveryFailed => [self::Shipping, self::Returned],
            self::Delivered => [self::Returned],
            self::Cancelled, self::Returned => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
