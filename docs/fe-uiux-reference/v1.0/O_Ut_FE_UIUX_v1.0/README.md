# O Út đặc sản Huế — Frontend UI/UX v1.0

Bản frontend hoàn chỉnh bám theo template **O Út đặc sản Huế** đã duyệt. Gói này là prototype tương tác độc lập, không cần cài thư viện và có thể dùng làm nguồn chuẩn để tích hợp vào Laravel 13 + Livewire + Filament.

## Chạy thử

Yêu cầu: Node.js 18 trở lên.

```bash
npm run dev -- --host 127.0.0.1 --port 4173
```

Mở `http://127.0.0.1:4173`.

Có thể chạy nhanh bằng:

- macOS/Linux: `./serve.sh`
- Windows: `serve.bat`

## Màn hình public

| Màn hình | Đường dẫn |
|---|---|
| Trang chủ | `/index.html` |
| Danh sách sản phẩm | `/san-pham.html` |
| Chi tiết sản phẩm | `/chi-tiet-san-pham.html?slug=tom-chua-hue` |
| Giỏ hàng | `/gio-hang.html` |
| Checkout | `/dat-hang.html` |
| Hoàn tất đơn | `/dat-hang-thanh-cong.html` |
| Tra cứu đơn | `/tra-cuu-don.html` |
| Cẩm nang/blog | `/blog.html` |
| Chi tiết bài viết | `/bai-viet.html?slug=tom-chua-hue-an-voi-gi` |
| Về O Út | `/ve-o-ut.html` |
| Chính sách | `/chinh-sach.html` |
| Liên hệ | `/lien-he.html` |

## Màn hình admin

| Màn hình | Đường dẫn |
|---|---|
| Đăng nhập | `/admin/login.html` |
| Dashboard | `/admin/index.html` |
| Sản phẩm | `/admin/products.html` |
| Biên tập sản phẩm | `/admin/product-form.html?id=1` |
| Đơn hàng | `/admin/orders.html` |
| Chi tiết đơn | `/admin/order-detail.html?id=1` |
| Bài viết | `/admin/posts.html` |
| Biên tập bài viết | `/admin/post-editor.html?id=1` |
| Cấu hình cửa hàng | `/admin/settings.html` |

### Dữ liệu demo

- Admin: email hợp lệ bất kỳ; mật khẩu từ 6 ký tự. Mặc định `owner@ouut.vn / ouutdemo`.
- Tra cứu đơn: `OU-260728-8F3K / 0909123456`.
- Mã giảm giá: `OUUT10`.
- Dữ liệu được lưu trong `localStorage`, phục vụ thử giao diện và hành vi. Có thể reset trong DevTools bằng `OUtData.resetDemo()`.

## Chức năng đã mô phỏng

- Tìm kiếm và gợi ý sản phẩm.
- Lọc/sắp xếp danh sách sản phẩm.
- Chọn biến thể, số lượng, gallery và accordion sản phẩm.
- Yêu thích và thêm nhanh vào giỏ.
- Giỏ hàng, cập nhật số lượng, xóa món, áp mã giảm giá.
- Guest checkout, validation, phương thức giao hàng, COD/chuyển khoản.
- Tạo mã đơn, QR demo, tra cứu và timeline đơn hàng.
- Admin dashboard, CRUD mô phỏng sản phẩm, trạng thái đơn, bài viết và cấu hình.
- Responsive desktop/tablet/mobile, mobile menu, bottom navigation và sticky buy bar.
- Metadata cơ bản, semantic HTML và structured data mẫu ở trang chủ.

## Cấu trúc chính

```text
assets/
  css/storefront.css     Design system + public UI
  css/admin.css          Admin UI
  js/data.js             Demo data/localStorage adapter
  js/components.js       Header, footer, product card, common UI
  js/public.js           Public interactions
  js/admin.js            Admin interactions
  images/                Ảnh UI đã tối ưu
  icons/icons.svg        SVG sprite
admin/                   Toàn bộ màn quản trị
laravel/                 Hướng dẫn và scaffold tích hợp Laravel
qa/                      Kết quả QA, screenshot và báo cáo
reference/               Reference thiết kế đã chọn
```

## Ghi chú trước khi go-live

Giá, tồn kho, hotline, địa chỉ, thành phần, hạn dùng, chính sách, tài khoản ngân hàng, review và số đã bán đang là **dữ liệu minh họa**. Phải thay bằng dữ liệu đã được chủ thương hiệu xác nhận.

QR trong prototype không được dùng để chuyển tiền thật. Bản này không chứa backend, xác thực thật, thanh toán thật, upload file thật hoặc database production.

Khi tích hợp Laravel, giữ nguyên lớp view/design token nhưng thay `assets/js/data.js` bằng Eloquent/Livewire action và validation server-side. Xem `laravel/README-LARAVEL.md`.
