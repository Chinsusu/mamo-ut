<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'original_path' => null,
            'thumb_path' => null,
            'card_path' => null,
            'product_path' => null,
            'alt_text' => fake('vi_VN')->sentence(4),
            'width' => null,
            'height' => null,
            'bytes' => null,
            'sort_order' => 0,
            'is_primary' => true,
        ];
    }
}
