<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 4);
        $unitPrice = fake()->randomElement([65000, 75000, 89000, 99000, 120000]);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'product_name' => fake('vi_VN')->randomElement([
                'Mắm ruốc Huế',
                'Mắm rò O Út',
                'Chả cá lạt',
                'Mắm tôm chua',
                'Mắm tép chua',
            ]),
            'variant_label' => 'Hũ 400g',
            'product_sku' => 'MOU-'.fake()->numerify('####'),
            'quantity' => $quantity,
            'unit_price_vnd' => $unitPrice,
            'line_total_vnd' => $quantity * $unitPrice,
        ];
    }
}
