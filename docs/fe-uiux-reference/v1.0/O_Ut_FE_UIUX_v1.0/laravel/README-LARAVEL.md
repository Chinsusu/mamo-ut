# Laravel handoff — O Út FE UI/UX

Đây là lớp bàn giao để tích hợp giao diện vào **Laravel 13 + Livewire 4 + Filament 5**. Bản static ở thư mục gốc là nguồn chuẩn trực quan và tương tác; thư mục này chỉ chứa khung view/asset/route, không phải backend hoàn chỉnh.

## 1. Copy asset

```bash
cp -R assets public/assets
cp laravel/resources/css/storefront.css resources/css/storefront.css
cp laravel/resources/js/storefront.js resources/js/storefront.js
cp laravel/resources/css/filament/admin/theme.css resources/css/filament/admin/theme.css
cp -R laravel/resources/views/* resources/views/
```

Cập nhật Vite entry:

```js
laravel({
  input: [
    'resources/css/storefront.css',
    'resources/js/storefront.js',
    'resources/css/filament/admin/theme.css',
  ],
  refresh: true,
})
```

## 2. Component Livewire cần triển khai

| Component | Nhiệm vụ |
|---|---|
| `Storefront\\ProductBuyPanel` | Biến thể, tồn, số lượng, add cart, buy now |
| `Storefront\\Cart` | Dòng giỏ, số lượng, coupon, tổng tiền |
| `Storefront\\Checkout` | Validation, ship/payment, transaction tạo order |
| `Storefront\\OrderTracker` | Tra cứu bằng mã đơn + số điện thoại |

Không đưa logic giá/tổng tiền từ prototype JS vào production. Server phải đọc giá hiện hành từ database, validate tồn và chụp snapshot vào `order_items`.

## 3. Data contract tối thiểu cho view

`Product` cần: `name`, `slug`, `short_description`, `description`, `badge`, `rating`, `seo_title`, `seo_description`, `primary_image_url`, quan hệ `variants`, `images`.

`Variant` cần: `label`, `sku`, `weight_gram`, `price`, `compare_at_price`, `stock_quantity`, `is_active`.

`Post` cần: `title`, `slug`, `excerpt`, `cover_url`, `sanitized_html`, `seo_title`, `seo_description`, `published_at`, `read_time`, quan hệ `category`.

## 4. Admin Filament

Dùng Resource riêng cho:

- Product + RelationManager variants/images.
- Order + status actions/timeline.
- Post + category/SEO fields.
- StoreSettings singleton.

Giữ admin thiên về tốc độ xử lý như bản demo; không áp decorative style của storefront lên table/form.

## 5. SEO

- Public page phải render server-side bằng Blade/Livewire.
- Product/Post có canonical, title, description và JSON-LD.
- Cart, checkout, success, admin đặt `noindex, nofollow`.
- Khi đổi slug, lưu redirect 301.
- Sitemap chỉ chứa entity `published/active`.

## 6. Những phần phải thay trước production

- LocalStorage demo → database + session cart.
- Demo login → Laravel auth + policies.
- QR/tài khoản demo → setting đã xác thực.
- Giá, tồn, thành phần, hạn dùng, shipping rule và policy demo → dữ liệu thật.
- Upload mô phỏng → validation MIME/size + image derivatives.
