<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Hidden = 'hidden';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Bản nháp',
            self::Active => 'Đang bán',
            self::Hidden => 'Đang ẩn',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'success',
            self::Hidden => 'warning',
        };
    }
}
