# Triển khai và backup production

Runbook này áp dụng baseline v1.1 cho một VPS production chạy trực tiếp, không
Docker. Chưa có hostname/IP production nên việc bootstrap và lần deploy đầu
được thực hiện có kiểm soát; không ghi secret hoặc thông tin server vào Git.

## 1. Contract môi trường

| Thuộc tính | Giá trị |
| --- | --- |
| App root | `/var/www/mamo-ut` |
| Current release | `/var/www/mamo-ut/current` |
| Shared data | `/var/www/mamo-ut/shared` |
| Deploy marker | `mamo-ut-production` |
| Health chuẩn | `/up` |
| Health tương thích | `/health` |
| App/DB timezone | UTC |
| Display timezone | `Asia/Ho_Chi_Minh` |
| RPO | Tối đa 4 giờ |
| RTO | Tối đa 4 giờ |

Production và DEV có thể dùng cùng app root vì nằm trên hai máy độc lập. Không
chạy hai môi trường trong cùng một root.

## 2. Bootstrap VPS

Tạo user deploy không có shell root trực tiếp, cài Nginx, PHP 8.3 FPM, Composer
2, Node.js 24 LTS, MySQL 8.4, Supervisor, Git, `rsync`, `curl`, `flock` và
`rclone`.

Tạo layout và marker bằng root:

```bash
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut/releases
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut/shared
printf 'mamo-ut-production\n' > /var/www/mamo-ut/.mamo-ut-deploy-root
chown root:root /var/www/mamo-ut/.mamo-ut-deploy-root
chmod 0444 /var/www/mamo-ut/.mamo-ut-deploy-root
install -o deploy -g www-data -m 0640 /dev/null /var/www/mamo-ut/shared/.env
```

Nginx phải trỏ `root` tới `/var/www/mamo-ut/current/public`. Scheduler gọi
`php /var/www/mamo-ut/current/artisan schedule:run` mỗi phút. Queue worker chạy
qua Supervisor và dùng chính file `.env` shared.

## 3. Cấu hình bắt buộc

File `/var/www/mamo-ut/shared/.env` phải có:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_DISPLAY_TIMEZONE=Asia/Ho_Chi_Minh
APP_URL=https://example.com

SESSION_DRIVER=database
SESSION_LIFETIME=120
CART_LIFETIME_DAYS=7
CACHE_STORE=file
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
MEDIA_PUBLIC_DISK=public
BANK_TRANSFER_HOLD_HOURS=24
```

Điền credential MySQL, mail, tài khoản ngân hàng và shipping bằng kênh secret
riêng. Chạy `php artisan key:generate` một lần nếu chưa có `APP_KEY`; không đổi
key tùy tiện sau khi có dữ liệu.

## 4. Deploy

Từ working copy đúng commit đã qua CI:

```bash
APP_ROOT=/var/www/mamo-ut \
HEALTHCHECK_URL=http://127.0.0.1/up \
KEEP_RELEASES=5 \
RELOAD_SERVICES=true \
bash scripts/deploy-production.sh deploy
```

Script khóa deploy, dựng release bất biến, cài dependency, build asset, chạy
migration, chuyển symlink nguyên tử, restart runtime, kiểm tra `/up` và tự quay
lại release trước nếu health check thất bại. Migration database không tự rollback.

Rollback ứng dụng:

```bash
APP_ROOT=/var/www/mamo-ut \
HEALTHCHECK_URL=http://127.0.0.1/up \
RELOAD_SERVICES=true \
bash scripts/deploy-production.sh rollback
```

Chỉ định release ID làm đối số thứ hai khi cần. Mọi migration production phải
theo expand-contract để release trước vẫn đọc được schema mới trong cửa sổ
rollback.

## 5. Backup offsite

Tạo `/etc/mamo-ut/mysql-backup.cnf` mode `0600`, chỉ chứa user backup có quyền
đọc cần thiết:

```ini
[client]
host=127.0.0.1
user=mamo_ut_backup
password=REPLACE_ON_SERVER
```

Tạo root và marker:

```bash
install -d -o backup -g backup -m 0700 /var/backups/mamo-ut
printf 'mamo-ut-production-backup\n' > /var/backups/mamo-ut/.mamo-ut-backup-root
chown root:root /var/backups/mamo-ut/.mamo-ut-backup-root
chmod 0444 /var/backups/mamo-ut/.mamo-ut-backup-root
```

`OFFSITE_REMOTE` phải là một remote `rclone crypt`; bucket thường không được
dùng trực tiếp. Lịch tối thiểu:

```cron
17 */4 * * * backup OFFSITE_REMOTE=crypt:mamo-ut DB_NAME=mamo_ut bash /var/www/mamo-ut/current/scripts/backup-production.sh database
43 2 * * * backup OFFSITE_REMOTE=crypt:mamo-ut DB_NAME=mamo_ut bash /var/www/mamo-ut/current/scripts/backup-production.sh full
```

Job 4 giờ tạo logical dump nhất quán bằng `--single-transaction`. Job đêm tạo
thêm archive toàn bộ shared storage. Mỗi archive được kiểm tra gzip, tạo SHA-256
và upload offsite. Bản local nằm trong `/var/backups/mamo-ut/archives` và được
xóa sau 8 ngày; chính sách lifecycle trên remote giữ 7 bản ngày, 4 bản tuần và
6 bản tháng. Alert nếu job không có bản thành công mới trong 5 giờ.

## 6. Restore drill

Mỗi quý, restore vào VPS hoặc database cô lập:

1. Tải archive và file `.sha256` từ remote mã hóa.
2. Chạy `sha256sum --check <archive>.sha256` và `gzip -t <archive>`.
3. Tạo database rỗng riêng; không restore đè production.
4. Import bằng `gunzip -c database-....sql.gz | mysql --defaults-extra-file=... mamo_ut_restore`.
5. Giải nén media vào storage tạm và chạy `storage:link`.
6. Chạy migration status, smoke test storefront/admin, kiểm tra số đơn và tổng
   tiền theo mẫu đối soát.
7. Ghi thời gian hoàn tất, checksum, người thực hiện và kết quả. Drill không
   đạt nếu vượt 4 giờ hoặc thiếu dữ liệu.

## 7. Go-live gate

- CI xanh trên đúng commit deploy.
- `APP_DEBUG=false`, HTTPS/HSTS và quyền file đúng.
- Queue, scheduler, mail, `/up` và log rotation hoạt động.
- Backup offsite gần nhất dưới 4 giờ và restore drill đạt.
- Dữ liệu thật trong checklist baseline v1.1 đã thay placeholder.
- Có người chịu trách nhiệm rollback, database và truyền thông sự cố.

