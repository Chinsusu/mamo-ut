# CR-001: Chốt baseline v1.1 cho phạm vi, đơn hàng và vận hành

| Thuộc tính | Giá trị |
| --- | --- |
| Trạng thái | Approved |
| Ngày hiệu lực | 2026-07-29 |
| Baseline nguồn | Bộ hồ sơ O Út v1.0 |
| Baseline đích | v1.1 |
| Người phê duyệt nghiệp vụ | Chủ dự án |
| Phạm vi ảnh hưởng | Product, UX, data, route, runtime, deploy, UAT |

## 1. Mục đích

Change request này đóng các điểm chưa rõ và xung đột được phát hiện khi đối
chiếu bộ hồ sơ v1.0 với các quyết định trực tiếp của chủ dự án và skeleton
Laravel hiện tại. Bộ v1.0 được giữ nguyên để truy vết. Khi nội dung mâu thuẫn,
CR-001 có mức ưu tiên cao hơn v1.0.

## 2. Phạm vi sản phẩm

### 2.1 MVP bắt buộc

- Storefront responsive theo `OUT-UX-001` và ảnh `UI_Reference_O_Ut.png`.
- Danh mục, sản phẩm, tìm kiếm, giỏ hàng và guest checkout.
- Thanh toán COD hoặc chuyển khoản ngân hàng.
- Admin quản lý sản phẩm, danh mục, đơn hàng, khách hàng, banner, bài viết và
  cấu hình shop.
- Chạy trực tiếp trên VPS, không dùng Docker.

### 2.2 Tài khoản khách hàng

- Guest checkout là luồng mặc định và không được yêu cầu đăng nhập.
- Khách có thể đăng ký, đăng nhập, đăng xuất, đặt lại mật khẩu và xem đơn của
  chính mình.
- Tài khoản khách hàng là phần triển khai sau guest checkout nhưng phải hoàn
  tất trước UAT cuối.
- `users` lưu danh tính đăng nhập. `customers` là hồ sơ mua hàng và có quan hệ
  một-một tùy chọn với `users`.
- Đơn guest có thể có `customer_id = null` nhưng luôn lưu snapshot tên, email,
  số điện thoại và địa chỉ. Khi khách đăng ký hoặc đăng nhập, chỉ liên kết các
  đơn cũ sau khi xác minh đúng email hoặc số điện thoại; không ghép tự động chỉ
  dựa trên dữ liệu nhập.
- Khách chỉ được đọc và sửa hồ sơ của mình. Nhân viên admin được quản lý hồ sơ
  khách trong Filament theo quyền được cấp.
- Dữ liệu khách không có nghĩa vụ kế toán được ẩn danh sau 24 tháng không hoạt
  động. Dữ liệu đơn hàng thuộc thời hạn lưu trữ kế toán áp dụng sẽ được giữ theo
  chính sách pháp lý được chủ shop phê duyệt trước go-live.

### 2.3 Banner

- Vị trí hỗ trợ ở MVP: `home_hero`, `home_promo`, `product_listing`.
- Banner có tiêu đề, nội dung phụ, ảnh, URL đích, thứ tự, trạng thái và khoảng
  thời gian hiệu lực.
- Chỉ banner đang bật và nằm trong khoảng hiệu lực mới được hiển thị.
- URL nội bộ phải là đường dẫn tương đối. URL ngoài phải dùng HTTPS.

## 3. Taxonomy và dữ liệu catalog

### 3.1 Danh mục và sản phẩm mở bán

Hai danh mục dữ liệu ban đầu:

1. Mắm Huế
2. Chả cá

Năm sản phẩm mở bán ban đầu:

1. Mắm ruốc
2. Mắm rò
3. Chả cá lạt
4. Mắm tôm chua
5. Mắm tép chua

Năm ô/chip trên trang chủ là lối tắt biên tập tới năm sản phẩm trên, không phải
năm bản ghi danh mục. Trang SEO danh mục chỉ dùng hai danh mục dữ liệu.

### 3.2 Cấu trúc sản phẩm

- `products` lưu nội dung dùng chung, trạng thái xuất bản, SEO và thứ tự.
- `product_variants` lưu SKU, nhãn biến thể, trọng lượng, giá và tồn kho.
- Mỗi sản phẩm phải có đúng một biến thể mặc định đang hoạt động trước khi được
  xuất bản.
- Khi sản phẩm đang `active`, biến thể mặc định không được bỏ cờ hoặc xóa trước
  khi một biến thể mặc định thay thế được thiết lập.
- `product_images` lưu ảnh gốc riêng tư và các đường dẫn phái sinh công khai
  (`thumb`, `card`, `product`), alt text, kích thước, dung lượng, thứ tự và cờ
  ảnh chính.
- Ảnh gốc không được phục vụ trực tiếp qua web. Storefront chỉ dùng ảnh phái
  sinh trên disk công khai.
- Trạng thái sản phẩm: `draft`, `active`, `hidden`. Chỉ `active` với
  `published_at <= now()` mới xuất hiện trên storefront.

## 4. Thanh toán, trạng thái đơn và tồn kho

### 4.1 Phương thức thanh toán

Chỉ có hai giá trị nghiệp vụ:

- `cod`
- `bank_transfer`

QR là cách trình bày thông tin chuyển khoản, không phải phương thức thanh toán
thứ ba. Ảnh QR, tên ngân hàng, chủ tài khoản và số tài khoản được lấy từ cấu
hình shop.

### 4.2 Trạng thái thanh toán

- `unpaid`: mặc định cho COD.
- `pending_verification`: mặc định cho chuyển khoản.
- `paid`: tiền đã được nhân viên xác nhận hoặc COD đã thu.
- `refunded`: tiền đã hoàn.
- `failed`: giao dịch hoặc xác minh thất bại.

Đơn chuyển khoản ở trạng thái `pending_verification` hết hạn sau 24 giờ, lấy từ
`BANK_TRANSFER_HOLD_HOURS`. Chỉ đơn `new` chưa thanh toán mới bị hủy tự động.

### 4.3 Trạng thái hoàn tất đơn

Chuỗi trạng thái:

`new -> confirmed -> preparing -> packed -> shipping -> delivered`

Nhánh ngoại lệ:

- `new`, `confirmed`, `preparing` hoặc `packed` có thể chuyển sang `cancelled`.
- `shipping` có thể chuyển sang `delivery_failed`.
- `delivery_failed` có thể giao lại về `shipping` hoặc kết thúc ở `returned`.
- `delivered` có thể chuyển sang `returned` sau quy trình đổi trả.
- `cancelled` và `returned` là trạng thái cuối.

COD được phép chuyển từ `new` sang `confirmed` khi còn `unpaid`. Chuyển khoản
chỉ được xác nhận đơn khi `payment_status = paid`.

Mọi thay đổi trạng thái phải tạo một bản ghi `order_status_history` gồm trạng
thái cũ, trạng thái mới, người thao tác, thời điểm và ghi chú tùy chọn.

### 4.4 Giữ và hoàn tồn kho

- Tồn kho được trừ trong cùng transaction tạo đơn.
- Đơn hết hạn hoặc bị hủy trước khi giao được hoàn kho đúng một lần.
- `inventory_released_at` là khóa chống hoàn kho lặp.
- Đơn `returned` không tự động nhập lại kho; nhân viên kiểm tra hàng rồi ghi
  nhận điều chỉnh tồn riêng.

## 5. Idempotency checkout

- Mọi yêu cầu tạo đơn phải có `Idempotency-Key` không rỗng.
- Server lưu `idempotency_key` dạng hash SHA-256, `NOT NULL`, `UNIQUE`.
- Server lưu thêm `idempotency_payload_hash` SHA-256 của payload checkout đã
  chuẩn hóa.
- Chuẩn hóa gồm trim text, email chữ thường, số điện thoại chỉ còn chữ số, gộp
  item trùng và sắp item theo ID biến thể.
- Gửi lại cùng key và cùng payload trả về đơn đã tạo, không trừ kho lần hai.
- Cùng key nhưng payload khác trả HTTP `409`.
- Client sinh key mới khi người dùng chủ động bắt đầu lần checkout mới.

## 6. Địa chỉ và phí giao hàng

- MVP không tích hợp API địa giới hoặc hãng vận chuyển.
- Checkout dùng bốn trường text bắt buộc: tỉnh/thành, quận/huyện, phường/xã và
  địa chỉ chi tiết.
- Admin nhập tay các quy tắc: phí nội tỉnh, phí tỉnh khác và ngưỡng miễn phí.
- Phí giao hàng phải được tính và hiển thị trước khi khách gửi đơn.
- Đơn lưu snapshot phí. Sau khi tạo đơn, mọi chỉnh sửa phí thủ công phải có lý
  do; lý do cùng mức phí cũ/mới được ghi vào audit log và không được tái sử dụng
  ngầm cho lần chỉnh tiếp theo.

## 7. Route contract

| Method | Route | Mục đích | Index |
| --- | --- | --- | --- |
| GET | `/` | Trang chủ | Có |
| GET | `/san-pham` | Danh sách sản phẩm | Có |
| GET | `/san-pham/danh-muc/{category:slug}` | Trang danh mục | Có |
| GET | `/san-pham/{product:slug}` | Chi tiết sản phẩm | Có |
| GET | `/tim-kiem?q=` | Tìm kiếm | Không |
| GET | `/lien-he` | Liên hệ | Có |
| GET/POST | `/dang-ky` | Đăng ký khách | Không |
| GET/POST | `/dang-nhap` | Đăng nhập khách | Không |
| POST | `/dang-xuat` | Đăng xuất | Không |
| GET/POST | `/quen-mat-khau` | Đặt lại mật khẩu | Không |
| GET | `/tai-khoan` | Hồ sơ và đơn của khách | Không |
| GET | `/up` | Health check chuẩn | Không |
| GET | `/health` | Alias tương thích môi trường DEV | Không |

Route danh mục phải được khai báo trước route chi tiết sản phẩm để không bị
route binding bắt nhầm. Trang tìm kiếm và tài khoản phải có `noindex,follow`.

Nhóm route tài khoản là contract của phase 2: được triển khai sau guest checkout
nhưng phải hoàn tất trước UAT cuối. Các route storefront còn lại thuộc MVP đầu.

## 8. Runtime và lưu trữ

- PHP, Laravel, queue, scheduler và database dùng UTC.
- Giao diện hiển thị theo `APP_DISPLAY_TIMEZONE=Asia/Ho_Chi_Minh`.
- Session admin giữ mặc định 120 phút; không tăng session để giữ giỏ hàng.
- Giỏ guest tồn tại ít nhất 7 ngày qua cookie ký hoặc cart storage riêng, cấu
  hình bằng `CART_LIFETIME_DAYS=7`.
- `FILESYSTEM_DISK=local` dùng cho dữ liệu riêng tư và ảnh gốc.
- `MEDIA_PUBLIC_DISK=public` dùng cho ảnh phái sinh được phép phục vụ.
- VPS một node dùng `CACHE_STORE=file`; queue và session dùng database.

## 9. Deploy và backup

- Tất cả môi trường dùng layout `/var/www/mamo-ut` trên máy chủ riêng của môi
  trường đó.
- Marker DEV là `mamo-ut-dev`; marker production là `mamo-ut-production`.
- `/up` là health endpoint chuẩn cho deploy. `/health` được giữ làm alias.
- Production dùng release bất biến, symlink `current`, deploy lock, health
  check, rollback symlink và giữ tối thiểu hai release.
- Database được logical dump với `--single-transaction` mỗi 4 giờ và mã hóa
  trước khi đẩy offsite; mục tiêu RPO tối đa 4 giờ.
- Có thêm full backup hằng đêm gồm database và media upload. Mục tiêu RTO là 4
  giờ. Restore drill phải chạy ít nhất mỗi quý.
- Backup chỉ được coi là đạt khi có checksum, bản sao offsite, chính sách giữ
  7 bản ngày, 4 bản tuần, 6 bản tháng và biên bản restore test.
- Archive local chỉ giữ 8 ngày trong root backup đã xác thực; bản offsite áp
  dụng lifecycle ở trên.

## 10. UAT bổ sung

CR-001 bổ sung các ca bắt buộc:

1. Năm sản phẩm nằm đúng hai danh mục và năm shortcut trang chủ không tạo danh
   mục giả.
2. Sản phẩm không có biến thể mặc định đang hoạt động không thể xuất bản.
3. Gửi checkout lặp cùng idempotency key không tạo đơn hoặc trừ kho lần hai.
4. Cùng key với payload khác trả `409`.
5. COD được xác nhận khi chưa thanh toán; chuyển khoản chưa `paid` thì không.
6. Đơn chuyển khoản quá hạn bị hủy và hoàn kho đúng một lần.
7. Mọi đổi trạng thái có lịch sử; chỉnh phí sau tạo đơn có audit.
8. Guest checkout hoàn tất không cần tài khoản.
9. Khách chỉ xem được đơn đã liên kết với chính mình.
10. Banner chỉ hiện đúng vị trí và thời gian hiệu lực.
11. `/up` và `/health` đều trả 200 nhưng pipeline dùng `/up`.
12. Restore backup vào môi trường cô lập thành công trong RTO.

## 11. Kế hoạch tương thích

Repository đang ở giai đoạn bootstrap, chưa có dữ liệu production. Vì vậy các
migration khởi tạo được chỉnh trực tiếp theo v1.1; không cần migration chuyển
đổi dữ liệu cũ. Từ sau lần deploy có dữ liệu dùng thật, mọi thay đổi schema phải
dùng migration tiến và không sửa migration đã chạy.

