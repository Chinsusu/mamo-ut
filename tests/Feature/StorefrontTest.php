<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_featured_products_and_two_data_categories(): void
    {
        $mamHue = Category::factory()->create([
            'name' => 'Mắm Huế',
            'slug' => 'mam-hue',
            'sort_order' => 10,
        ]);
        $chaCa = Category::factory()->create([
            'name' => 'Chả cá',
            'slug' => 'cha-ca',
            'sort_order' => 20,
        ]);
        $product = Product::factory()
            ->for($mamHue)
            ->create([
                'name' => 'Mắm ruốc',
                'slug' => 'mam-ruoc',
                'is_featured' => true,
            ]);

        $this
            ->get(route('home'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($mamHue->name)
            ->assertSee($chaCa->name);
    }

    public function test_products_index_can_filter_by_legacy_category_query(): void
    {
        $mamHue = Category::factory()->create(['name' => 'Mắm Huế', 'slug' => 'mam-hue']);
        $chaCa = Category::factory()->create(['name' => 'Chả cá', 'slug' => 'cha-ca']);
        Product::factory()->for($mamHue)->create(['name' => 'Mắm rò', 'slug' => 'mam-ro']);
        Product::factory()->for($chaCa)->create(['name' => 'Chả cá lạt', 'slug' => 'cha-ca-lat']);

        $this
            ->get(route('products.index', ['category' => 'mam-hue']))
            ->assertOk()
            ->assertSee('Mắm rò')
            ->assertDontSee('Chả cá lạt');
    }

    public function test_category_has_a_canonical_slug_route(): void
    {
        $category = Category::factory()->create(['name' => 'Mắm Huế', 'slug' => 'mam-hue']);
        Product::factory()->for($category)->create(['name' => 'Mắm tép chua', 'slug' => 'mam-tep-chua']);

        $this
            ->get(route('categories.show', $category))
            ->assertOk()
            ->assertSee('Mắm tép chua');
    }

    public function test_product_detail_uses_slug_route_binding(): void
    {
        $product = Product::factory()->create([
            'name' => 'Mắm tôm chua',
            'slug' => 'mam-tom-chua',
        ]);

        $this
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_search_is_noindex_and_finds_visible_products(): void
    {
        Product::factory()->create(['name' => 'Mắm ruốc', 'slug' => 'mam-ruoc']);
        Product::factory()->create(['name' => 'Chả cá lạt', 'slug' => 'cha-ca-lat']);

        $this
            ->get(route('search', ['q' => 'Mắm ruốc']))
            ->assertOk()
            ->assertSee('noindex,follow', false)
            ->assertSee('Mắm ruốc')
            ->assertDontSee('Chả cá lạt');
    }

    public function test_contact_and_both_health_routes_are_available(): void
    {
        $this->get(route('contact'))->assertOk()->assertSee('O Út Đặc Sản Huế');
        $this->get('/up')->assertOk();
        $this->get('/health')->assertOk()->assertJsonPath('canonical', '/up');
    }
}
