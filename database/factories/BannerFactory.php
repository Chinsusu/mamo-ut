<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Enums\BannerPosition;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake('vi_VN')->sentence(4),
            'subtitle' => fake('vi_VN')->sentence(),
            'image_path' => null,
            'link_url' => null,
            'position' => BannerPosition::HomeHero,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
