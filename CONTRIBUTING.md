# Contributing to Mắm O Út

Cảm ơn bạn đã đóng góp cho dự án. Tài liệu này quy định cách viết mã và gửi thay đổi vào `Chinsusu/mamo-ut`. Quy trình chi tiết từ phát triển đến phát hành nằm tại [docs/DEVELOPMENT_WORKFLOW.md](docs/DEVELOPMENT_WORKFLOW.md).

## 1. Nguyên tắc chung

- `master` là nhánh ổn định duy nhất và luôn phải ở trạng thái có thể triển khai.
- Mọi thay đổi đi qua pull request (PR); không push trực tiếp vào `master`.
- Chỉ `origin = git@github.com:Chinsusu/mamo-ut.git` là remote được phép ghi.
- Mỗi PR giải quyết một mục tiêu rõ ràng, có kiểm thử tương ứng và đủ nhỏ để review.
- Không commit bí mật, dữ liệu khách hàng, file `.env`, khóa SSH, bản dump cơ sở dữ liệu hoặc artefact build.
- Không bỏ qua kiểm thử bằng `--no-verify`, không force-push lên `master`, không sửa lịch sử của nhánh đã chia sẻ nếu chưa phối hợp với reviewer.

## 2. Stack mục tiêu

- PHP 8.3
- Laravel 13
- Livewire 4
- Filament 5
- Tailwind CSS 4
- MySQL 8.4 LTS
- Node.js phiên bản LTS được khóa bởi dự án

Phiên bản thực tế trong `composer.lock`, `package-lock.json` và file cấu hình runtime là nguồn sự thật. Khi các file khóa đã tồn tại, không tự ý nâng phiên bản dependency trong một PR không liên quan.

## 3. Chuẩn bị môi trường local

Yêu cầu tối thiểu:

- PHP 8.3 và các extension Laravel yêu cầu
- Composer 2
- Node.js LTS và npm
- MySQL 8.4
- Git và OpenSSH

Khởi tạo lần đầu:

```bash
git clone git@github.com:Chinsusu/mamo-ut.git
cd mamo-ut
cp .env.example .env
composer install
php artisan key:generate
npm ci
php artisan migrate
npm run build
```

Chỉ dùng dữ liệu giả hoặc dữ liệu đã ẩn danh ở local. Không sao chép dữ liệu thật từ máy chủ về máy cá nhân.

Chạy môi trường phát triển:

```bash
composer run dev
```

Nếu dự án chưa định nghĩa script `composer run dev`, chạy các tiến trình riêng:

```bash
php artisan serve
php artisan queue:work --tries=3
npm run dev
```

## 4. Nhánh làm việc

Luôn đồng bộ `master` trước khi tạo nhánh:

```bash
git switch master
git pull --ff-only origin master
git switch -c feat/short-description
```

Tên nhánh dùng chữ thường, dấu gạch ngang và một trong các tiền tố:

| Loại | Mẫu | Ví dụ |
|---|---|---|
| Tính năng | `feat/<scope>` | `feat/checkout-address` |
| Sửa lỗi | `fix/<scope>` | `fix/order-total-rounding` |
| Hotfix | `hotfix/<scope>` | `hotfix/payment-callback` |
| Tài liệu | `docs/<scope>` | `docs/deployment-runbook` |
| Refactor | `refactor/<scope>` | `refactor/order-service` |
| Kiểm thử | `test/<scope>` | `test/inventory-reservation` |
| Hạ tầng/CI | `chore/<scope>` | `chore/github-actions` |
| Agent Codex | `codex/<scope>` | `codex/project-standards-cicd` |

Không dùng tên cá nhân, `temp`, `test1`, `final` hoặc nhánh sống lâu kiểu `develop`.

## 5. Coding standard

### PHP và Laravel

- Tuân thủ PSR-12 và Laravel conventions; Laravel Pint là formatter chuẩn.
- Bật strict typing cho class PHP mới khi tương thích với framework:

```php
<?php

declare(strict_types=1);
```

- Dùng type declaration cho tham số, thuộc tính và kiểu trả về; tránh `mixed` nếu có thể mô tả kiểu chính xác.
- Controller chỉ điều phối HTTP. Nghiệp vụ dùng action/service/domain class có tên theo hành vi.
- Validation nằm trong Form Request hoặc Livewire form object; không tin dữ liệu từ client.
- Authorization bắt buộc qua Policy/Gate trước thao tác đọc hoặc ghi dữ liệu nhạy cảm.
- Tránh query trong vòng lặp; eager-load quan hệ và kiểm tra N+1 trong review.
- Thao tác nhiều bảng phải dùng transaction. Tác vụ có thể retry phải có tính idempotent.
- Tiền tệ lưu bằng số nguyên theo đơn vị nhỏ nhất được dự án quy định; không dùng `float`.
- Thời gian lưu ở UTC; chỉ chuyển timezone ở lớp hiển thị.
- Migration phải an toàn khi triển khai rolling: ưu tiên expand → migrate/backfill → contract.
- Không sửa migration đã chạy ở môi trường chia sẻ; tạo migration mới.
- Không gọi `env()` ngoài file `config/*`; truy cập cấu hình qua `config()`.
- Không log secret, token, mật khẩu, payload thanh toán đầy đủ hoặc PII không cần thiết.

### Livewire và Filament

- Component chỉ public những state cần thiết; validate và authorize lại ở mọi action.
- Không dựa vào trạng thái phía trình duyệt cho giá, quyền, tồn kho hoặc tổng tiền.
- Mỗi component có một trách nhiệm; truy vấn và nghiệp vụ phức tạp được chuyển sang lớp riêng.
- Dùng key ổn định cho danh sách động và tránh hydrate payload lớn không cần thiết.
- Filament Resource/Page/Action phải áp dụng Policy; ẩn UI không thay thế authorization phía server.
- Thông báo lỗi cho người dùng không được làm lộ exception hoặc thông tin hạ tầng.

### Frontend và Tailwind

- Ưu tiên semantic HTML và component tái sử dụng; không lặp utility class dài nếu đã có pattern chung.
- Đáp ứng WCAG 2.2 AA: điều hướng bàn phím, focus rõ ràng, label hợp lệ, độ tương phản và thông báo lỗi dễ hiểu.
- Thiết kế mobile-first và kiểm tra ít nhất các breakpoint mobile, tablet, desktop.
- Không chèn inline script/style tùy tiện; JavaScript phải tương thích với lifecycle của Livewire.
- Ảnh phải có kích thước phù hợp, lazy-load khi thích hợp và `alt` có ý nghĩa.

### SQL và dữ liệu

- MySQL 8.4 là hệ quản trị chuẩn; không dựa vào hành vi riêng của SQLite cho logic sản xuất.
- Khóa chính/khóa ngoại phải cùng kiểu; mặc định dùng unsigned `BIGINT` khi theo chuẩn Laravel.
- Ràng buộc duy nhất và index phải thể hiện invariant nghiệp vụ, không chỉ tối ưu truy vấn.
- Query mới trên đường nóng cần được xem xét bằng `EXPLAIN` và có dữ liệu kiểm thử đủ đại diện.
- Seeder/factory phải xác định được mục đích, không chứa dữ liệu thật.

### Kiểm thử

- Sửa lỗi phải có regression test tái hiện lỗi trước khi sửa.
- Ưu tiên Feature test cho hành vi HTTP/Livewire/Filament và Unit test cho logic thuần.
- Test không phụ thuộc thứ tự chạy, mạng ngoài, đồng hồ hệ thống hoặc dữ liệu tồn tại sẵn.
- Dịch vụ ngoài phải fake/mock ở test; không gọi cổng thanh toán hoặc gửi email thật.
- Logic liên quan tiền, tồn kho, thanh toán, phân quyền và idempotency phải có cả happy path lẫn failure/retry path.

## 6. Commit

Dùng Conventional Commits:

```text
<type>(<scope>): <mô tả ngắn ở thể mệnh lệnh>
```

Các `type` hợp lệ:

- `feat`: tính năng mới
- `fix`: sửa lỗi
- `docs`: tài liệu
- `style`: định dạng, không đổi hành vi
- `refactor`: tái cấu trúc, không thêm tính năng/sửa lỗi
- `perf`: cải thiện hiệu năng
- `test`: thêm hoặc sửa test
- `build`: build system/dependency
- `ci`: CI/CD
- `chore`: bảo trì
- `revert`: hoàn tác

Ví dụ:

```text
feat(checkout): validate two-level shipping address
fix(inventory): prevent duplicate reservation on retry
ci(deploy): add non-production smoke check
```

Breaking change dùng dấu `!` và footer:

```text
feat(api)!: normalize order status response

BREAKING CHANGE: `payment_status` is now returned separately from `status`.
```

Trước khi commit, ưu tiên cổng kiểm tra thống nhất:

```bash
./scripts/ci.sh
git diff --check
git status --short
```

Nếu script chưa tồn tại trong giai đoạn bootstrap, chạy tối thiểu:

```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --no-progress
php artisan test
npm run build
```

Chỉ stage file thuộc phạm vi thay đổi. Không dùng `git add .` khi worktree có file không liên quan.

## 7. Pull request và review

Trước khi mở PR:

```bash
git fetch origin
git rebase origin/master
git push -u origin HEAD
```

PR phải:

- Nhắm vào `Chinsusu/mamo-ut:master`.
- Dùng template của dự án và liên kết issue/ticket nếu có.
- Mô tả vấn đề, cách giải quyết, rủi ro và cách kiểm thử.
- Có ảnh/video cho thay đổi giao diện.
- Nêu migration, config, queue, cache hoặc bước triển khai đặc biệt.
- Không chứa thay đổi không liên quan hoặc dependency update ngoài phạm vi.

Reviewer kiểm tra:

- Đúng nghiệp vụ và không tạo regression.
- Authorization, validation, bảo mật và dữ liệu nhạy cảm.
- Transaction, idempotency, concurrency và rollback.
- N+1 query, index, cache, queue và khả năng quan sát.
- Test có ý nghĩa, không chỉ tăng coverage.
- Accessibility và responsive nếu có giao diện.

Tác giả phải xử lý từng comment hoặc giải thích rõ lý do không áp dụng. Sau thay đổi quan trọng, yêu cầu reviewer duyệt lại.

## 8. Điều kiện merge

Chỉ merge khi:

- Tất cả required checks xanh.
- Có ít nhất một approval từ người không phải tác giả khi repository đã có
  reviewer thứ hai; phần nhạy cảm cần code owner phù hợp.
- Không còn conversation chưa resolve.
- Nhánh đã cập nhật với `master` và không conflict.
- Checklist PR và Definition of Done đã hoàn tất.

Mặc định dùng **Squash and merge**, xóa nhánh sau merge. Tiêu đề squash phải là Conventional Commit và đủ rõ để tạo changelog.

Trong giai đoạn repository chỉ có một maintainer, không cấu hình approval rule
không thể thỏa mãn. PR và `CI / gate` vẫn bắt buộc; phải bổ sung reviewer thứ
hai trước khi bật rule này.

## 9. Báo cáo vấn đề bảo mật

Không mở issue công khai cho lỗ hổng, credential bị lộ hoặc dữ liệu nhạy cảm. Báo riêng cho maintainer, thu hồi credential liên quan và ghi lại phạm vi ảnh hưởng. Không đưa secret thật vào PR kể cả đã xóa ở commit sau.
