# Quyết định triển khai O Út

Tài liệu này là chỉ mục quyết định đã duyệt. Chi tiết contract và tiêu chí
nghiệm thu nằm tại
[`CR-001`](change-requests/CR-001-baseline-v1.1-alignment.md). Khi có khác biệt,
baseline v1.1 được ưu tiên hơn bộ v1.0.

## Quyết định đang hiệu lực

| Phạm vi | Quyết định |
| --- | --- |
| Baseline | v1.0 được giữ nguyên; v1.1 = v1.0 + CR-001. |
| Sản phẩm ban đầu | Mắm ruốc, mắm rò, chả cá lạt, mắm tôm chua, mắm tép chua. |
| Taxonomy | Hai danh mục `Mắm Huế`, `Chả cá`; năm shortcut trang chủ trỏ tới năm sản phẩm. |
| Thanh toán | Chỉ `cod` và `bank_transfer`; QR là cách hiển thị chuyển khoản. |
| Phí giao hàng | Quy tắc do admin nhập tay, phí được tính và hiện trước khi gửi đơn. |
| Admin | Sản phẩm, danh mục, đơn hàng, khách hàng, banner, bài viết và cấu hình shop. |
| Checkout | Guest checkout trước; tài khoản khách là tùy chọn và không được chặn checkout. |
| Tồn kho | Trừ khi tạo đơn; hủy/hết hạn hoàn đúng một lần. |
| Hạ tầng | Chạy trực tiếp trên VPS, không Docker. |
| UI/UX | Thi công theo OUT-UX-001 và `UI_Reference_O_Ut.png` ở cả desktop/mobile. |
| Server | Máy chủ bắt đầu sạch; DEV và production dùng runbook riêng. |

## Dữ liệu cần có trước UAT/go-live

- Trọng lượng, giá, tồn kho, SKU, thành phần, cách dùng, bảo quản và hạn dùng thật.
- Ảnh sản phẩm gốc, logo chính thức, ảnh hero và nội dung thương hiệu.
- Hotline, email, địa chỉ, tài khoản ngân hàng và ảnh QR thật.
- Phí nội tỉnh, phí tỉnh khác, ngưỡng miễn phí và phạm vi giao.
- Domain, thông tin VPS production và đích backup offsite.
- Chính sách giao hàng, đổi trả, bảo quản và quyền riêng tư.
- Thời hạn lưu dữ liệu được chủ shop và cố vấn pháp lý phê duyệt.

Các dữ liệu này không chặn bootstrap bằng dữ liệu mẫu nhưng placeholder không
được dùng khi nghiệm thu.