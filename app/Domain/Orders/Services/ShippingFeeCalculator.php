<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Models\ShopSetting;
use Illuminate\Support\Str;

final class ShippingFeeCalculator
{
    public function calculate(int $subtotalVnd, string $province): int
    {
        /** @var array<string, string|null> $settings */
        $settings = ShopSetting::query()
            ->whereIn('key', [
                'shipping_fee_hue_vnd',
                'shipping_fee_other_vnd',
                'free_shipping_threshold_vnd',
            ])
            ->pluck('value', 'key');

        $freeThreshold = max(
            0,
            (int) ($settings['free_shipping_threshold_vnd']
                ?? config('commerce.shipping.free_threshold_vnd')),
        );

        if ($freeThreshold > 0 && $subtotalVnd >= $freeThreshold) {
            return 0;
        }

        $normalizedProvince = Str::lower(Str::ascii($province));
        $isHue = Str::contains($normalizedProvince, 'hue');
        $settingKey = $isHue ? 'shipping_fee_hue_vnd' : 'shipping_fee_other_vnd';
        $fallbackKey = $isHue ? 'commerce.shipping.hue_fee_vnd' : 'commerce.shipping.other_fee_vnd';

        return max(0, (int) ($settings[$settingKey] ?? config($fallbackKey)));
    }
}
