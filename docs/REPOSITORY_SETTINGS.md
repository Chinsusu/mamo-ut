# Thiết lập GitHub repository

Tài liệu này mô tả cấu hình quản trị cần áp dụng cho
`Chinsusu/mamo-ut`. Các thiết lập GitHub không được lưu đầy đủ trong Git, vì
vậy chủ repository cần đối chiếu lại sau khi bootstrap.

## 1. Bootstrap một lần cho repository trống

Repository chưa có commit thì chưa thể mở PR vào `master`. Chỉ trong lần đầu,
maintainer được tạo một empty root commit và push trực tiếp để làm base:

```bash
git switch --orphan master
git commit --allow-empty -m "chore: initialize repository"
git push -u origin master
git switch -c codex/project-standards-cicd
```

Xác nhận GitHub đã đặt `master` làm default branch trước khi push branch tính
năng. Sau đó mở PR cho toàn bộ nội dung bootstrap; không đưa source ứng dụng
vào empty root commit. Ngoại lệ direct-push kết thúc ngay khi `master` tồn tại.

## 2. Branch mặc định

- Default branch: `master`.
- Không cho phép xóa hoặc force-push `master`.
- Không push trực tiếp vào `master`; mọi thay đổi phải qua pull request.

## 3. Ruleset cho `master`

Tạo branch ruleset áp dụng cho `master`:

- yêu cầu pull request trước khi merge;
- tối thiểu 1 approval khi đã có ít nhất hai maintainer/reviewer hợp lệ;
- dismiss approval cũ khi có commit mới;
- yêu cầu xử lý toàn bộ review conversation;
- yêu cầu branch cập nhật với `master` trước khi merge;
- yêu cầu status check `CI / gate`;
- chặn force push và xóa branch;
- áp dụng cả với administrator, chỉ dùng bypass cho tình huống khẩn cấp có
  biên bản.

Merge method duy nhất: **squash merge**. Tắt merge commit và rebase merge để
lịch sử `master` giữ một commit cho mỗi pull request.

Nếu repository hiện chỉ có một maintainer là `Chinsusu`, chưa bật rule approval
không thể thỏa mãn. PR, `CI / gate`, conversation resolution và squash merge
vẫn bắt buộc; thêm reviewer thứ hai rồi bật approval rule sớm nhất có thể.

Chỉ bật ruleset sau khi empty root commit đã được push. Có thể bật trước PR
bootstrap đầu tiên; không cần chờ merge.

## 4. GitHub Environment `development`

Tạo Environment tên `development` và giới hạn deployment branch ở
`master`.

Variables:

| Tên | Giá trị gợi ý |
| --- | --- |
| `DEV_APP_ROOT` | `/var/www/mamo-ut` |
| `DEV_PUBLIC_URL` | URL để GitHub hiển thị cho môi trường dev |
| `DEV_HEALTHCHECK_URL` | URL health check của môi trường dev |
| `DEV_KEEP_RELEASES` | `5` |
| `DEV_RELOAD_SERVICES` | `false` trừ khi đã cấp sudo tối thiểu |

CD chạy cục bộ trên self-hosted runner của máy dev nên không cần lưu SSH
private key trong GitHub. SSH key cá nhân chỉ dùng từ máy quản trị để bootstrap
server; không đưa key đó vào repository hoặc runner.

## 5. Self-hosted runner

`10.1.1.109` là địa chỉ RFC1918 nên GitHub-hosted runner không thể truy cập
trực tiếp. Đăng ký repository-level runner ngay trên máy dev bằng user
`deploy`, gắn đủ các label:

```text
self-hosted, linux, x64, mamo-ut-dev
```

Runner không chạy bằng `root`, không được dùng cho workflow từ fork không tin
cậy và chỉ nhận deployment sau khi CI trên `master` đã thành công.

## 6. Bảo vệ bổ sung

- Bật Dependabot alerts, secret scanning và push protection nếu gói GitHub hỗ
  trợ.
- Bật tự động xóa head branch sau merge.
- Bật private vulnerability reporting.
- Chỉ cấp quyền Actions mặc định `contents: read`; workflow nào cần thêm quyền
  phải khai báo tại chính workflow đó.
- Với action bên thứ ba, dùng release channel được nhà cung cấp hỗ trợ và bật
  Dependabot; ưu tiên commit SHA khi có quy trình cập nhật tự động phù hợp.
