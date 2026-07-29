<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_cannot_be_published_without_an_active_default_variant(): void
    {
        $product = Product::query()->create([
            'category_id' => Category::factory()->create()->getKey(),
            'name' => 'Mắm ruốc',
            'slug' => 'mam-ruoc',
            'status' => ProductStatus::Draft,
        ]);

        $product->status = ProductStatus::Active;
        $product->published_at = now();

        $this->expectException(DomainException::class);
        $product->save();
    }

    public function test_default_variant_cannot_be_removed_from_an_active_product(): void
    {
        $product = Product::factory()->create();
        /** @var ProductVariant $variant */
        $variant = $product->defaultVariant()->firstOrFail();

        $this->expectException(DomainException::class);
        $variant->delete();
    }
}
