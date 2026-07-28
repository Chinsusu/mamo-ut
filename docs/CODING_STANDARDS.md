# Coding Standards — Mắm O Út

Tài liệu này là chuẩn kỹ thuật bắt buộc cho mã nguồn của dự án Mắm O Út.

Stack mục tiêu:

- PHP 8.3;
- Laravel 13;
- Blade, Livewire 4 và Alpine.js;
- Tailwind CSS 4;
- Filament 5;
- MySQL 8.4 LTS.

Các từ **MUST**, **MUST NOT**, **SHOULD** và **MAY** biểu thị mức độ bắt buộc. Nếu cần phá lệ,
Pull Request (PR) phải nêu rõ lý do, rủi ro và cách hoàn trả về chuẩn.

## 1. Nguyên tắc nền tảng

1. Ưu tiên tính đúng đắn, bảo mật và khả năng vận hành hơn sự ngắn gọn.
2. Dùng khả năng có sẵn của Laravel trước khi thêm abstraction hoặc dependency mới.
3. Mỗi lớp có một trách nhiệm rõ ràng; không đặt nghiệp vụ trong Controller, Blade,
   Livewire component, Filament Resource hoặc Job.
4. Dữ liệu đầu vào từ HTTP, Livewire, queue, webhook và CLI luôn là dữ liệu không tin cậy.
5. Luồng có side effect phải an toàn khi chạy lại. Queue của Laravel có ngữ nghĩa
   **at-least-once**, không được giả định một Job chỉ chạy đúng một lần.
6. Tiền, tồn kho, thanh toán và trạng thái đơn hàng phải được bảo vệ bởi ràng buộc ở cả
   tầng ứng dụng lẫn cơ sở dữ liệu.
7. Code dễ đọc quan trọng hơn code “thông minh”. Chỉ trừu tượng hóa khi đã có nhu cầu thực.

## 2. Định dạng và công cụ chất lượng

- Mọi file PHP do dự án sở hữu MUST bắt đầu bằng `declare(strict_types=1);`, ngay sau `<?php`.
  Blade template và file do công cụ sinh ra mà việc thêm khai báo sẽ phá cú pháp là ngoại lệ.
- PHP tuân theo PSR-12 và cấu hình Laravel Pint của repo.
- Dòng code SHOULD không vượt quá 120 ký tự. Có thể vượt với URL, chuỗi không thể ngắt hoặc
  cấu trúc do formatter tạo.
- CI là nguồn xác nhận cuối cùng. Code chỉ được merge khi formatter, static analysis, test và
  frontend build đều đạt.
- Không được vô hiệu hóa rule bằng `@phpstan-ignore-*`, `eslint-disable`, `@ts-ignore` hoặc
  suppression tương tự nếu không có comment giải thích và issue theo dõi.
- Dependency mới MUST có lý do, giấy phép phù hợp, được duy trì và không trùng chức năng framework.

Các lệnh chuẩn của repo SHOULD được gom sau các script ổn định, ví dụ:

```bash
composer lint
composer analyse
composer test
npm run lint
npm run build
```

Tên script cụ thể do file workflow của repo định nghĩa; developer không được dựa vào lệnh chỉ có
trên máy cá nhân.

## 3. Cấu trúc ứng dụng và hướng phụ thuộc

Cấu trúc khuyến nghị:

```text
app/
├── Actions/
│   ├── Catalog/
│   ├── Checkout/
│   ├── Orders/
│   └── Payments/
├── Data/
├── Domain/
│   └── <Domain>/
│       ├── Enums/
│       ├── Exceptions/
│       ├── Services/
│       └── ValueObjects/
├── Events/
├── Filament/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Integrations/
├── Jobs/
├── Listeners/
├── Livewire/
├── Models/
├── Policies/
├── Queries/
└── Support/
```

Không cần tạo thư mục rỗng. Chỉ tạo một lớp hoặc abstraction khi có use case sử dụng nó.

Hướng phụ thuộc chuẩn:

```text
HTTP / Livewire / Filament / CLI / Job
                  ↓
          Validation + Authorization
                  ↓
                 DTO
                  ↓
               Action
                  ↓
       Domain / Model / Integration
```

Quy tắc:

- UI adapter MAY gọi Action hoặc Query, nhưng Action/Domain MUST NOT phụ thuộc Controller,
  Request, Livewire, Blade hoặc Filament.
- Job và Listener SHOULD gọi lại cùng Action mà HTTP sử dụng; không sao chép nghiệp vụ.
- Model Eloquent chứa relationship, cast, local scope và hành vi cục bộ của entity. Workflow
  nhiều bước hoặc nhiều aggregate thuộc Action/Domain Service.
- Repository không phải mặc định. Chỉ tạo Repository khi thực sự cần thay persistence,
  gom một contract truy vấn phức tạp hoặc cô lập integration; không bọc mọi lời gọi Eloquent.
- Query đọc phức tạp MAY đặt trong `app/Queries`; Query không tạo side effect.
- Integration với dịch vụ ngoài MUST nằm sau một interface do ứng dụng sở hữu, với adapter thật
  và fake dùng cho test.

## 4. Chuẩn PHP

### 4.1 Kiểu dữ liệu

- Mọi property, parameter và return value MUST có type khi PHP cho phép.
- Dùng union/nullable type có chủ đích; không dùng `mixed` nếu có thể mô tả type cụ thể.
- Dùng PHPDoc cho generic collection, array shape, template hoặc điều kiện không biểu diễn được
  bằng type system; không lặp lại thông tin đã có trong chữ ký.
- DTO và Value Object SHOULD là `final readonly`.
- Dùng backed Enum cho tập trạng thái hữu hạn. Không dùng magic string rải rác.
- Không dùng `float` cho tiền, thuế, chiết khấu hoặc dữ liệu cần tính toán chính xác.
- `CarbonImmutable` được ưu tiên trong nghiệp vụ để tránh thay đổi thời gian ngoài ý muốn.

Ví dụ:

```php
<?php

declare(strict_types=1);

namespace App\Data\Checkout;

final readonly class PlaceOrderData
{
    /**
     * @param list<PlaceOrderItemData> $items
     */
    public function __construct(
        public int $customerId,
        public array $items,
        public string $idempotencyKey,
    ) {
    }
}
```

### 4.2 Control flow và lỗi

- Ưu tiên guard clause để giảm nesting.
- Không bắt `Throwable`/`Exception` chỉ để bỏ qua lỗi.
- Chỉ bắt exception khi có thể bổ sung ngữ cảnh, chuyển sang domain exception hoặc phục hồi an toàn.
- Domain exception phải có tên mô tả điều kiện nghiệp vụ, ví dụ
  `InsufficientInventory` hoặc `InvalidOrderTransition`.
- Message hiển thị cho người dùng không được làm lộ stack trace, SQL, đường dẫn hệ thống hoặc
  chi tiết integration.
- Không dùng exception cho nhánh điều khiển thông thường có thể biểu diễn rõ bằng result/value.

### 4.3 Dependency injection

- Constructor injection là mặc định cho service/action/integration.
- Facade được phép ở lớp adapter hoặc với dịch vụ Laravel đơn giản (`DB`, `Cache`, `Log`), nhưng
  domain logic cần test độc lập SHOULD phụ thuộc contract rõ ràng.
- Không dùng service locator tùy ý (`app(...)`) bên trong nghiệp vụ.
- Lớp SHOULD là `final` nếu không được thiết kế để kế thừa. Không đánh dấu `final` cho lớp mà
  framework cần proxy hoặc mở rộng.

## 5. Quy ước đặt tên

| Thành phần | Quy ước | Ví dụ |
|---|---|---|
| Class/Enum | `StudlyCase`, danh từ rõ nghĩa | `OrderStatus`, `Money` |
| Action | động từ + đối tượng | `PlaceOrder`, `CapturePayment` |
| Method | `camelCase`, bắt đầu bằng động từ | `calculateTotal()` |
| Boolean | `is/has/can/should` | `isPaid`, `canCancel()` |
| DTO | hậu tố `Data` | `PlaceOrderData` |
| Form Request | hậu tố `Request` | `StoreAddressRequest` |
| Job | mô tả công việc, hậu tố tùy chọn `Job` | `SendOrderConfirmation` |
| Event | sự kiện đã xảy ra, thì quá khứ | `OrderPlaced` |
| Listener | hành động khi nhận event | `ReserveInventory` |
| Policy | model + `Policy` | `OrderPolicy` |
| DB table | danh từ số nhiều `snake_case` | `order_items` |
| DB column | `snake_case` | `paid_at`, `amount_vnd` |
| Route name | phân cấp bằng dấu chấm | `checkout.orders.store` |
| Config key | `snake_case` | `payment.webhook_secret` |
| Env variable | `UPPER_SNAKE_CASE` | `PAYMENT_WEBHOOK_SECRET` |
| Blade component | `kebab-case` | `<x-order-summary />` |
| Livewire component | tên theo chức năng | `CheckoutForm` |

Tên phải phản ánh ngôn ngữ nghiệp vụ. Tránh tên chung chung như `Helper`, `Manager`, `Util`,
`CommonService`, `handleData()` hoặc `process()`.

## 6. Controller, Action, Service và DTO

### Controller / Livewire / Filament

- Controller chỉ nhận request, gọi authorization/validation, tạo DTO, gọi Action và trả response.
- Controller SHOULD là invokable nếu endpoint chỉ có một nhiệm vụ.
- Livewire component giữ state giao diện và điều phối tương tác; không thực thi transaction hoặc
  tính toán nghiệp vụ quan trọng trong `render()`.
- Filament Resource/Page chỉ cấu hình UI quản trị và gọi Action/Policy dùng chung.
- Không truyền nguyên `Request`, Livewire component hoặc Filament form state vào Domain/Action.

### DTO

- DTO là dữ liệu đã chuẩn hóa đi qua ranh giới lớp.
- DTO không tự truy cập DB, container hoặc global state.
- Không dùng mảng không định hình cho input nghiệp vụ quan trọng.
- Chỉ truyền field đã được validation; không dùng `$request->all()` hoặc state chưa lọc.

### Action

- Action biểu diễn một use case có tên nghiệp vụ và có một public entry point (`execute()` hoặc
  `handle()`; một module phải thống nhất một tên).
- Action sở hữu transaction của use case.
- Action MAY phối hợp model, domain service, event và integration contract.
- Action không trả HTTP response hoặc view.

### Domain Service

- Dùng khi quy tắc không thuộc tự nhiên về một model/value object hoặc cần phối hợp nhiều entity.
- Service không trở thành nơi chứa mọi logic; tên phải mô tả capability cụ thể.
- Hàm tính toán thuần SHOULD tách khỏi I/O để test đơn giản và xác định.

## 7. Validation và authorization

- Endpoint HTTP MUST dùng Form Request cho input không tầm thường.
- Livewire MUST dùng typed property cùng rules hoặc Livewire Form Object.
- Validation ở client chỉ hỗ trợ UX; server validation luôn bắt buộc.
- Dùng `$request->validated()` hoặc `$request->safe()`, không mass assign dữ liệu chưa lọc.
- Chuẩn hóa dữ liệu trong `prepareForValidation()` chỉ khi việc chuẩn hóa không làm thay đổi ý
  nghĩa người dùng nhập.
- Authorization MUST thực hiện ở mọi entry point: HTTP, Livewire, Filament, CLI quản trị và Job
  nhạy cảm. Dùng Policy/Gate thay vì `if ($user->role === ...)` rải rác.
- Validation không thay thế authorization. Mọi truy vấn theo ID phải kiểm tra quyền sở hữu/phạm vi
  để ngăn IDOR.
- Không tin giá tiền, tổng tiền, tồn kho, phí vận chuyển, vai trò hoặc trạng thái do client gửi.
  Server phải tự tính lại từ nguồn tin cậy.
- Route model binding không tự chứng minh quyền truy cập; vẫn phải gọi Policy.
- Phân biệt đúng mã phản hồi: chưa đăng nhập `401`, không có quyền `403`, sai input `422`,
  xung đột trạng thái `409` khi phù hợp.

## 8. Eloquent và cơ sở dữ liệu

### 8.1 Migration và schema

- Primary key mặc định là `BIGINT UNSIGNED`. Foreign key phải cùng kiểu.
- Dùng foreign key, `NOT NULL`, unique index và check constraint khi DB có thể bảo vệ invariant.
- Index phải xuất phát từ access pattern thực tế; kiểm tra `EXPLAIN` cho truy vấn quan trọng.
- Mọi foreign key và cột thường dùng để lọc/sắp xếp/join cần được xem xét index.
- Không sửa migration đã được merge và chạy trên môi trường dùng chung. Tạo migration mới.
- Thay đổi schema phải theo **expand → migrate/backfill → switch → contract** để deploy ngược
  tương thích. Không xóa/đổi tên cột đang được phiên bản ứng dụng hiện tại sử dụng.
- Migration schema phải xác định; không gọi API ngoài hoặc phụ thuộc dữ liệu biến động.
- Backfill lớn đặt trong command/job vận hành có checkpoint, không chạy vòng lặp lớn trong migration.
- Rollback chỉ được cung cấp khi an toàn. Nếu không thể đảo ngược, ghi rõ trong migration và runbook.
- Tránh native DB `ENUM` khi trạng thái còn có thể phát triển; ưu tiên `VARCHAR` có PHP backed Enum
  và constraint phù hợp.
- JSON chỉ dùng cho dữ liệu thực sự linh hoạt/metadata; field cần query, join hoặc constraint phải là
  cột riêng.
- Soft delete chỉ dùng khi nghiệp vụ cần khôi phục/lưu vết. Không xem soft delete là giải pháp audit.

### 8.2 Eloquent

- Khai báo rõ `$fillable` hoặc `$guarded`; không dùng unguarded input.
- Khai báo `casts()` cho boolean, enum, immutable datetime, encrypted value và cấu trúc JSON.
- Relationship method MUST có return type phù hợp.
- Ngăn N+1 bằng eager loading có chủ đích. Không bật eager loading toàn cục chỉ để che truy vấn sai.
- Chỉ select cột cần dùng ở truy vấn lớn.
- Không query DB trong Blade loop, accessor thường xuyên gọi hoặc `render()` của Livewire.
- Global scope chỉ dùng khi hành vi thực sự toàn cục và không gây kết quả bất ngờ.
- Model event/observer không nên che giấu workflow quan trọng. Side effect nghiệp vụ phải hiện rõ
  trong Action và có test.

### 8.3 Thời gian

- Mọi thời điểm lưu trong DB và xử lý nội bộ MUST ở UTC.
- Cấu hình PHP/Laravel, worker, scheduler, MySQL connection và server phải nhất quán UTC.
- Chuyển sang múi giờ người dùng (`Asia/Ho_Chi_Minh` khi phù hợp) chỉ ở ranh giới hiển thị/nhập liệu.
- Field thời điểm có hậu tố `_at`; ngày không có thời gian dùng `_date`.
- Dùng clock/fake time trong test; không để test phụ thuộc giờ hệ thống thực.
- Không so sánh ngày/giờ bằng chuỗi đã format.

### 8.4 Tiền và số lượng

- VND được lưu bằng `BIGINT UNSIGNED` theo đơn vị đồng; tên cột có hậu tố `_vnd` hoặc chuẩn
  `amount_minor` kèm `currency CHAR(3)` nếu hỗ trợ đa tiền tệ.
- PHP dùng `int` và Value Object `Money`; không dùng `float`.
- Quy tắc làm tròn, thứ tự áp dụng giảm giá/thuế/phí và thời điểm chốt giá phải được định nghĩa,
  test và không phụ thuộc giao diện.
- Snapshot giá/tên/SKU cần thiết phải được lưu trên order item; lịch sử đơn hàng không phụ thuộc
  dữ liệu sản phẩm có thể thay đổi.
- Số lượng tồn kho dùng integer và không được âm nếu nghiệp vụ không cho phép.

## 9. Transaction, concurrency và idempotency

- Transaction bắt đầu/kết thúc trong Action, không nằm trong Controller, Blade, Livewire hoặc Job.
- Transaction phải ngắn; không gọi API, gửi email, upload file hoặc thực hiện I/O mạng bên trong.
- Khi đọc–sửa dữ liệu cạnh tranh, dùng `lockForUpdate()`, conditional update hoặc optimistic locking.
- Luôn khóa record theo một thứ tự xác định để giảm deadlock.
- Có thể retry transaction khi deadlock bằng số lần giới hạn; không retry vô hạn.
- Dispatch event/job có side effect sau khi commit (`afterCommit`) để worker không thấy dữ liệu
  chưa commit.
- Ràng buộc unique trong DB là lớp bảo vệ cuối cùng; kiểm tra `exists()` trước insert không đủ an toàn.

Mọi thao tác có thể được gửi lại MUST có idempotency:

- đặt đơn;
- callback/webhook thanh toán;
- ghi nhận giao dịch tồn kho;
- refund;
- import/export hoặc Job có side effect ngoài hệ thống.

Yêu cầu tối thiểu:

1. Nhận idempotency key/event ID từ nguồn phù hợp.
2. Có unique constraint theo phạm vi nghiệp vụ.
3. Lưu trạng thái và kết quả đã xử lý.
4. Cùng key + cùng payload trả lại kết quả tương đương.
5. Cùng key + payload khác phải bị từ chối và ghi cảnh báo.

Không dùng cache lock hoặc `ShouldBeUnique` làm lớp bảo vệ duy nhất cho tiền/tồn kho. Với event cần
đảm bảo phát sau commit và không được mất, xem xét transactional outbox.

State transition phải được kiểm tra rõ ràng; không gán trực tiếp trạng thái đơn/thanh toán từ input.

## 10. Queue, event, scheduler và cache

### Queue / Job

- Job MUST idempotent vì có thể chạy lại.
- Constructor Job chỉ chứa ID/scalar/DTO nhỏ; không serialize object lớn hoặc dữ liệu nhạy cảm.
- Mỗi Job quy định hợp lý `tries`, `timeout`, `backoff` và `maxExceptions`.
- Retry chỉ áp dụng cho lỗi tạm thời. Lỗi validation hoặc invariant phải fail ngay.
- `timeout` của Job phải ngắn hơn `retry_after` của queue.
- Job quan trọng phải có `failed()`/monitoring phù hợp và runbook retry thủ công.
- Không log toàn bộ payload nếu chứa PII, token hoặc secret.
- Queue phải được phân nhóm theo mức độ ưu tiên; tác vụ chậm không được chặn email hoặc thanh toán.

### Event / Listener

- Event đặt tên ở thì quá khứ và mô tả sự kiện đã xảy ra.
- Event không thay thế lời gọi hàm khi cần kết quả đồng bộ.
- Listener không được phụ thuộc vào thứ tự trừ khi orchestration thể hiện thứ tự đó rõ ràng.
- Side effect không quan trọng với transaction SHOULD chạy qua queue sau commit.

### Scheduler

- Command định kỳ phải an toàn khi chạy lại và dùng `withoutOverlapping()`/single-server lock khi cần.
- Tác vụ dài cần checkpoint, metric và khả năng tiếp tục.

### Cache

- Key cache có namespace và version, ví dụ `catalog:v2:product:{id}`.
- Mọi cache có TTL trừ khi có chiến lược invalidation được chứng minh.
- Invalidate sau commit.
- Không lưu authorization decision hoặc dữ liệu nhạy cảm lâu hơn vòng đời cho phép.
- Không dựa vào cache để đảm bảo invariant nghiệp vụ.

## 11. Bảo mật, secret và logging

### Secret và cấu hình

- MUST NOT commit `.env`, SSH private key, API key, mật khẩu, token, certificate private key hoặc
  credential dưới mọi hình thức.
- `.env.example` chỉ có key và giá trị giả an toàn.
- Secret của CI/CD và server nằm trong secret store/quyền file phù hợp, có chủ sở hữu và lịch xoay.
- Không hard-code host, credential hoặc khác biệt môi trường; dùng config file đọc từ `env()` tại
  tầng config. Không gọi `env()` ngoài `config/*.php`.
- SSH host key phải được xác minh; không dùng `StrictHostKeyChecking=no` trong pipeline.

### Bảo vệ request và dữ liệu

- Giữ CSRF protection cho request dùng session.
- Escape output bằng `{{ }}`. Chỉ dùng `{!! !!}` với HTML đã sanitize từ nguồn đáng tin cậy và
  phải giải thích tại chỗ.
- Query dùng Eloquent/query binding; không nối input vào raw SQL.
- Endpoint đăng nhập, checkout, coupon, webhook và thao tác nhạy cảm phải rate-limit phù hợp.
- Webhook phải kiểm tra chữ ký trên raw body, timestamp/replay window và unique event ID trước xử lý.
- Upload phải kiểm tra kích thước, MIME thực, extension, tên file ngẫu nhiên và lưu ngoài public path
  nếu không cần public. Không tin MIME do browser gửi.
- HTTP client tới URL do người dùng ảnh hưởng phải có allowlist/validation chống SSRF, timeout và
  giới hạn redirect.
- Cookie xác thực phải có `Secure`, `HttpOnly`, `SameSite` phù hợp trên HTTPS.
- Admin/Filament phải dùng Policy, rate limit và cơ chế xác thực mạnh; MFA SHOULD bật cho tài khoản
  có quyền cao.
- Chạy kiểm tra dependency vulnerability trong CI và xử lý theo mức độ rủi ro.

### Logging và dữ liệu cá nhân

- Log có cấu trúc, kèm `request_id`/`correlation_id`, entity ID không nhạy cảm và tên operation.
- Chọn đúng level: `debug` cho chẩn đoán cục bộ, `info` cho mốc nghiệp vụ,
  `warning` cho bất thường có thể phục hồi, `error` cho thao tác thất bại cần xử lý.
- MUST NOT log mật khẩu, session/cookie, authorization header, access token, OTP, private key,
  webhook secret, số thẻ hoặc payload thanh toán đầy đủ.
- Mask email, số điện thoại, địa chỉ và PII khi không cần giá trị đầy đủ.
- Không dùng log làm audit trail duy nhất. Thay đổi quản trị/trạng thái quan trọng cần audit record
  bất biến với actor, action, target và thời gian UTC.
- Không nuốt exception. Báo lỗi một lần ở boundary phù hợp, tránh log trùng cùng một exception.

## 12. Blade, Livewire, Alpine, Tailwind và Filament

### Blade

- Dùng semantic HTML trước khi thêm ARIA.
- Tách UI lặp lại thành Blade component có API nhỏ, typed prop/default rõ ràng.
- View không query DB, gọi service hoặc thay đổi state.
- Không đặt business rule quan trọng trong directive/template.
- Mọi form có CSRF, method đúng và hiển thị lỗi gắn với field.

### Livewire 4

- Public property là input không tin cậy; validate và authorize lại trong action.
- Không tin ID/model/state đã hydrate từ client cho giá, quyền hoặc ownership.
- Dùng `wire:key` ổn định và duy nhất trong danh sách; không dùng index nếu danh sách có thể đổi.
- Tránh query hoặc tính toán nặng trong `render()`. Dùng eager loading, computed property/cache có
  phạm vi rõ ràng hoặc Query class.
- Dùng loading/disabled state để tránh submit lặp, nhưng server vẫn phải idempotent.
- Component lớn phải tách theo capability, không theo từng phần tử trang một cách máy móc.
- Test component cho validation, authorization, event và trạng thái quan trọng.

### Alpine.js

- Alpine chỉ quản lý tương tác nhỏ phía client; nghiệp vụ và authorization vẫn ở server.
- State dùng cục bộ, tên rõ nghĩa; tránh global store nếu không thực sự chia sẻ.
- Dùng `x-cloak` để tránh flash, quản lý focus khi mở/đóng modal và cleanup listener/timer.
- Không chèn HTML chưa sanitize bằng `x-html`.
- Tôn trọng `prefers-reduced-motion`.

### Tailwind CSS 4

- Ưu tiên token/theme của dự án cho màu, spacing, radius, typography và breakpoint.
- Không lặp nhóm utility dài; trích Blade component hoặc class semantic ở đúng mức.
- Arbitrary value chỉ dùng khi không có token phù hợp và SHOULD có lý do trong PR.
- Không xây class Tailwind bằng chuỗi động khiến compiler không phát hiện; dùng map/safelist rõ ràng.
- Responsive bắt đầu từ mobile; kiểm tra overflow ở kích thước màn hình nhỏ.
- Dark mode chỉ triển khai khi component có đầy đủ trạng thái, không vá từng trang.

### Filament 5

- Resource/Page/Widget là adapter quản trị, không chứa transaction hoặc workflow nghiệp vụ.
- Query của table phải scope theo tenant/quyền và tránh N+1.
- Mọi action quản trị phải authorize ở server; ẩn nút không phải là authorization.
- Destructive action cần confirm, thông báo tác động và audit log.
- Bulk action phải xử lý theo chunk/queue khi dữ liệu lớn, có báo cáo lỗi một phần.
- Dùng Action/Policy/DTO chung với phần ứng dụng thay vì sao chép quy tắc.

## 13. Accessibility và trải nghiệm

Mục tiêu bắt buộc là WCAG 2.2 mức AA cho luồng người dùng và quản trị quan trọng.

- Mọi chức năng phải dùng được bằng bàn phím, có thứ tự focus hợp lý và focus indicator nhìn rõ.
- Mỗi input có label chương trình; placeholder không thay label.
- Lỗi form phải được mô tả bằng text, liên kết với field và được thông báo phù hợp cho screen reader.
- Nút dùng `<button>`, liên kết điều hướng dùng `<a>`; không dùng `div` click thay control.
- Ảnh nội dung có `alt` phù hợp; ảnh trang trí dùng `alt=""`.
- Modal giữ focus, có tên truy cập, đóng bằng Escape khi an toàn và trả focus về trigger.
- Nội dung thay đổi động quan trọng có live region phù hợp, không thông báo quá mức.
- Không truyền đạt trạng thái chỉ bằng màu. Tương phản tối thiểu 4.5:1 cho chữ thường và 3:1 cho
  chữ lớn/thành phần giao diện theo WCAG AA.
- Kích thước target tương tác và khoảng cách phải phù hợp WCAG 2.2; ưu tiên ít nhất 44 × 44 CSS px
  cho thao tác cảm ứng chính.
- Animation phải có phương án giảm chuyển động; không dùng hiệu ứng nhấp nháy gây nguy hiểm.
- Kiểm tra tối thiểu bằng keyboard, trình đọc màn hình đại diện và automated accessibility test
  cho flow checkout/admin quan trọng.

## 14. Kiểm thử

### 14.1 Nguyên tắc

- Test kiểm tra hành vi quan sát được, không khóa cứng implementation detail.
- Mỗi bug fix MUST có regression test thất bại trước khi sửa và đạt sau khi sửa.
- Test độc lập, xác định, không phụ thuộc thứ tự, mạng thật hoặc đồng hồ thật.
- Dùng factory/builder tạo dữ liệu có ý nghĩa; chỉ tạo field cần cho scenario.
- Không dùng `sleep()` để đồng bộ test.
- Tên test mô tả điều kiện và kết quả mong đợi.

### 14.2 Phạm vi

- **Unit test:** Value Object, Enum transition, tính tiền, phân bổ giảm giá và domain rule thuần.
- **Feature test:** route/controller, validation, Policy, Action, DB constraint, Livewire và Filament.
- **Integration/contract test:** payment, vận chuyển, email/storage qua sandbox/fake có contract rõ.
- **Browser/E2E test:** số lượng nhỏ cho happy path và failure path quan trọng của checkout/admin.

Luồng quan trọng phải có test cho:

- `401`, `403`, `404`, `409`, `422` phù hợp;
- thay đổi giá/tồn kho trong lúc checkout;
- submit lặp cùng idempotency key;
- cùng key nhưng payload khác;
- webhook lặp, sai chữ ký, sai thứ tự và trạng thái terminal;
- transaction rollback và side effect chỉ phát sau commit;
- queue retry/failure;
- giới hạn quyền truy cập đơn hàng/admin;
- time zone và boundary ngày;
- rounding/tổng tiền bằng số nguyên;
- accessibility state của form/modal quan trọng.

Test migration và truy vấn phụ thuộc MySQL MUST chạy với MySQL 8.4 trong CI. SQLite MAY dùng cho unit
nhẹ nhưng không được là bằng chứng duy nhất cho foreign key, lock, JSON, collation hoặc SQL đặc thù.

Coverage là tín hiệu, không phải mục tiêu duy nhất. Code mới/thay đổi SHOULD đạt ít nhất 80% line
coverage; các invariant về tiền, đơn hàng, thanh toán và tồn kho phải phủ đủ nhánh nghiệp vụ bất kể
con số tổng.

## 15. Tài liệu và comment

- Public API, command vận hành, biến môi trường và quy trình triển khai phải được tài liệu hóa.
- Thay đổi kiến trúc đáng kể cần ADR ngắn: bối cảnh, quyết định, phương án bị loại và hệ quả.
- Comment giải thích **vì sao**, invariant hoặc workaround; không diễn giải lại code.
- Workaround phải có issue/link và điều kiện gỡ bỏ.
- PHPDoc bắt buộc cho generic collection, array shape phức tạp và contract khó suy ra.
- Migration/backfill không hiển nhiên phải có runbook gồm pre-check, thực thi, quan sát và phục hồi.
- Khi đổi route/payload/event/schema, cập nhật tài liệu contract và ví dụ trong cùng PR.
- `README` phải đủ để developer mới chạy project mà không cần secret thật.

## 16. Definition of Done

Một thay đổi chỉ hoàn tất khi:

- code đã được format và không còn lỗi static analysis;
- test liên quan đã thêm/cập nhật và toàn bộ suite đạt;
- frontend lint/build đạt nếu có thay đổi UI;
- migration được kiểm tra trên MySQL 8.4 và có chiến lược deploy tương thích;
- authorization, validation, idempotency và concurrency đã được xem xét;
- không có secret/PII/debug dump trong diff hoặc log;
- không phát sinh N+1/truy vấn bất hợp lý ở đường chạy bị ảnh hưởng;
- UI đã kiểm tra responsive, keyboard, focus và trạng thái lỗi/loading/empty;
- tài liệu, `.env.example`, runbook và ADR được cập nhật nếu cần;
- PR nêu rõ rủi ro, cách test và cách rollback/roll-forward.

Các ngoại lệ phải được ghi trong PR và được reviewer chịu trách nhiệm kỹ thuật chấp thuận. Ngoại lệ
không tự động trở thành tiền lệ cho thay đổi tiếp theo.
