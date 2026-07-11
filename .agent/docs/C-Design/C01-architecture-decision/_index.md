# C01-architecture-decision - Architecture Roll-up

**Last updated:** 2026-06-21
**source_state:** AS_BUILT

## Decision
EverLMS là một **modular monolith trên Drupal 11**: một bản cài Drupal, một database MariaDB, ~28 module custom dưới cùng một tiền tố `lms_*`, mỗi module sở hữu một phần domain qua **custom content/config entities** (không dùng node, trừ vài `node.type` legacy). Các mối quan tâm xuyên suốt (cross-cutting) dùng **plugin host**: payment (`@PaymentGateway`), AI (`@AiProvider`), và chấm điểm quiz (`GradingStrategyFactory`). Front-end là một theme Drupal tùy biến (`lms_theme`); một **Flutter app** riêng biệt gọi REST API tùy biến (`/api/v1`, `lms_mobile_api`). Commerce được xây **tùy biến** (không dùng Drupal Commerce).

## Rationale
- **Custom entities thay vì node:** domain (course, quiz, order, certificate) cần schema, quan hệ và access rule chính xác mà node generic không mô hình hóa gọn gàng được.
- **Plugin host** giữ cho gateway/AI provider/grading strategy luôn mở để mở rộng mà không phải đụng vào caller (nguyên tắc Open/Closed) — thêm một gateway hay LLM mới chỉ là thêm một plugin.
- **Modular monolith** (so với microservices): một team duy nhất, một lần deploy duy nhất, DB transaction dùng chung để đảm bảo tính nhất quán order→enrollment→certificate; vận hành đơn giản hơn nhiều ở quy mô này.
- **Event-driven side effect:** việc cấp certificate subscribe vào `QuizPassedEvent` — tách rời (decouple) phần đánh giá khỏi phần cấp chứng chỉ.
- **Quyết định được ghi nhận tại** [ADR-002 modular monolith](../../G-Governance/G01-adrs/ADR-002-drupal-11-modular-monolith.md), [ADR-003 plugin hosts](../../G-Governance/G01-adrs/ADR-003-plugin-hosts-for-extensibility.md).

## Source classification
Đây là bản ghi nhận theo hướng **rebuild/as-built**. Mọi module bên dưới đều tồn tại trong `web/modules/custom/`. Status phản ánh trạng thái as-built đã được xác minh: `done` = đã build & hoạt động; `in-progress` = một phần; `blocked` = đã định nghĩa nhưng chưa hoạt động. (Rebuild refinement: mọi dòng đều là `as-built-backed` — đã xác minh theo code/config/DB thực tế, 2026-06-21.)

## Standard runtime shape
- **Request:** nginx-fpm → Drupal 11 (PHP 8.3) → MariaDB 10.11; Drush 13 dùng cho CLI/vận hành; DDEV cho môi trường local.
- **Rendering:** server-rendered Twig qua `lms_theme` (dựa trên Bootstrap, 16 region, 15 homepage skin); phần admin dùng Claro.
- **Mobile:** Flutter app → `/api/v1` JSON (bearer token) → controller `lms_mobile_api` → service/entity dùng chung.
- **Async:** Drupal cron (`drush cron`) điều phối việc gửi notification; certificate được cấp theo cơ chế event.
- **PDF:** mPDF render certificate template ở phía server.

## Route groups
| Route group | Path prefix (logical) | Audience | Access posture | EverLMS mapping |
| --- | --- | --- | --- | --- |
| Public | `/*` | anonymous + authenticated | open / minimal | catalog (`/courses`, `/course/*`), blog, FAQ, login/register |
| App | `/app/*` (logical) | authenticated learners | role-gated | dashboard, my-courses, lesson player, quiz attempt, certificates |
| Manage | `/manage/*` (logical) | instructors / staff | role + permission | course authoring, analytics, order/membership management |
| API | `/api/v1/*` | mobile client | bearer token + scope | `lms_mobile_api` (~22 routes) |
| Admin | `/admin/*` | administrators | admin permission | Drupal admin + `lms_*.settings` config forms |

> EverLMS không dùng tiền tố `/app` / `/manage` theo nghĩa đen cho mọi route (nhiều route nằm ở `/course/*`, `/dashboard`, v.v.); bảng trên ánh xạ path thực tế vào 5 nhóm logic. Path chính xác xem tại [C07-sitemap](../C07-sitemap/_index.md).

> **Residual as-built (route-group mirror, Gate 6.R):** nhóm `API` là conditional group hợp lệ (B02 khai báo API surface, ~22 route `lms_mobile_api`) và có mặt nhất quán ở đây + [C02 access matrix](../C02-user-roles/_index.md#route-group-access-matrix), nhưng KHÔNG có trang C11 nào mang `route_group: api` — đúng theo bảng chuẩn khung (mỗi route group | conditional | realisation): `API | conditional | REST/JSON controller`, tức API luôn là controller trả JSON, không bao giờ là trang Twig có theme, nên không tạo `C11-pages-components/pages/P0##` cho nó (không có trang nào để mirror). Toàn bộ 22 route được đặc tả tại [C06 mobile API](../C06-apis/_index.md) và [I01-public-api-spec](../../I-External/I01-public-api-spec/README.md). Đây là mismatch cấu trúc có chủ đích giữa C01/C02 (khai báo đúng) và C11 (0 trang, đúng thiết kế) — không phải nội dung thiếu; ghi nhận residual thay vì tạo trang C11 giả cho một route group không có UI.

## Standard modules
The framework mandates 3 cross-cutting standard modules. As-built status:
| Standard module | EverLMS realisation | Status |
| --- | --- | --- |
| **Content Tools** (seed/demo generators) | [lms_content_tools](../C05-modules/lms_content_tools.md) + `lms_base` Drush commands (`lms:import`, `lms:generate-demo`, `lms:clean`) | ✅ present |
| **Secrets** (single entry point + rotation + audit) | ❌ **absent** — secrets stored in `lms_*.settings.yml` config (R-001). `key` module is a declared dep of `lms_ai` but **no key entities are configured**. | ⚠️ gap (BLK-001) |
| **Feature Flag Manager** (evaluator + rollout DSL + kill-switch) | ❌ **absent** — only core `system.feature_flags` (`linkset_endpoint`) exists; no custom rollout/kill-switch. | ⚠️ gap |

## Module table
Status: mọi module đã build đều là `done` trừ khi có ghi chú khác. Epic link → [D02-epics/](../../D-Development/D02-epics/). Số backing-pages là số trang logic xấp xỉ cho mỗi module.

| Module | Name | Phase | Epic | Status | Backing pages |
| ------ | ---- | ----- | ---- | ------ | ------------- |
| lms_base | [Base / shared services](../C05-modules/lms_base.md) | 1 | E01 | done | 0 |
| open_switcher | [User impersonation](../C05-modules/open_switcher.md) | 1 | E01 | done | 1 |
| lms_theme | [Front-end theme](../C05-modules/lms_theme.md) | 1 | E01 | done | (theme) |
| lms_course | [Courses & catalog](../C05-modules/lms_course.md) | 2 | E02 | done | 4 |
| lms_chapter | [Chapters](../C05-modules/lms_chapter.md) | 2 | E02 | done | 1 |
| lms_lesson | [Lessons & media](../C05-modules/lms_lesson.md) | 2 | E02 | done | 2 |
| lms_progress | [Enrollment & progress](../C05-modules/lms_progress.md) | 3 | E03 | done | 3 |
| lms_question | [Questions (8 types)](../C05-modules/lms_question.md) | 4 | E04 | done | 2 |
| lms_quiz | [Quizzes & grading](../C05-modules/lms_quiz.md) | 4 | E04 | done | 3 |
| lms_certificate | [Certificates (PDF)](../C05-modules/lms_certificate.md) | 5 | E05 | done | 2 |
| lms_badge | [Badges](../C05-modules/lms_badge.md) | 5 | E05 | blocked | 1 |
| lms_payment | [Payment gateways](../C05-modules/lms_payment.md) | 6 | E06 | in-progress | 1 |
| lms_stripe | [Stripe gateway](../C05-modules/lms_stripe.md) | 6 | E06 | done | 0 |
| lms_paypal | [PayPal gateway (duplicate)](../C05-modules/lms_paypal.md) | 6 | E06 | blocked | 0 |
| lms_order | [Orders & checkout](../C05-modules/lms_order.md) | 6 | E06 | done | 2 |
| lms_bundle | [Course bundles](../C05-modules/lms_bundle.md) | 6 | E06 | done | 2 |
| lms_membership | [Memberships](../C05-modules/lms_membership.md) | 7 | E07 | done | 2 |
| lms_forum | [Community forum](../C05-modules/lms_forum.md) | 8 | E08 | done | 3 |
| lms_rating | [Ratings & reviews](../C05-modules/lms_rating.md) | 8 | E08 | done | 1 |
| lms_note | [Learner notes](../C05-modules/lms_note.md) | 8 | E08 | done | 1 |
| lms_wishlist | [Wishlist](../C05-modules/lms_wishlist.md) | 8 | E08 | done | 1 |
| lms_blog | [Blog](../C05-modules/lms_blog.md) | 8 | E08 | done | 2 |
| lms_faq | [FAQ](../C05-modules/lms_faq.md) | 8 | E08 | done | 1 |
| lms_zoom | [Live sessions (Zoom)](../C05-modules/lms_zoom.md) | 8 | E08 | in-progress | 1 |
| lms_notification | [Notifications](../C05-modules/lms_notification.md) | 9 | E09 | in-progress | 1 |
| lms_ai | [AI authoring](../C05-modules/lms_ai.md) | 10 | E10 | done | 1 |
| lms_mobile_api | [Mobile REST API](../C05-modules/lms_mobile_api.md) | 11 | E11 | done | 0 (API) |
| lms_analytics | [Analytics & reports](../C05-modules/lms_analytics.md) | 12 | E12 | in-progress | 3 |
| lms_content_tools | [Content tools / seed](../C05-modules/lms_content_tools.md) | 13 | E13 | done | 1 |

> 28 module (data-derived 2026-06-21). Mỗi module ở đây đều có một contract [C05-modules](../C05-modules/_index.md).
