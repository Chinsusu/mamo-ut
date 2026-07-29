# Mắm O Út

Repository triển khai website thương mại điện tử O Út Đặc Sản Huế.

## Trạng thái

Repository đã bootstrap skeleton Laravel theo stack mục tiêu. Mã ứng dụng
thương mại điện tử sẽ được bổ sung dần qua các pull request nhỏ, bắt đầu từ
catalog, giỏ hàng, checkout và quản trị.

## Nền tảng mục tiêu

- PHP 8.3, Laravel 13
- Blade, Livewire 4, Alpine.js
- Tailwind CSS 4
- Filament 5
- MySQL 8.4 LTS
- Nginx và PHP-FPM trên VPS

## Quy trình ngắn

1. Tạo branch từ `master` theo mẫu `feat/...`, `fix/...`, `docs/...` hoặc
   `chore/...`.
2. Phát triển và chạy `./scripts/ci.sh`.
3. Commit theo Conventional Commits.
4. Mở pull request vào `master`; không push trực tiếp vào `master`.
5. Chỉ squash-merge khi CI đạt, review hoàn tất và mọi trao đổi đã được xử lý.
6. Merge vào `master` kích hoạt triển khai tự động tới môi trường
   `development`.

## Tài liệu dự án

- [Baseline đang hiệu lực v1.1](docs/project-baseline/v1.1/README.md)
- [CR-001: chốt phạm vi, đơn hàng, route và vận hành](docs/change-requests/CR-001-baseline-v1.1-alignment.md)
- [Kết quả xử lý finding baseline](docs/reviews/2026-07-29-baseline-findings-resolution.md)
- [Bộ hồ sơ baseline sản phẩm, UI/UX, kỹ thuật và nghiệm thu v1.0](docs/project-baseline/v1.0/README.md)
- [Các quyết định triển khai đã xác nhận](docs/PROJECT_DECISIONS.md)
- [Coding standard](docs/CODING_STANDARDS.md)
- [Quy trình phát triển, kiểm thử và Git](docs/DEVELOPMENT_WORKFLOW.md)
- [Thiết lập môi trường development và CD](docs/DEPLOYMENT_DEV.md)
- [Triển khai và backup production](docs/DEPLOYMENT_PRODUCTION.md)
- [Thiết lập GitHub repository](docs/REPOSITORY_SETTINGS.md)
- [Hướng dẫn đóng góp](CONTRIBUTING.md)
- [Chính sách bảo mật](SECURITY.md)

## Kiểm tra nhanh

```bash
./scripts/ci.sh
```

Script này luôn kiểm tra cấu trúc repository. Khi `composer.json` hoặc
`package.json` được thêm vào, script tự động chạy các bước kiểm tra backend và
frontend tương ứng.

## Chạy local

Yêu cầu môi trường trực tiếp, không Docker:

- PHP 8.3 và các extension Laravel yêu cầu
- Composer 2
- Node.js 24 LTS và npm
- MySQL 8.4 LTS

Khởi tạo lần đầu:

```bash
cp .env.example .env
composer install
php artisan key:generate
npm ci
php artisan migrate
npm run build
```

Chạy môi trường phát triển:

```bash
composer run dev
```

Admin panel Filament mặc định nằm tại `/admin`. Tạo tài khoản admin local bằng:

```bash
php artisan filament:make-user
```

## Nguyên tắc bảo mật

Không commit `.env`, private key, token, mật khẩu, bản sao database hoặc dữ
liệu khách hàng. Secret ứng dụng nằm trong `shared/.env` trên máy dev; SSH
private key chỉ nằm trên máy quản trị dùng để bootstrap, không lưu trong GitHub
Environment hoặc self-hosted runner.
