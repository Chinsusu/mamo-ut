# Baseline dự án O Út v1.1

Baseline đang hiệu lực là bộ hồ sơ v1.0 cộng với change request CR-001.

## Thứ tự ưu tiên

1. `docs/change-requests/CR-001-baseline-v1.1-alignment.md`
2. Các quyết định được chủ dự án phê duyệt sau ngày 2026-07-29
3. Bộ hồ sơ gốc v1.0
4. Tài liệu vận hành và coding standard trong repository

Khi có mâu thuẫn, tài liệu ở thứ tự cao hơn được áp dụng. Không sửa đè file
DOCX/PDF v1.0; thay đổi tiếp theo phải có change request mới.

## Nội dung được chốt ở v1.1

- Hai danh mục dữ liệu và năm sản phẩm mở bán.
- Guest checkout cùng tài khoản khách tùy chọn.
- Contract khách hàng và banner.
- COD/chuyển khoản; QR chỉ là cách hiển thị chuyển khoản.
- State machine đơn hàng, thời hạn thanh toán và hoàn tồn kho đúng một lần.
- Idempotency bắt buộc khi tạo đơn.
- Địa chỉ text có cấu trúc và bảng phí nhập tay trước checkout.
- Route tìm kiếm, liên hệ, danh mục, tài khoản và health.
- UTC nội bộ, timezone hiển thị Việt Nam, cart 7 ngày và tách disk media.
- Runbook production, RPO/RTO và chính sách kiểm tra restore.

## Tài liệu

- [CR-001](../../change-requests/CR-001-baseline-v1.1-alignment.md)
- [Kết quả đóng finding](../../reviews/2026-07-29-baseline-findings-resolution.md)
- [Bộ hồ sơ gốc v1.0](../v1.0/README.md)
- [Quyết định triển khai](../../PROJECT_DECISIONS.md)
- [Coding standard](../../CODING_STANDARDS.md)
- [Workflow development](../../DEVELOPMENT_WORKFLOW.md)
- [Deploy DEV](../../DEPLOYMENT_DEV.md)
- [Deploy production](../../DEPLOYMENT_PRODUCTION.md)

