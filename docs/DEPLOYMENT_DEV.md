# Development deployment

Tài liệu này mô tả cách CI kiểm tra mã nguồn và cách triển khai tự động nhánh
`master` lên máy development `10.1.1.109`. Đây **không phải** môi trường
production.

## 1. Kiến trúc và nguyên tắc an toàn

- Workflow `CI` chạy trên GitHub-hosted runner.
- Job tổng hợp `CI / gate` là required check duy nhất cần cấu hình cho branch
  protection. Gate chỉ xanh khi tất cả job phù hợp với trạng thái repository
  đều xanh.
- Workflow `Deploy development` chỉ nhận sự kiện `workflow_run` của `CI` khi:
  lần chạy thành công, sự kiện gốc là push, commit thuộc `master`, và repository
  nguồn chính là `Chinsusu/mamo-ut`.
- Job `preflight` trên GitHub-hosted runner chỉ cho phép deploy khi SHA đã test
  vẫn là tip hiện tại của `master` và đủ `composer.json`, `composer.lock`,
  `package.json`, `package-lock.json`, `artisan`. Repo docs-only hoặc rerun CI
  cũ được skip an toàn.
- `10.1.1.109` là địa chỉ private nên GitHub-hosted runner không thể SSH trực
  tiếp đến máy này. CD dùng self-hosted runner đặt ngay trên máy development;
  runner chủ động kết nối ra GitHub qua HTTPS.
- Không lưu SSH private key, `.env`, database password, runner registration
  token hoặc secret bất kỳ trong Git.
- Deploy dùng release directory, shared `.env`/`storage`, lock chống chạy đồng
  thời, chuyển symlink nguyên tử, health check và tự động quay lại release trước
  nếu kiểm tra sau chuyển đổi thất bại.
- Migration cơ sở dữ liệu không tự động rollback. Mọi thay đổi schema phải theo
  chiến lược expand–migrate–contract và tương thích ít nhất với release ngay
  trước đó.

## 2. Hành vi CI

Chạy local gate trước khi push:

```bash
bash scripts/ci.sh
```

Repository mới chỉ có tài liệu vẫn pass. Khi manifest xuất hiện, CI tự kích hoạt
contract tương ứng:

| Tín hiệu | Kiểm tra bắt buộc |
|---|---|
| Luôn chạy | whitespace, `.env` không bị track, cú pháp shell |
| `composer.json` | `composer.lock`, Composer validate/install/audit, PHP syntax, Pint, PHPStan/Larastan, migration trên database CI riêng, `php artisan test` |
| `package.json` | `package-lock.json`, các script `lint`, `test`, `build`, sau đó `npm ci`, audit dependency và chạy cả ba script |

Script `test` của npm phải kết thúc sau khi chạy xong; không được mở watch mode
trong CI. Khi application đã được bootstrap nhưng thiếu `artisan`, Pint,
lockfile hoặc npm script bắt buộc, CI dừng với thông báo lỗi cụ thể.

Backend CI dùng PHP 8.3 và MySQL 8.4. Frontend CI dùng Node.js 24 LTS,
được khóa bằng `.nvmrc`.

## 3. Bootstrap máy chủ một lần

Hiện tại có thể truy cập bằng `root@10.1.1.109` qua SSH key. Chỉ dùng tài khoản
root cho bootstrap ban đầu. Sau khi xác nhận tài khoản deploy hoạt động, nên tắt
SSH trực tiếp bằng root theo chính sách vận hành của hệ thống.

Từ máy quản trị, private key chỉ nằm trên máy đó:

```bash
chmod 600 /duong-dan/toi/private-key
ssh -i /duong-dan/toi/private-key root@10.1.1.109
```

Trên server, tạo user riêng và cấu trúc thư mục:

```bash
adduser --disabled-password --gecos '' deploy
usermod -aG www-data deploy
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut/releases
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut/shared
install -d -o deploy -g www-data -m 2775 /var/www/mamo-ut/shared/storage
printf 'mamo-ut-dev\n' > /var/www/mamo-ut/.mamo-ut-deploy-root
chown root:root /var/www/mamo-ut/.mamo-ut-deploy-root
chmod 0444 /var/www/mamo-ut/.mamo-ut-deploy-root
install -d -o deploy -g deploy -m 0700 /home/deploy/.ssh
install -o deploy -g deploy -m 0600 /dev/null /home/deploy/.ssh/authorized_keys
```

Thêm **public key** được phép vận hành vào
`/home/deploy/.ssh/authorized_keys`. Không sao chép private key lên server hoặc
GitHub. Kiểm tra đăng nhập `deploy@10.1.1.109` thành công trước khi thay đổi cấu
hình SSH root.

Cài đặt và khóa phiên bản phù hợp:

- PHP 8.3 CLI/FPM cùng các extension Laravel cần dùng;
- Composer 2;
- Node.js 24 LTS và npm;
- MySQL 8.4;
- Nginx, Supervisor, Git, rsync, curl và util-linux (`flock`).

Xác minh:

```bash
php -v
composer --version
node --version
npm --version
mysql --version
rsync --version
flock --version
```

Tạo database và database user riêng cho development với quyền tối thiểu. Nhập
mật khẩu trong phiên MySQL tương tác; không đặt password thật trong tài liệu,
shell history hoặc repository.

## 4. Shared environment và web server

Tạo file bí mật trực tiếp trên server:

```bash
install -o deploy -g www-data -m 0640 /dev/null /var/www/mamo-ut/shared/.env
```

File `.mamo-ut-deploy-root` là marker an toàn bắt buộc. Deploy script chỉ chấp
nhận đúng `/var/www/mamo-ut`, kiểm tra canonical path và marker này trước khi
tạo hoặc xóa release.

Chỉnh file bằng công cụ quản trị an toàn. Giá trị tối thiểu cần rà soát gồm
`APP_ENV`, `APP_KEY`, `APP_URL`, `DB_*`, cache/session/queue, mail, object
storage và thông tin tích hợp. Dù là development, đặt `APP_DEBUG=false` nếu môi
trường có thể được truy cập ngoài nhóm phát triển.

Nginx phải trỏ document root đến symlink ổn định:

```nginx
root /var/www/mamo-ut/current/public;
```

Health endpoint phải trả HTTP 2xx khi ứng dụng sẵn sàng. Có thể dùng URL chỉ
truy cập nội bộ như `http://127.0.0.1/health`; nếu virtual host yêu cầu hostname,
dùng URL development thực tế. Không để health endpoint trả secret hoặc dữ liệu
nhạy cảm.

Worker Supervisor cần chạy dưới user không đặc quyền, dùng đường dẫn
`/var/www/mamo-ut/current/artisan`, và tự khởi động lại khi
`php artisan queue:restart` yêu cầu worker thoát.

## 5. Cài self-hosted runner

Trong repository GitHub:

1. Mở **Settings → Actions → Runners → New self-hosted runner**.
2. Chọn Linux x64 và dùng đúng lệnh tải/kiểm tra checksum mà GitHub hiển thị ở
   thời điểm cài đặt.
3. Chạy các lệnh cấu hình bằng user `deploy`, không phải root.
4. Gắn thêm label `mamo-ut-dev`; workflow yêu cầu đủ các label
   `self-hosted`, `linux`, `x64`, `mamo-ut-dev`.
5. Dùng root đúng một lần để cài runner thành system service chạy với user
   `deploy`, rồi xác nhận runner ở trạng thái Online.

Registration token của runner có thời hạn ngắn và chỉ dùng khi đăng ký; không
ghi token vào file hoặc commit. Self-hosted runner thực thi mã từ repository nên
repository phải private, `master` phải được bảo vệ và không cấp quyền write cho
người không tin cậy.

Runner không cần SSH private key để deploy vì script chạy cục bộ trên chính máy
development.

## 6. GitHub Environment và branch protection

Tạo GitHub Environment tên chính xác `development`, giới hạn deployment branch
là `master`, và nên yêu cầu reviewer nếu nhóm muốn một approval gate trước khi
deploy.

Tạo Environment variables:

| Variable | Ví dụ | Bắt buộc |
|---|---|---|
| `DEV_APP_ROOT` | `/var/www/mamo-ut` | Có |
| `DEV_HEALTHCHECK_URL` | `http://127.0.0.1/health` | Có |
| `DEV_PUBLIC_URL` | `https://dev.example.internal` | Không |
| `DEV_KEEP_RELEASES` | `5` | Có |
| `DEV_RELOAD_SERVICES` | `false` | Có |

Để `DEV_RELOAD_SERVICES=false` trong cấu hình thông thường: queue được restart
bằng Artisan, còn FPM/Nginx không cần reload khi chỉ đổi symlink mã nguồn. Chỉ
bật `true` sau khi đã cấp cho user `deploy` các lệnh sudo **cụ thể, tối thiểu**
đúng với tên service trên máy; không cấp sudo tổng quát.

Branch protection cho `master`:

- bắt buộc pull request trước merge;
- bắt buộc required check `CI / gate`;
- bắt buộc resolve conversation và chặn force push/deletion;
- tùy quy mô nhóm, yêu cầu ít nhất một approval và dismiss stale approvals.

Không đánh dấu riêng `backend` hoặc `frontend` là required check vì chúng được
skip hợp lệ khi repository còn ở giai đoạn docs-only. `gate` luôn chạy và thực
thi đúng điều kiện theo manifest.

## 7. Trình tự deployment

Sau khi PR được merge:

1. Push mới trên `master` kích hoạt `CI`.
2. `repository` phát hiện manifest; `backend`/`frontend` chạy nếu cần.
3. `gate` xác nhận tất cả kiểm tra bắt buộc thành công.
4. `preflight` xác nhận ứng dụng đã bootstrap và SHA vẫn là tip `master`.
5. Self-hosted runner checkout lại `master`, so sánh SHA lần nữa để chặn race
   hoặc rerun cũ.
6. Runner tạo release mới, liên kết `.env` và `storage` dùng chung.
7. Composer/npm build trong release; Laravel migrate và tạo cache.
8. Symlink `current` được chuyển nguyên tử sang release mới.
9. Queue được restart và health check được gọi.
10. Nếu restart hoặc health check thất bại, symlink quay lại release cũ.
11. Sau khi thành công, chỉ giữ số release mới nhất theo
    `DEV_KEEP_RELEASES`.

Các release nằm tại:

```text
/var/www/mamo-ut/
├── current -> releases/<release-id>
├── releases/
└── shared/
    ├── .env
    └── storage/
```

Deploy script dùng `flock`; lần chạy thứ hai sẽ fail thay vì chồng lên một deploy
đang hoạt động.

## 8. Rollback thủ công

Health check thất bại sẽ tự rollback code. Để chủ động quay về release hợp lệ
gần nhất:

```bash
sudo -u deploy env \
  APP_ROOT=/var/www/mamo-ut \
  HEALTHCHECK_URL=http://127.0.0.1/health \
  RELOAD_SERVICES=false \
  bash /var/www/mamo-ut/current/scripts/deploy-dev.sh rollback
```

Hoặc chỉ định release:

```bash
sudo -u deploy env \
  APP_ROOT=/var/www/mamo-ut \
  HEALTHCHECK_URL=http://127.0.0.1/health \
  RELOAD_SERVICES=false \
  bash /var/www/mamo-ut/current/scripts/deploy-dev.sh rollback \
  20260728153000-012345abcdef
```

Rollback trên chỉ đổi code release. Nó **không** chạy `migrate:rollback`; nếu
release mới đã ghi dữ liệu theo schema mới thì rollback schema tự động có thể
gây mất dữ liệu. Dùng expand–contract, feature flag và backup/PITR để xử lý sự
cố liên quan database.

## 9. Kiểm tra sau bootstrap

- Tạo PR docs-only và xác nhận `repository` + `gate` xanh,
  `backend`/`frontend` được skip.
- Sau khi bootstrap Laravel, xác nhận đủ hai lockfile và ba npm scripts trước
  khi mở PR.
- Merge một thay đổi vô hại vào `master`; xác nhận workflow deploy dùng đúng SHA
  và GitHub Environment `development`.
- Kiểm tra `readlink -f /var/www/mamo-ut/current`, HTTP health, queue worker,
  log ứng dụng và migration status.
- Thử health URL sai trong cửa sổ bảo trì để xác nhận rollback symlink hoạt động,
  sau đó khôi phục URL đúng.

Nếu workflow deploy bị skip, kiểm tra lần chạy CI có phải push trực tiếp phát
sinh sau merge trên `master` hay không. CI của pull request không bao giờ được
phép kích hoạt deployment.
