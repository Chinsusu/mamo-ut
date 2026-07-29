<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'MOU-'.fake()->unique()->numerify('####'),
            'label' => fake()->randomElement(['Hũ 250g', 'Hũ 400g', 'Gói 500g']),
            'weight_gram' => fake()->randomElement([250, 400, 500]),
            'price_vnd' => fake()->randomElement([65000, 75000, 89000, 99000, 120000]),
            'compare_at_price_vnd' => null,
            'stock_quantity' => fake()->numberBetween(10, 120),
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 0,
        ];
    }

    public function secondary(): static
    {
        return $this->state(fn (): array => [
            'is_default' => false,
            'sort_order' => 10,
        ]);
    }
}
