<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            if (! $product->variants()->exists()) {
                ProductVariant::factory()
                    ->for($product)
                    ->create([
                        'sku' => 'MOU-'.Str::upper(Str::random(8)),
                        'is_default' => true,
                    ]);

                $product->forceFill([
                    'status' => ProductStatus::Active,
                    'published_at' => $product->published_at ?? now(),
                ])->save();
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake('vi_VN')->randomElement([
            'Mắm ruốc Huế',
            'Mắm rò O Út',
            'Chả cá lạt',
            'Mắm tôm chua',
            'Mắm tép chua',
        ]);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'short_description' => fake('vi_VN')->sentence(),
            'description' => fake('vi_VN')->paragraphs(2, asText: true),
            'ingredients' => null,
            'usage_instruction' => null,
            'storage_instruction' => null,
            'shelf_life_text' => null,
            'status' => ProductStatus::Draft,
            'is_featured' => fake()->boolean(35),
            'sort_order' => 0,
            'seo_title' => null,
            'seo_description' => null,
            'published_at' => now(),
        ];
    }
}
