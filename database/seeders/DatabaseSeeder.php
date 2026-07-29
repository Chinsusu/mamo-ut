<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@mamo-ut.local'],
            [
                'name' => 'Admin O Út',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        $mamHue = $this->category([
            'name' => 'Mắm Huế',
            'description' => 'Các loại mắm đặc sản Huế làm nền cho bữa cơm gia đình.',
            'sort_order' => 10,
        ]);

        $chaCa = $this->category([
            'name' => 'Chả cá',
            'description' => 'Chả cá vị thanh, dễ chế biến cho bữa ăn hằng ngày.',
            'sort_order' => 20,
        ]);

        $this->product($mamHue, [
            'name' => 'Mắm ruốc',
            'short_description' => 'Mắm ruốc đậm vị Huế, hợp chấm xoài, nêm bún bò hoặc kho rim.',
            'price_vnd' => 65000,
            'stock_quantity' => 50,
            'weight_gram' => 400,
            'is_featured' => true,
            'sort_order' => 10,
        ]);

        $this->product($mamHue, [
            'name' => 'Mắm rò',
            'short_description' => 'Mắm rò thơm, vị mặn mà, dùng kèm cơm nóng và rau sống.',
            'price_vnd' => 75000,
            'stock_quantity' => 40,
            'weight_gram' => 400,
            'is_featured' => true,
            'sort_order' => 20,
        ]);

        $this->product($chaCa, [
            'name' => 'Chả cá lạt',
            'short_description' => 'Chả cá vị lạt, tiện chiên hoặc nấu bún, hợp khẩu vị gia đình.',
            'price_vnd' => 89000,
            'stock_quantity' => 35,
            'weight_gram' => 500,
            'is_featured' => true,
            'sort_order' => 30,
        ]);

        $this->product($mamHue, [
            'name' => 'Mắm tôm chua',
            'short_description' => 'Tôm chua Huế vị chua cay dịu, ăn cùng thịt luộc hoặc cuốn rau.',
            'price_vnd' => 99000,
            'stock_quantity' => 30,
            'weight_gram' => 500,
            'is_featured' => true,
            'sort_order' => 40,
        ]);

        $this->product($mamHue, [
            'name' => 'Mắm tép chua',
            'short_description' => 'Mắm tép chua gọn vị, dễ ăn, hợp làm món chấm nhanh.',
            'price_vnd' => 79000,
            'stock_quantity' => 32,
            'weight_gram' => 400,
            'is_featured' => true,
            'sort_order' => 50,
        ]);

        Banner::query()->updateOrCreate(
            ['position' => 'home_hero', 'sort_order' => 10],
            [
                'title' => 'Đặc sản Huế gửi từ căn bếp O Út',
                'subtitle' => 'Mắm ruốc, tôm chua, mắm rò và chả cá cho bữa cơm đậm vị miền Trung.',
                'link_url' => '/san-pham',
                'is_active' => true,
            ],
        );

        Post::query()->updateOrCreate(
            ['slug' => 'cach-dung-mam-ruoc-hue'],
            [
                'title' => 'Cách dùng mắm ruốc Huế trong bữa cơm nhà',
                'excerpt' => 'Một vài cách dùng mắm ruốc dễ áp dụng: chấm, nêm, kho và làm sốt.',
                'body' => 'Mắm ruốc Huế có thể dùng để nêm bún bò, kho thịt, pha nước chấm hoặc rim cùng sả ớt. Khi nấu, nên bắt đầu bằng lượng nhỏ rồi nêm thêm để giữ vị cân bằng.',
                'seo_title' => 'Cách dùng mắm ruốc Huế | O Út Đặc Sản Huế',
                'seo_description' => 'Gợi ý dùng mắm ruốc Huế để chấm, nêm, kho và làm sốt trong bữa cơm gia đình.',
                'is_published' => true,
                'published_at' => now(),
            ],
        );

        $settings = [
            ['general', 'shop_name', 'Tên cửa hàng', 'O Út Đặc Sản Huế', 'string', true],
            ['general', 'shop_phone', 'Số điện thoại', '', 'string', true],
            ['general', 'shop_email', 'Email', '', 'string', true],
            ['general', 'shop_address', 'Địa chỉ', '', 'text', true],
            ['payment', 'bank_account_name', 'Tên tài khoản ngân hàng', '', 'string', false],
            ['payment', 'bank_account_number', 'Số tài khoản ngân hàng', '', 'string', false],
            ['payment', 'bank_name', 'Tên ngân hàng', '', 'string', false],
            ['payment', 'qr_image_path', 'Ảnh mã QR', '', 'image', false],
            ['shipping', 'shipping_fee_hue_vnd', 'Phí giao tại Huế', '25000', 'integer', true],
            ['shipping', 'shipping_fee_other_vnd', 'Phí giao tỉnh khác', '40000', 'integer', true],
            ['shipping', 'free_shipping_threshold_vnd', 'Ngưỡng miễn phí giao', '500000', 'integer', true],
            ['shipping', 'shipping_fee_note', 'Ghi chú phí giao hàng', 'Phí giao hàng được tính và hiển thị trước khi đặt đơn.', 'text', true],
        ];

        foreach ($settings as [$group, $key, $label, $value, $type, $isPublic]) {
            ShopSetting::query()->updateOrCreate(
                ['key' => $key],
                compact('group', 'label', 'value', 'type') + ['is_public' => $isPublic],
            );
        }
    }

    /**
     * @param  array{name: string, description: string, sort_order: int}  $data
     */
    private function category(array $data): Category
    {
        /** @var Category $category */
        $category = Category::query()->updateOrCreate(
            ['slug' => Str::slug($data['name'])],
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'seo_title' => $data['name'].' | O Út Đặc Sản Huế',
                'seo_description' => $data['description'],
                'is_active' => true,
                'sort_order' => $data['sort_order'],
            ],
        );

        return $category;
    }

    /**
     * @param array{
     *     name: string,
     *     short_description: string,
     *     price_vnd: int,
     *     stock_quantity: int,
     *     weight_gram: int,
     *     is_featured: bool,
     *     sort_order: int
     * } $data
     */
    private function product(Category $category, array $data): Product
    {
        /** @var Product $product */
        $product = Product::query()->updateOrCreate(
            ['slug' => Str::slug($data['name'])],
            [
                'category_id' => $category->id,
                'name' => $data['name'],
                'short_description' => $data['short_description'],
                'description' => $data['short_description'],
                'status' => ProductStatus::Draft,
                'is_featured' => $data['is_featured'],
                'published_at' => now(),
                'sort_order' => $data['sort_order'],
                'seo_title' => $data['name'].' | O Út Đặc Sản Huế',
                'seo_description' => $data['short_description'],
            ],
        );

        ProductVariant::query()->updateOrCreate(
            ['sku' => 'MOU-'.Str::upper(Str::slug($data['name'], ''))],
            [
                'product_id' => $product->id,
                'label' => $data['weight_gram'].'g',
                'weight_gram' => $data['weight_gram'],
                'price_vnd' => $data['price_vnd'],
                'compare_at_price_vnd' => null,
                'stock_quantity' => $data['stock_quantity'],
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 0,
            ],
        );

        $product->forceFill([
            'status' => ProductStatus::Active,
            'published_at' => $product->published_at ?? now(),
        ])->save();

        return $product;
    }
}
