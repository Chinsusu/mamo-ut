<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case CashOnDelivery = 'cod';
    case BankTransfer = 'bank_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'COD',
            self::BankTransfer => 'Chuyển khoản ngân hàng',
        };
    }
}
