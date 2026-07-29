<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShopSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopSetting>
 */
class ShopSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => 'setting_'.fake()->unique()->slug(2),
            'label' => fake('vi_VN')->words(3, asText: true),
            'value' => fake('vi_VN')->sentence(),
            'type' => 'string',
            'is_public' => true,
        ];
    }
}
