# Task: Date/Number Locale Formatting (Vietnamese)

**ID:** TASK-112
**Unit:** — (cross-cutting: i18n)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Đảm bảo date, time, và số được format đúng theo locale Việt Nam trong toàn bộ EverLMS UI. Định dạng ngày: `d/m/Y` (không phải m/d/Y). Tiền tệ VND: số nguyên, dấu phẩy ngăn cách nghìn (1.000.000 đ), không có decimal. Số thập phân dùng dấu phẩy (theo chuẩn VN). Drupal `date.formatter` service + PHP `NumberFormatter` (intl extension).

## Acceptance Criteria
- [ ] Drupal date format config: tạo date format `vi_date` = `d/m/Y` làm mặc định cho language VI
- [ ] Time format: `H:i` (24-hour, không AM/PM) cho VI locale
- [ ] DateTime display trong `lms_enrollment`, `lms_progress`, `lms_certificate` entities dùng VI date format khi ngôn ngữ = VI
- [ ] VND currency formatting:
  - [ ] Template/helper: `format_vnd($amount)` → `"1.500.000 đ"` (dấu chấm ngăn cách nghìn, không decimal)
  - [ ] Sử dụng trong: checkout summary, payment receipt, course price display, dashboard revenue widget (admin)
- [ ] PHP `intl` extension enabled (verify `phpinfo()`)
- [ ] `NumberFormatter` cho số VND:
  ```php
  $fmt = new \NumberFormatter('vi_VN', \NumberFormatter::CURRENCY);
  $fmt->format(1500000); // "1.500.000 ₫"
  ```
- [ ] "Relative time" strings đúng tiếng Việt: "2 ngày trước", "vừa xong", "trong 1 giờ"
- [ ] Twig extension hoặc custom filter `|format_date_vi` và `|format_vnd` available trong templates
- [ ] KernelTest: `LocaleFormatterTest` verify format output cho date + VND

## Technical Notes
- Drupal date formats: `/admin/config/regional/date-time` → export vào config YAML
- `date.formatter` service: `\Drupal::service('date.formatter')->format($timestamp, 'vi_date')`
- VND format service: `web/modules/custom/lms_base/src/Service/CurrencyFormatter.php`
- Twig filter: register trong `lms_base.services.yml` — `lms_base.twig.currency_extension`
- Drupal core "Relative time" (`\Drupal\Core\Datetime\DateFormatter::formatTimeDiffSince`) — đã VI nếu locale đúng
- Verify: `composer require ext-intl` trong Dockerfile/DDEV config nếu chưa có
- Tham chiếu: TASK-110 (i18n infrastructure); TASK-111 (VI translation)

## Dependencies
- TASK-110 (language config phải exist)
- TASK-111 (VI translation — date format strings trong PO file)

## Definition of Done
- [ ] Code complete (DateFormatter config + CurrencyFormatter service + Twig filter committed)
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (`LocaleFormatterTest`)
- [ ] Manual verify: checkout page hiển thị "1.500.000 đ" đúng format
- [ ] Manual verify: enrollment date hiển thị "25/04/2026" (không "04/25/2026")
- [ ] No new debt introduced
