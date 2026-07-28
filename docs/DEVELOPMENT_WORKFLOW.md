# Development, Review và Delivery Workflow

Tài liệu này là quy trình chuẩn để phát triển, build, test, commit, mở PR, chạy CI/CD, merge và phát hành Mắm O Út.

## 1. Mô hình nhánh và quyền ghi

```text
issue/ticket
    │
    ▼
feat|fix|docs|.../<scope>
    │  local checks
    ▼
PR → required CI → review/approval
    │
    ▼
squash merge vào master
    │
    ├── deploy tự động lên DEV
    └── tag release có phê duyệt
```

- Repo duy nhất được phép ghi: `git@github.com:Chinsusu/mamo-ut.git`.
- Nhánh gốc: `master`.
- `master` được bảo vệ; cấm direct push và force push.
- Không duy trì nhánh `develop`. Mỗi nhánh ngắn hạn được tạo từ `master` và xóa sau merge.
- Merge mặc định: squash merge. Merge commit/rebase merge chỉ dùng khi maintainer có lý do cụ thể.

Branch protection đề xuất cho `master`:

1. Require a pull request before merging.
2. Require ít nhất 1 approval và dismiss stale approvals khi đã có reviewer
   thứ hai.
3. Require review từ Code Owners cho khu vực nhạy cảm.
4. Require conversation resolution.
5. Require branches to be up to date before merging.
6. Require status checks và không cho bỏ qua bởi administrator trong luồng thông thường.
7. Block force pushes và deletion.
8. Chỉ cho GitHub Actions/maintainer được tạo tag release.

## 2. Luồng phát triển tiêu chuẩn

### Bước 1 — Xác định phạm vi

Mỗi thay đổi bắt đầu từ issue/ticket hoặc mô tả công việc có:

- Kết quả mong đợi và tiêu chí chấp nhận.
- Phạm vi ngoài yêu cầu.
- Rủi ro dữ liệu, bảo mật, thanh toán, tồn kho hoặc vận hành.
- Kế hoạch test và rollback sơ bộ nếu thay đổi có rủi ro.

### Bước 2 — Kiểm tra repo và tạo nhánh

Trước mọi commit/push/PR/merge/deploy, kiểm tra đúng đích:

```bash
git remote -v
git branch --show-current
git status --short
gh repo view --json nameWithOwner,viewerPermission,url
```

Kết quả phải xác nhận `origin` và GitHub repo đều là `Chinsusu/mamo-ut`.

```bash
git switch master
git pull --ff-only origin master
git switch -c feat/checkout-address
```

Chọn tiền tố theo loại thay đổi:

```text
feat/       fix/       hotfix/      docs/
refactor/   test/      chore/       codex/
```

### Bước 3 — Cài đặt và chạy local

Lần đầu:

```bash
cp .env.example .env
composer install
php artisan key:generate
npm ci
php artisan migrate
npm run build
```

Mỗi khi đổi nhánh:

```bash
composer install
npm ci
php artisan migrate
php artisan optimize:clear
```

Chạy dev:

```bash
composer run dev
```

Nếu script tổng hợp chưa được cấu hình, chạy web server, queue worker và Vite trong các terminal riêng:

```bash
php artisan serve
php artisan queue:work --tries=3
npm run dev
```

### Bước 4 — Vòng lặp build/test

Trong lúc phát triển, chạy test hẹp trước:

```bash
php artisan test --filter=Order
```

Trước commit và trước PR, chạy cổng kiểm tra thống nhất:

```bash
./scripts/ci.sh
```

Trong giai đoạn bootstrap nếu script chưa tồn tại, chạy tối thiểu:

```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --no-progress
php artisan test
npm run build
```

PR bootstrap ứng dụng phải thêm PHPStan/Larastan và cấu hình tương ứng;
`scripts/ci.sh` bắt buộc chạy phân tích tĩnh khi `composer.json` xuất hiện.
Frontend phải có các script `lint`, `test`, `build`. Không sửa lỗi CI bằng cách
giảm mức kiểm tra, skip test hoặc nới rule nếu chưa có lý do được review.

### Bước 5 — Commit

Review diff trước khi stage:

```bash
git status --short
git diff
git diff --check
```

Stage có chọn lọc:

```bash
git add path/to/file.php tests/Feature/RelevantTest.php
git diff --cached --check
git diff --cached --stat
```

Commit theo Conventional Commits:

```bash
git commit -m "feat(checkout): validate two-level shipping address"
```

Một commit nên là một đơn vị logic có thể hiểu và revert. Không trộn formatter toàn repo, dependency update hoặc refactor lớn vào một fix nhỏ.

### Bước 6 — Đồng bộ và mở PR

```bash
git fetch origin
git rebase origin/master
./scripts/ci.sh
git push -u origin HEAD
```

Mở PR đúng repo:

```bash
PR_BODY_FILE="$(mktemp)"
cp .github/PULL_REQUEST_TEMPLATE.md "$PR_BODY_FILE"
"${EDITOR:-vi}" "$PR_BODY_FILE"
gh pr create \
  --repo Chinsusu/mamo-ut \
  --base master \
  --head "$(git branch --show-current)" \
  --title "feat(checkout): validate two-level shipping address" \
  --body-file "$PR_BODY_FILE"
rm "$PR_BODY_FILE"
```

Không gửi nguyên checklist chưa chỉnh sửa. Có thể dùng `gh pr create --web` nếu
muốn điền template trực tiếp trên GitHub.

## 3. Required CI checks

Branch protection chỉ yêu cầu check tổng hợp **`CI / gate`**. Check này luôn
chạy và chỉ xanh khi mọi job áp dụng cho trạng thái repository đều thành công:

| Check | Nội dung |
|---|---|
| `CI / repository` | Whitespace, secret cơ bản và cú pháp shell |
| `CI / backend` | Composer validation/audit, PHP syntax, Pint, PHPStan/Larastan, migration sạch và Laravel test trên MySQL 8.4 |
| `CI / frontend` | `npm ci`, lint, test và production build bằng Node.js 24 LTS |
| `CI / gate` | Tổng hợp bắt buộc cho branch protection |

Khuyến nghị thêm khi công cụ đã sẵn sàng:

- Static analysis.
- Frontend unit/component tests.
- End-to-end smoke test cho checkout/admin.
- Kiểm tra accessibility.
- Secret scanning, dependency review và CodeQL.

CI phải dùng file lock, cache theo lockfile và không sử dụng secret sản xuất. Dịch vụ ngoài phải chạy sandbox hoặc fake.

PR không được merge khi `CI / gate` hoặc job thành phần bị hủy, skipped bất
thường hay flaky. Flaky test được coi là lỗi cần xử lý, không phải lý do rerun
đến khi xanh.

## 4. Review và phê duyệt

### Trách nhiệm của tác giả

- Tự review diff trước khi yêu cầu review.
- Cung cấp bằng chứng test, ảnh UI và chỉ dẫn reviewer tái hiện.
- Đánh dấu rõ migration, config, feature flag, job queue, cron hoặc thay đổi vận hành.
- Trả lời và resolve mọi conversation sau khi reviewer xác nhận.
- Re-request review sau thay đổi làm mất hiệu lực approval.

### Trách nhiệm của reviewer

Review theo mức rủi ro, tập trung vào:

1. Hành vi có khớp tiêu chí chấp nhận.
2. Validation, authorization và bảo vệ dữ liệu.
3. Transaction, idempotency, race condition và khả năng retry.
4. Tính tương thích của schema/migration khi triển khai.
5. Hiệu năng query, N+1, cache và queue.
6. Test có thất bại trước fix và bao phủ failure path.
7. Khả năng quan sát, cảnh báo và rollback.
8. Accessibility/responsive đối với thay đổi UI.

Không approve chỉ dựa vào CI xanh.

### Approval tối thiểu

- Thay đổi thông thường: 1 approval khi có reviewer thứ hai.
- Auth, payment, order, inventory, PII, migration phá vỡ, CI/CD hoặc hạ tầng:
  1 approval từ maintainer/code owner có chuyên môn tương ứng.
- Người viết không tự approve PR của mình.
- Nếu repository chỉ có một maintainer, không bật approval rule bất khả thi.
  PR, `CI / gate` và review diff vẫn bắt buộc; bổ sung reviewer thứ hai sớm nhất
  có thể.

## 5. Merge

Ngay trước merge:

```bash
git remote -v
git branch --show-current
git status --short
gh repo view --json nameWithOwner,viewerPermission,url
gh pr checks --repo Chinsusu/mamo-ut <PR_NUMBER>
gh pr diff --repo Chinsusu/mamo-ut <PR_NUMBER>
```

Điều kiện:

- Required checks đều thành công.
- Đủ approval và không có stale approval khi approval rule đang được bật.
- Mọi conversation đã resolve.
- Không conflict và PR cập nhật với `master`.
- Tiêu đề PR theo Conventional Commits.
- Definition of Done hoàn tất.

Lệnh mặc định:

```bash
gh pr merge \
  --repo Chinsusu/mamo-ut \
  <PR_NUMBER> \
  --squash \
  --delete-branch
```

Không merge khi đang có incident liên quan, migration chưa được đánh giá hoặc rollback chưa khả thi.

## 6. Deploy môi trường DEV

Máy `root@10.1.1.109` là môi trường phát triển, **không phải production**. Xác
thực quản trị chỉ dùng SSH key; tắt password authentication nếu hạ tầng cho
phép. Private key chỉ nằm trên máy quản trị dùng cho bootstrap, không lưu trong
repo, GitHub Environment hoặc self-hosted runner. CD chạy cục bộ bằng user
`deploy` trên máy dev.

Luồng đề xuất:

1. Squash merge vào `master`.
2. CI build/test lại đúng SHA đã merge.
3. Workflow deploy dùng GitHub Environment `development`.
4. Self-hosted runner trên máy dev checkout đúng SHA đã được CI kiểm tra.
5. Tạo release nguyên tử, chạy migration, tạo cache và restart worker.
6. Chạy smoke checks.
7. Ghi lại SHA, thời gian, người/automation triển khai và kết quả.

Trước khi triển khai trên server:

```bash
git remote -v
git branch --show-current
git status --short
```

Không pull đè nếu có tracked file bị sửa trên server. Dừng deploy và xử lý drift bằng PR hoặc worktree riêng. Chi tiết đường dẫn ứng dụng, tên service và script deploy phải được cấu hình trong runbook/CI, không hardcode private key.

Triển khai DEV không được xem là phê duyệt production. Dự án hiện không có production target trong phạm vi quy trình này.

## 7. Hotfix và rollback

### Hotfix

Hotfix vẫn phải đi qua PR:

```bash
git switch master
git pull --ff-only origin master
git switch -c hotfix/payment-callback
```

Quy trình có thể ưu tiên reviewer và rút ngắn thời gian chờ, nhưng không bỏ required CI, approval hoặc smoke test. Sau merge, deploy DEV và xác minh hành vi gây sự cố.

### Rollback ứng dụng

Ưu tiên revert commit squash bằng PR:

```bash
git switch master
git pull --ff-only origin master
git switch -c revert/payment-callback
git revert <SQUASH_COMMIT_SHA>
```

Mở PR `revert(scope): ...`, chạy đủ CI, merge và deploy. Trong incident nghiêm trọng, maintainer có thể redeploy artefact/SHA tốt gần nhất trước, sau đó vẫn phải tạo PR revert để Git và server hội tụ.

### Rollback dữ liệu

- Không chạy `migrate:rollback` mù quáng trên database chia sẻ.
- Đánh giá migration có đảo ngược an toàn và dữ liệu mới có bị mất hay không.
- Với thay đổi dữ liệu, ưu tiên forward-fix hoặc migration bù.
- Restore backup/PITR chỉ theo runbook, sau khi xác nhận phạm vi mất dữ liệu.

Sau rollback phải chạy smoke test và lập ghi chú nguyên nhân/sự cố.

## 8. Release và tag

Áp dụng Semantic Versioning:

- `MAJOR`: thay đổi không tương thích.
- `MINOR`: thêm tính năng tương thích ngược.
- `PATCH`: sửa lỗi tương thích ngược.

Tag có dạng `vMAJOR.MINOR.PATCH`, ví dụ `v1.4.2`, và chỉ trỏ tới commit trên `master`.

Quy trình:

1. Đảm bảo CI của `master` xanh và DEV smoke test thành công.
2. Tổng hợp changelog từ các squash commit kể từ tag trước.
3. Xác định version theo thay đổi thực tế, gồm breaking changes.
4. Tạo annotated tag và push đúng repo:

```bash
git switch master
git pull --ff-only origin master
git tag -a v1.4.2 -m "Release v1.4.2"
git push origin v1.4.2
```

5. Tạo GitHub Release kèm changelog, migration/config notes và rollback reference.

Không di chuyển hoặc ghi đè tag đã phát hành. Nếu release lỗi, tạo version patch mới hoặc đánh dấu release bị thu hồi.

## 9. Definition of Done

Một thay đổi chỉ được coi là hoàn tất khi:

- [ ] Tiêu chí chấp nhận đã đáp ứng và phạm vi ngoài yêu cầu không bị kéo vào.
- [ ] Code tuân thủ `CONTRIBUTING.md`, không có secret hoặc dữ liệu thật.
- [ ] Validation và authorization được thực hiện phía server.
- [ ] Test mới/cập nhật bao phủ happy path, failure path và regression phù hợp.
- [ ] Pint, test suite, frontend build và mọi required CI đều xanh.
- [ ] Migration tương thích triển khai, có index/ràng buộc phù hợp và kế hoạch rollback/forward-fix.
- [ ] Không có N+1/query bất hợp lý trên đường nóng.
- [ ] Tài liệu, `.env.example`, config và runbook được cập nhật khi cần.
- [ ] UI đã kiểm tra responsive, keyboard và accessibility nếu bị ảnh hưởng.
- [ ] Log/metric/smoke check đủ để phát hiện lỗi sau deploy.
- [ ] PR có bằng chứng kiểm thử, rủi ro và hướng rollback.
- [ ] Đủ approval khi rule đang bật; mọi conversation đã resolve và required
      checks xanh.
- [ ] PR được squash merge; nhánh đã xóa.
- [ ] DEV deploy đúng SHA và smoke test thành công nếu thay đổi cần triển khai.

## 10. Xử lý ngoại lệ

Nếu không thể đáp ứng một bước bắt buộc:

1. Không tự bỏ qua.
2. Ghi rõ lý do, rủi ro và biện pháp bù trong PR.
3. Yêu cầu maintainer chấp thuận bằng văn bản.
4. Tạo issue theo dõi với owner và thời hạn.

Ngoại lệ không mặc nhiên trở thành tiêu chuẩn cho các PR tiếp theo.
