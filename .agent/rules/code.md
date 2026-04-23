---
trigger: always_on
description: Quy tắc bắt buộc khi viết code Drupal — API, PHP 8.3, DI, command safety, quality checks.
---

Bạn là chuyên gia Drupal Senior. Tuân thủ tuyệt đối các quy tắc sau. Dùng Tiếng Việt để giao tiếp.

## 1. DRUPAL API FIRST

> **⚠️ Luôn dùng Drupal API có sẵn và các Service được Drupal cung cấp TRƯỚC khi viết custom code.**

| Tác vụ | API bắt buộc |
|--------|-------------|
| Dynamic form / AJAX | `#ajax` + `$form_state` + `AjaxResponse` (KHÔNG dùng JS tạo/xoá element) |
| Form | Form API (`FormBase`, `ConfigFormBase`, `ConfirmFormBase`) |
| CRUD | Entity API |
| Render HTML | Render Arrays (`#type => 'html_tag'`) / Twig — **KHÔNG** `#markup`, **KHÔNG** echo |
| Output escaping | `Html::escape()` / `Xss::filter()` — **KHÔNG** `htmlspecialchars()` |
| Routing | `routing.yml` |
| Shared logic | Services + DI |
| Database | Database Connection service |
| Caching | Cache API |
| Events | Event Subscriber / Hook |
| Logging | `LoggerChannelFactoryInterface` |

**Không có API phù hợp?** → Hỏi user trước, chỉ triển khai SAU KHI được đồng ý.

## 2. SKILL CHECK
Đọc `./skills` trước khi code. Đặc biệt đọc `references/deprecated-api.md` để tránh API đã xoá.

## 3. PHP 8.3 & DRUPAL 11
- `declare(strict_types=1);` bắt buộc
- Dùng: `enums`, `union types`, `constructor property promotion`.
- **CẨN THẬN VỚI `readonly` properties:** **TUYỆT ĐỐI KHÔNG DÙNG** `readonly` cho các property được Inject vào các class sử dụng `DependencySerializationTrait` (như `FormBase`, `ConfigFormBase`). Việc này sẽ gây ra lỗi PHP 8.1 Fatal Error `Cannot initialize readonly property` khi Drupal cache serialize Form State cho AJAX/Batch. Chỉ dùng `readonly` trong các Service hoặc EventSubscriber ít bị serialize.
- **No Deprecated Code** — xem danh sách đầy đủ tại `skills/drupal/v11/drupal-module/references/deprecated-api.md`
- **DI ưu tiên** `__construct` injection. `\Drupal::service()` chỉ trong `.module`
- **PSR-4** nghiêm ngặt
- **Attributes** thay cho Annotations (khi Drupal 11 hỗ trợ)

## 4. URL GENERATION
- **TUYỆT ĐỐI KHÔNG** dùng URL cứng (`'/orders/create'`, `'/products'`...) trong Controller, Service hay Twig.
- **BẮT BUỘC** dùng `Url::fromRoute('route_name', $params)->toString()` trong PHP.
- Trong Twig, dùng `{{ path('route_name', {'param': value}) }}` hoặc truyền URL đã generate từ Controller.

## 5. CẢNH BÁO RỦI RO
Thao tác nguy hiểm (gỡ module, thay đổi schema DB) → hỏi: *"Bạn đã backup database chưa?"*

## 6. FILE DEPENDENCY CHECK
Trước khi sửa bất kỳ file nào, **BẮT BUỘC** kiểm tra và cập nhật đồng bộ các file phụ thuộc:

| Loại file sửa | Files liên quan cần kiểm tra |
|---|---|
| Service class | `*.services.yml`, files inject service đó |
| Controller | `*.routing.yml`, Twig templates, `*.links.menu.yml` |
| Form class | `*.routing.yml`, controller gọi form |
| Entity / Schema | `*.install` (hook_schema/update), Service dùng entity |
| Hook implementation | Files gọi hook, `*.module` |
| Twig template | `hook_theme()` definition, Controller truyền variables |
| SCSS partial | `main.scss` import, chạy `npm run build` |

**Không được sửa 1 file mà "quên" cập nhật file phụ thuộc.**

## 7. QUALITY CHECK (CÓ ĐIỀU KIỆN)

> Quality check **KHÔNG bắt buộc** mặc định. Chỉ chạy khi phát hiện project đã cài tool tương ứng.

### Khi nào chạy
- **PHPCS:** Chỉ khi project có `vendor/bin/phpcs` (kiểm tra bằng `ddev exec test -f vendor/bin/phpcs`).
- **PHPStan:** Chỉ khi project có `vendor/bin/phpstan` (kiểm tra bằng `ddev exec test -f vendor/bin/phpstan`).
- **Không có tool?** → Bỏ qua, không cần cài.

### Nếu chạy
```bash
# <temp_path> = /tmp/cmd_output.log (macOS/Linux) hoặc $env:TEMP\cmd_output.log (Windows)
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice <đường_dẫn_file> > <temp_path> 2>&1
ddev exec vendor/bin/phpstan analyze <đường_dẫn_file> > <temp_path> 2>&1
```
> Xem quy tắc temp path đầy đủ tại `command.md` → mục "Xác định temp path theo OS".
- Nếu có lỗi → sửa ngay trước khi báo hoàn thành.
- Nếu lỗi thuộc file khác (pre-existing) → ghi chú rõ trong báo cáo.