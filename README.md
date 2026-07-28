# Mắm O Út

Repository triển khai website thương mại điện tử O Út Đặc Sản Huế.

## Trạng thái

Repository đang ở giai đoạn bootstrap. Bộ quy chuẩn phát triển, kiểm thử và
CI/CD được thiết lập trước khi đưa mã ứng dụng vào để mọi thay đổi sau đó đi
qua cùng một quy trình kiểm soát.

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

- [Coding standard](docs/CODING_STANDARDS.md)
- [Quy trình phát triển, kiểm thử và Git](docs/DEVELOPMENT_WORKFLOW.md)
- [Thiết lập môi trường development và CD](docs/DEPLOYMENT_DEV.md)
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

## Nguyên tắc bảo mật

Không commit `.env`, private key, token, mật khẩu, bản sao database hoặc dữ
liệu khách hàng. Secret ứng dụng nằm trong `shared/.env` trên máy dev; SSH
private key chỉ nằm trên máy quản trị dùng để bootstrap, không lưu trong GitHub
Environment hoặc self-hosted runner.
