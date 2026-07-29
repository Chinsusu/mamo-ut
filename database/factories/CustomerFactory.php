<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('vi_VN')->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('09########'),
            'address_line' => fake('vi_VN')->streetAddress(),
            'ward' => 'Phường '.fake('vi_VN')->word(),
            'district' => fake('vi_VN')->randomElement(['Quận 1', 'Quận Bình Thạnh', 'TP Huế']),
            'province' => fake('vi_VN')->randomElement(['TP. Hồ Chí Minh', 'Thừa Thiên Huế', 'Đà Nẵng']),
            'notes' => null,
            'last_active_at' => now(),
        ];
    }
}
