# C09-themes - Theme and Design System

**Last updated:** 2026-06-21 · **source_state:** AS_BUILT (`web/themes/custom/lms_theme/`)

Chỉ là kiến trúc trình bày (presentation) — business data, route ownership, access và workflow logic vẫn nằm ở module/C11.

## Direction

### Canonical decision table
| Concern | Decision |
| ------- | -------- |
| Theme machine name | `lms_theme` (default front-end); `lms_classic` (alternate); Claro (admin) |
| Owning module | theme (not a module); thin `lms_theme.theme` preprocess |
| Visual source | live site / [C10](../C10-prototypes/_index.md) |
| Page/block source | [C11](../C11-pages-components/_index.md) (canonical) |
| Wireframe source | [C08](../C08-wireframes/_index.md) |
| Base theme | `false` (standalone; bundles Bootstrap-based styling via own libraries) |
| Posture | server-rendered Twig + SCSS; 15 selectable homepage skins; responsive |

### Route-group theme roles (ADR-007: single shared theme)
Framework convention mặc định một Drupal theme package riêng cho mỗi route group (`lms_public`/`lms_manage`/`lms_app`). As-built, EverLMS gộp cả ba vào **một** package (`lms_theme`) — xem [ADR-007](../../G-Governance/G01-adrs/ADR-007-single-shared-theme.md) cho lý do/hệ quả. Mapping:
| Route-group role | Theme package | Token scope | Ghi chú |
| ----------------- | -------------- | ----------- | ------- |
| `lms_public` | `lms_theme` | `--fe` | catalog, blog, marketing, auth |
| `lms_app` | `lms_theme` | `--fe` | dashboard, my-*, lesson/quiz player |
| `lms_manage` | `lms_theme` | `--be` | `/workspace/*`, `/manage/*` (staff) |
| `lms_admin` | **Claro (core default admin theme), hoàn toàn untouched** | n/a | `/admin/*` — không có admin theme tùy biến, không override template/library/CSS phạm vi admin |

- `lms_classic` là một **theme front-end thay thế riêng biệt** (full skin swap), không phải một trong 15 homepage skin dạng token-remap, và không phải một trong các role route-group ở trên.
- Mobile là **Flutter app** (client riêng biệt, không phải theme); print/export dùng mPDF cho certificate.

### Non-goals
- Không đặt business logic, access decision, hay entity query trong Twig — những thứ đó nằm ở controller/Views/route access.

## Tokens

### Token scopes
| Scope | Audience | Base palette | Notes |
| ----- | -------- | ------------ | ----- |
| `--fe` | public + learners | `{brand}` primary/secondary | 15 skins re-map these |
| `--be` | staff workspace | neutral + `{brand}` accents | denser tables |
| `--brand` | shared identity | `{brand}` | logo/colors via theme settings |
| `print` | certificates (mPDF) | high-contrast | PDF-safe |

### Core variables
Typography (họ font heading/body, scale), spacing scale, radius, color (primary/secondary/surface), semantic state (success/warn/error/info), định hướng component (card, button, form). Skin = một bản token re-map được chọn trong theme settings.

### Status tokens
| Status | Color rule | Text/icon rule |
| ------ | ---------- | -------------- |
| success (passed/paid/completed) | green | "Passed"/"Paid" label + check icon |
| warning (pending/partial) | amber | "Pending" label + clock icon |
| error (failed/declined/revoked) | red | "Failed" label + alert icon |
| info (draft/new) | blue | "Draft" label + dot |
Color không bao giờ là chỉ báo duy nhất (đảm bảo an toàn cho người mù màu).

### SDC contract
Props chỉ mang các display value đã chuẩn bị sẵn; slot mang render array; render metadata (cache tags/contexts) được giữ nguyên xuyên suốt các component (course_card, curriculum, KPI tiles).

### Breakpoints
4-tier (mobile / tablet / desktop / wide), dùng chung xuyên suốt mọi C11 page spec. Giá trị số (px) và mapping với SCSS `$breakpoint-*` là literal — xem [token-values.md ## Breakpoint tiers](token-values.md#breakpoint-tiers) (single source of truth, không lặp số ở đây). `.container` co giãn theo tier; layout 2 cột (sidebar/aside) thường sập về 1 cột ở tier tablet trở xuống trên trang listing/detail (chi tiết từng trang: page spec `## Responsive behavior`).

### Locale & formatting
Single-locale as-built (`config/sync/system.site.yml`: `langcode: en`, `default_langcode: en`; không có `language.entity.*.yml` bổ sung — module đa ngôn ngữ chưa cấu hình dù `lms_course.field_language` có option English/Spanish/Vietnamese ở cấp metadata course, không phải UI đa ngôn ngữ của site).
| Aspect | Value | Source |
| ------ | ----- | ------ |
| Site language | `en` (English) | `system.site.yml` |
| Timezone default | `Asia/Bangkok` | `system.date.yml` |
| First day of week | Sunday (`first_day: 0`) | `system.date.yml` |
| Date format (medium) | `D, j M Y - H:i` (vd "Mon, 6 Jul 2026 - 09:15") | `core.date_format.medium.yml` |
| Currency | USD, ký hiệu `$`, phân tách hàng nghìn bằng dấu phẩy (vd "$284,900") | `lms_payment`/`lms_order` price fields, quan sát trong C11 manage-orders/pricing |
| Number format | `1,234` (comma thousands, dot decimal) | quan sát trong C11 listing pages (vd "1,247 courses found") |

## Drupal theme notes
- **Theme files:** `lms_theme.info.yml` (16 region, library), `lms_theme.libraries.yml` (21 library), `lms_theme.theme` mỏng (preprocess), `templates/` (theo khu vực: home, user, navigation, block, page, content, layout, form, misc), SCSS compile ra `css/`.
- **Render ownership:** data/access/cache nằm ở route/controller/form/View/block; Twig sở hữu markup/class/slot.
- **Region inventory (16):** `header`, `primary_menu`, `secondary_menu`, `hero`, `breadcrumb`, `highlighted`, `content_top`, `content`, `content_bottom`, `sidebar_first`, `sidebar_second`, `footer_first`, `footer_second`, `footer_third`, `footer_fourth`, `footer_bottom`.
- **Asset libraries:** `lms_theme/global` dùng chung (global); 20 feature library được attach theo từng page/component.
- **Theme negotiation:** `lms_theme` cho front-end + workspace; Claro cho `/admin/*`.
- **Accessibility:** semantic HTML, alt text cho hình ảnh, quiz/player điều hướng được bằng bàn phím, mục tiêu WCAG 2.1 AA (B02).
- **QA pages:** homepage (tất cả skin), catalog, player, quiz, checkout, certificate PDF.

## Scope boundary
| In C09 | Outside C09 |
| ------ | ----------- |
| tokens, regions, components, libraries, negotiation, a11y, skins | business data, route ownership, access decisions, queries, workflow logic (modules/C11) |
