## Mục tiêu

<!-- Vấn đề nào được giải quyết? Kết quả mong đợi là gì? -->

## Thay đổi chính

<!-- Liệt kê ngắn gọn các thay đổi có ý nghĩa với reviewer. -->

-

## Liên kết

<!-- Ví dụ: Closes #123 / Ticket / thiết kế / tài liệu liên quan. -->

- Issue/Ticket:

## Cách kiểm thử

<!-- Ghi lệnh đã chạy và kết quả. Không chỉ ghi "đã test". -->

```text
./scripts/ci.sh
```

- [ ] Đã thêm/cập nhật test phù hợp
- [ ] Đã kiểm tra failure/retry path nếu có
- [ ] Không gọi dịch vụ thật hoặc dùng dữ liệu thật trong test

## Bằng chứng UI

<!-- Ảnh/video trước và sau cho thay đổi giao diện; ghi N/A nếu không áp dụng. -->

N/A

## Ảnh hưởng triển khai

<!-- Đánh dấu và mô tả chi tiết bên dưới nếu chọn Có. -->

| Hạng mục | Không | Có | Ghi chú |
|---|:---:|:---:|---|
| Database migration/backfill | [ ] | [ ] | |
| Biến môi trường/config | [ ] | [ ] | |
| Cache/queue/scheduler | [ ] | [ ] | |
| API/route contract | [ ] | [ ] | |
| Breaking change | [ ] | [ ] | |
| Dependency/build | [ ] | [ ] | |
| Quyền truy cập/PII | [ ] | [ ] | |

## Rủi ro và rollback

<!-- Nêu failure mode, cách phát hiện và lệnh/SHA/feature flag để hoàn tác. -->

- Mức rủi ro: Thấp / Trung bình / Cao
- Dấu hiệu lỗi:
- Cách rollback hoặc forward-fix:
- Smoke check sau deploy:

## Checklist tác giả

- [ ] PR chỉ chứa một mục tiêu và nhắm vào `Chinsusu/mamo-ut:master`
- [ ] Tiêu đề PR theo Conventional Commits
- [ ] Đã tự review toàn bộ diff và không có file ngoài phạm vi
- [ ] Không có secret, `.env`, khóa SSH, PII hoặc dữ liệu thật
- [ ] Validation và authorization được thực hiện phía server
- [ ] Đã kiểm tra transaction, idempotency và race condition khi liên quan
- [ ] Không có N+1 hoặc query/index bất hợp lý
- [ ] Migration an toàn khi triển khai và không sửa migration đã chạy
- [ ] Tài liệu, `.env.example` và runbook đã cập nhật khi cần
- [ ] UI đã kiểm tra responsive, keyboard và accessibility khi liên quan
- [ ] Required checks đã xanh
- [ ] Đã hoàn thành Definition of Done trong `docs/DEVELOPMENT_WORKFLOW.md`

## Gợi ý cho reviewer

<!-- Chỉ rõ file/luồng cần chú ý, quyết định thiết kế hoặc câu hỏi còn mở. -->
