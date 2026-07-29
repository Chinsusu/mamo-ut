<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Unpaid = 'unpaid';
    case PendingVerification = 'pending_verification';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'Chưa thanh toán',
            self::PendingVerification => 'Chờ đối soát',
            self::Paid => 'Đã thanh toán',
            self::Refunded => 'Đã hoàn tiền',
            self::Failed => 'Thanh toán lỗi',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::PendingVerification => 'warning',
            self::Paid => 'success',
            self::Refunded => 'info',
            self::Failed => 'danger',
        };
    }
}
