# Kết quả xử lý finding baseline ngày 2026-07-29

| Finding | Mức | Trạng thái | Bằng chứng xử lý |
| --- | --- | --- | --- |
| Account, customer và banner chưa có contract | P1 | Closed | CR-001 mục 2; quan hệ `users`/`customers`; banner position và thời gian hiệu lực |
| Payment, timeout và hoàn tồn kho chưa rõ | P1 | Closed | CR-001 mục 4; enum, scheduler và `OrderInventoryService` |
| Idempotency nullable/không xác định | P1 | Closed | CR-001 mục 5; cột hash bắt buộc/unique, payload chuẩn hóa và `OrderPlacementService` |
| Schema sản phẩm/đơn lệch OUT-DATA | P1 | Closed | `product_variants`, invariant biến thể mặc định, `product_images`, status history, audit log và slug redirect |
| Timezone, cart, cache và filesystem xung đột | P2 | Closed | CR-001 mục 8; `.env.example`, `config/app.php`, `config/commerce.php` |
| Route tìm kiếm, liên hệ và danh mục thiếu | P2 | Closed | CR-001 mục 7; route/controller/view và test storefront |
| Hai danh mục hay năm danh mục chưa rõ | P2 | Closed | CR-001 mục 3; seeder hai danh mục, năm sản phẩm/shortcut |
| Địa chỉ và phí giao hàng nhập tay chưa có rule | P2 | Closed | CR-001 mục 6; ba setting phí và `ShippingFeeCalculator` |
| Runbook production/backup còn là ví dụ mơ hồ | P2 | Closed | `DEPLOYMENT_PRODUCTION.md`, deploy marker riêng, backup offsite và dọn archive local sau 8 ngày |
| Health path và tên thương hiệu không nhất quán | P3 | Closed | `/up` chuẩn, `/health` alias; tên hiển thị `O Út Đặc Sản Huế` |

## Kiểm tra bắt buộc

- `migrate:fresh --seed` phải thành công.
- PHP formatter và PHPStan phải xanh.
- Test phải bao phủ replay idempotency sau chuẩn hóa, conflict payload, payment
  gate, expiry, hoàn kho đúng một lần, publishing/default variant gate, audit lý
  do chỉnh phí và route contract.
- Frontend lint/test/build và script syntax phải đạt trước khi merge.

Account customer là phase 2 sau guest checkout nhưng trước UAT cuối. Finding
được đóng ở mức contract; route và UI account sẽ được nghiệm thu cùng module
account, không được dùng để chặn guest checkout.

