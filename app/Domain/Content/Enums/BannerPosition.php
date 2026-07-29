<?php

declare(strict_types=1);

namespace App\Domain\Content\Enums;

use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasLabel
{
    case HomeHero = 'home_hero';
    case HomePromo = 'home_promo';
    case ProductListing = 'product_listing';

    public function getLabel(): string
    {
        return match ($this) {
            self::HomeHero => 'Hero trang chủ',
            self::HomePromo => 'Khuyến mại trang chủ',
            self::ProductListing => 'Danh sách sản phẩm',
        };
    }
}
