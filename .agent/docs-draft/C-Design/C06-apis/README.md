# C06-APIs — Index of API Contracts

> Drupal 11 không có REST/GraphQL riêng. Toàn bộ "API" là HTTP routes được Drupal routing system xử lý qua controllers. Một số route trả JSON (JsonResponse), số còn lại trả Drupal render array (HTML page). File này là index của tất cả routes được mapping theo domain.

**Base URL:** `/` (Drupal standard, không có `/api/v1` prefix toàn cục)
**Auth:** Drupal session cookie + CSRF token (X-CSRF-Token header cho các POST XHR requests)
**Response format:** JSON hoặc HTML tùy route. Các route JSON thuần trả `application/json`.

---

## Conventions dùng trong các file con

| Cột | Ý nghĩa |
|-----|---------|
| Route ID | Machine name trong routing.yml |
| Path | URL pattern (path parameter trong `{}`) |
| Method | HTTP method (GET nếu không khai báo `methods:`) |
| Controller | Class::method hoặc `_form:`/`_entity_form:` |
| Auth | `anon` = public · `login` = `_user_is_logged_in` · `perm:X` = quyền cụ thể |
| Response | `HTML`, `JSON`, `Form` (Drupal Form API) |

---

## File Index

| File | Domain | Modules | Routes |
|------|--------|---------|--------|
| [01-content-api.md](01-content-api.md) | Content | lms_course | 18 |
| [02-assessment-api.md](02-assessment-api.md) | Assessment | lms_quiz | 4 |
| [03-progress-api.md](03-progress-api.md) | Progress / Dashboard | lms_progress | 5 |
| [04-commerce-api.md](04-commerce-api.md) | Commerce | lms_order, lms_membership, lms_payment | 12 |
| [05-certificate-api.md](05-certificate-api.md) | Certificate | lms_certificate | 7 |
| [06-communication-api.md](06-communication-api.md) | Communication | lms_forum, lms_notification, lms_rating | 11 |
| [07-integration-api.md](07-integration-api.md) | Integration | lms_ai, lms_zoom, lms_analytics | 10 |

---

## Known Gaps (routing.yml missing)

| Module | Gap | Status |
|--------|-----|--------|
| `lms_chapter` | Không có routing.yml — chapter CRUD xử lý qua admin form trong `lms_course` (entity form route) | Gap |
| `lms_lesson` | Không có routing.yml — lesson view ở `lms_progress`, CRUD ở entity form route | Gap |
| `lms_question` | Không có routing.yml — question CRUD qua entity form; quiz builder dùng inline form | Gap |
| `lms_stripe` | Không có routing.yml — không có webhook endpoint; chỉ là plugin class | **RISK-004** |
| `lms_paypal` | Không có routing.yml — không có webhook endpoint; chỉ là plugin class | **RISK-004** |

> **RISK-004** (từ A10): Payment gateway chỉ là stub → thiếu webhook receiver cho Stripe và PayPal. Đây là gap nghiêm trọng về tính toàn vẹn thanh toán.

---

## Open Decisions Liên quan

| ID | Quyết định | Ảnh hưởng |
|----|-----------|-----------|
| DEC-001 | API input validation: per-route vs global middleware | Tất cả routes POST |
| RISK-001 | API endpoint validation gaps | Tất cả POST routes |

---

*Cập nhật lần cuối: 2026-04-16*
