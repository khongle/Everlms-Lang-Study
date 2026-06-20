---
page: workspace-dashboard
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Workspace Dashboard

**Canonical URL:** `/workspace` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin_dashboard`
**Solution:** Custom controller returning a Twig render array; KPI metrics aggregated from entity storage at request time; last 10 orders embedded as sub-component.
**Owner module:** `lms_course` — `src/Controller/AdminController::dashboard()`
**Primary source:** Computed — entity counts + order query (no single entity backs the page)
**Editable source:** `lms_order` (recent orders table); `lms_course`, `lms_quiz`, `lms_user_course` for KPI counts; all managed via their respective workspace sub-pages
**Editor role:** `administer lms_course` (instructor, admin)
**Editor management UI:** KPIs are read-only; orders editable at `/workspace/orders/{id}/edit`
**Theme template:** `lms_course/templates/lms-admin-dashboard.html.twig` (hook: `lms_admin_dashboard`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache (per-request aggregation; no explicit cache tags set in controller)
**Related entities:** [`lms_order`](../../C04-entities/), [`lms_course`](../../C04-entities/), [`lms_quiz_result`](../../C04-entities/)
**Prototype reference:** [`../../C10-prototypes/html/manage/dashboard.html`](../../C10-prototypes/html/manage/dashboard.html)
**Notes:** `quizList()` has one known mock: `pending_grading` is hardcoded to `5` regardless of actual essay submissions. Revenue figure aggregates from `lms_order` entities with status `completed`. Sidebar navigation is a shared include (`lms-admin-sidebar.html.twig`).

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Page-specific (shared across all workspace pages via Twig include)
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig`
- **Plugin / Module:** `lms_course` (shared sub-template)
- **Source content type / Entity:** Static navigation links (hardcoded in template)
- **Backed by fields / config keys:** None — nav items are hardcoded HTML links in the sidebar template
- **Editable source:** Static / developer-owned; edit the Twig file to change items
- **Editor role / permission:** Developer only
- **Editor management UI:** `computed/read-only` — no UI; edit `lms_course/templates/lms-admin-sidebar.html.twig`
- **Revision / moderation:** No
- **Translation source:** N/A (English only in template)
- **Edit smoke:** n/a (static)
- **View mode / Projection:** Sidebar panel
- **Region / Placement:** Left sidebar (`.app-sidebar`)
- **Notes:** Sidebar links cover Dashboard, Courses, Quizzes, Orders, Users, Reports plus links to Pricing, Certificate Templates, Content Tools. Active state driven by route comparison in template.
- **How to implement (EN):** Create `lms-admin-sidebar.html.twig`; include it inside each workspace page template with `{% include 'lms-admin-sidebar.html.twig' %}`.
- **How to implement (VI):** Tạo file Twig `lms-admin-sidebar.html.twig` chứa các link điều hướng; nhúng vào mọi template workspace bằng `{% include %}`.
- **Solution Architect Request:** —

### 2. Page Topbar (Title + Action Buttons)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block in `lms-admin-dashboard.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static (page title hardcoded; date rendered server-side)
- **Backed by fields / config keys:** Static string "Dashboard"; current date from PHP
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** n/a
- **View mode / Projection:** Topbar strip
- **Region / Placement:** Top of `.app-main`
- **Notes:** "Export" button (not wired in current implementation) and "+ New Course" button link to `/workspace/courses/create`.
- **How to implement (EN):** Render page title + date in the `.app-topbar` div; link the "+ New Course" button to `lms_course.admin_course_create` route.
- **How to implement (VI):** Hiển thị tiêu đề trang và ngày hiện tại trong vùng topbar; nút "+ Thêm khóa học" trỏ đến route tạo mới.
- **Solution Architect Request:** —

### 3. KPI Cards (4-column row)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over `kpi_data` array passed from `AdminController::dashboard()`
- **Plugin / Module:** `lms_course` — `AdminController::dashboard()`
- **Source content type / Entity:** Aggregated from: `lms_order` (revenue), `lms_user_course` (learner count), `lms_quiz_result` (pending grading — currently mocked), `lms_course` (course count)
- **Backed by fields / config keys:** `lms_order.total`, `lms_order.status`; `lms_user_course` count; `lms_quiz_result.status`
- **Editable source:** computed/read-only (live DB counts)
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Create/complete an order → reload dashboard → revenue KPI updates
- **View mode / Projection:** Card grid (4 columns)
- **Region / Placement:** Top of content area
- **Notes:** Four KPIs: Total Revenue (sum of completed order totals), Active Students (lms_user_course distinct UIDs), Course Completion Rate (computed ratio), Pending Grading (hardcoded mock = 5). The "Pending Grading" KPI is a **known bug/stub** — see [`../../../../E-Quality/E02-issues/`](../../../../E-Quality/E02-issues/).
- **How to implement (EN):** In `AdminController::dashboard()`, query entity storage for each KPI. Pass a `kpi_data[]` array. Render cards in template with icon, value, label, delta.
- **How to implement (VI):** Trong `AdminController::dashboard()`, truy vấn entity storage cho từng KPI; truyền mảng `kpi_data[]` vào template để hiển thị thẻ số liệu.
- **Solution Architect Request:** —

### 4. Revenue Trend Chart

- **Scope:** Page-specific
- **Implementation mechanism:** Static SVG bar chart inside `lms-admin-dashboard.html.twig` (no JavaScript charting library)
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_order` (grouped by month for completed orders)
- **Backed by fields / config keys:** `lms_order.total`, `lms_order.created`, `lms_order.status`
- **Editable source:** computed/read-only (live aggregation)
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Create completed orders in different months; verify bar heights update
- **View mode / Projection:** 2/3-width chart panel
- **Region / Placement:** Left of Alerts panel, below KPI row
- **Notes:** Prototype uses a static SVG for visual shape. The production implementation should generate month-by-month revenue data server-side and pass it to the template. A time-period filter select (This Month / Last 3 Months / This Year) is shown in the prototype but is **not wired** in the current as-built implementation.
- **How to implement (EN):** Query `lms_order` grouped by `DATE_FORMAT(created, '%Y-%m')` for completed orders; pass data array to template; render as an inline SVG or pass JSON for a lightweight JS chart.
- **How to implement (VI):** Truy vấn `lms_order` gom nhóm theo tháng; truyền dữ liệu vào template dưới dạng SVG inline hoặc JSON cho biểu đồ JS nhẹ.
- **Solution Architect Request:** —

### 5. Alerts Panel

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block; data assembled in `AdminController::dashboard()`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_quiz_result` (pending essay grading), `lms_order` (pending/failed orders)
- **Backed by fields / config keys:** `lms_quiz_result.status = pending_review`; `lms_order.status = pending`
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** Links navigate to `/workspace/orders` or `/workspace/quizzes`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Create a quiz result with pending_review status; verify it appears in Alerts panel
- **View mode / Projection:** 1/3-width alert list (`.alert-item` rows)
- **Region / Placement:** Right of Revenue chart
- **Notes:** Prototype shows 3 alert types (essay grading, Stripe webhook failure, refund requests). The "Stripe webhook failed" alert is **prototype-only** — no webhook infrastructure exists in the current codebase (see known risk in [`../../../../E-Quality/E02-issues/`](../../../../E-Quality/E02-issues/)). Real implementation should surface essay-pending and order-pending alerts.
- **How to implement (EN):** Query `lms_quiz_result` for `status=pending_review`; query `lms_order` for `status=pending`. Pass alert arrays to template. Each alert item has icon, text, timestamp, link.
- **How to implement (VI):** Truy vấn kết quả quiz chờ chấm và đơn hàng chờ xử lý; hiển thị danh sách cảnh báo với icon, mô tả và liên kết đến trang quản lý tương ứng.
- **Solution Architect Request:** —

### 6. Recent Orders Table

- **Scope:** Page-specific
- **Implementation mechanism:** Twig table rendered from `recent_orders` array passed by `AdminController::dashboard()` (last 10 orders)
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_order` entity — last 10 ordered by `created` DESC
- **Backed by fields / config keys:** `lms_order.id`, `lms_order.field_learner` (entity_ref→user), `lms_order.field_course` (entity_ref→lms_course), `lms_order.field_total`, `lms_order.field_payment_method`, `lms_order.field_status`, `lms_order.created`
- **Editable source:** `lms_order` entities; edit at `/workspace/orders/{id}/edit`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/orders` (full list) and `/workspace/orders/{lms_order}/edit` (edit form)
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Edit an order's status → reload dashboard → updated status visible in row
- **View mode / Projection:** HTML table (`.table`)
- **Region / Placement:** Below chart/alerts row
- **Notes:** Columns: Order ID, Customer (avatar + name), Course, Amount, Method, Status badge, Date. "View all →" links to `/workspace/orders`.
- **How to implement (EN):** Load 10 most recent `lms_order` entities with their `field_learner` and `field_course` populated. Pass as `recent_orders` array. Render each as a `<tr>` with a status badge.
- **How to implement (VI):** Tải 10 đơn hàng gần nhất của entity `lms_order` cùng người học và khóa học; hiển thị dạng bảng với badge trạng thái.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar | Navigation links, section labels | Static template | Hardcoded HTML | Developer edits Twig file | Developer only | No | No | None | Static — visual check |
| Topbar | "Dashboard" title, current date, action buttons | Static + PHP date | N/A | N/A | N/A | No | No | None | Static |
| KPI Cards | Revenue total, student count, completion rate, pending grading | `lms_order`, `lms_user_course`, `lms_quiz_result` | total, status, created | computed/read-only | N/A | No | No | Per-request (no-cache) | Create order → check revenue |
| Revenue Trend | Monthly revenue bars + trend line | `lms_order` grouped by month | total, created, status=completed | computed/read-only | N/A | No | No | Per-request | Add completed orders across months |
| Alerts Panel | Essay-pending count, order-pending count | `lms_quiz_result`, `lms_order` | status=pending_review / pending | computed/read-only | N/A | No | No | Per-request | Create pending quiz result |
| Recent Orders | Order ID, customer, course, amount, method, status, date | `lms_order` + user + lms_course | All lms_order fields | `/workspace/orders/{id}/edit` | `administer lms_course` | No | No | Per-request | Edit order status → verify in table |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Sidebar navigation links | Developer-owned UI chrome; rarely changes | Developer convention | Review if nav items grow beyond ~12 links |
| "pending_grading" KPI value | Hardcoded to `5` in `AdminController::quizList()` — mock | Known stub — see E02 issues | Fix when essay grading review UI is implemented |
| About page team members (not on this page) | Hardcoded in PageController | Known limitation | Migrate to entity when team management needed |

## Implementation status

| Component | Status | Notes |
|---|---|---|
| Sidebar | DONE | `lms-admin-sidebar.html.twig` shared include |
| Topbar | DONE | Static title + date in template |
| KPI Cards | DONE (partial) | Revenue + student count real; pending_grading hardcoded to 5 |
| Revenue Trend Chart | DONE (static SVG) | Data not dynamically generated from DB in current implementation |
| Alerts Panel | DONE (partial) | Essay + order alerts real; Stripe webhook alert is prototype-only |
| Recent Orders Table | DONE | Last 10 orders loaded from entity storage |
