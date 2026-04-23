---
description: Scaffold docs structure cho Drupal project mới — tạo INDEX, architecture, tasks, SoT templates.
---

# /docs-init — Scaffold Project Documentation

Tạo khung sườn documentation cho Drupal project, chuẩn anti-drift với Source of Truth (SoT) per file.

## Step 1: Thu thập thông tin

Hỏi user **TẤT CẢ** các câu dưới đây trước khi tạo bất kỳ file nào:

1. **Tên project** (machine name + display name)?
   - VD: `affhub` / "AffHub", `linne_store` / "Linne Store"

2. **Mô tả ngắn** — 1 câu mô tả project?

3. **Danh sách custom modules** (machine names)?
   - VD: `myapp_core`, `myapp_product`, `myapp_order`, `myapp_api`
   - Mỗi module cần: tên, mô tả 1 dòng, entities sở hữu (nếu có)

4. **Danh sách custom entities** (nếu đã biết)?
   - VD: `product` (owned by myapp_product), `order` (owned by myapp_order)
   - Entity type: Custom Content Entity / Config Entity / Node bundles?

5. **Có Python/Node worker bên ngoài không?**
   - Nếu có: mô tả ngắn (VD: "Python worker poll jobs via API")

6. **Route prefix convention?**
   - VD: `/admin/store/...`, `/admin/hub/...`, `/admin/app/...`

7. **Theme:** Gin admin? Custom theme? Sub-theme?

8. **Có API endpoints cho external services?**
   - VD: REST API cho mobile app, webhook endpoints, worker API

> [!IMPORTANT]
> Nếu user chưa biết hết chi tiết (VD: chưa chốt entities) — **vẫn tạo** skeleton structure với placeholder. User có thể điền sau.

## Step 2: Tạo folder structure

// turbo
```bash
mkdir -p .agent/docs/architectures .agent/docs/design .agent/docs/testing
```

## Step 3: Tạo INDEX.md

Tạo file `.agent/docs/INDEX.md` theo template sau, **thay thế** placeholder `{...}` bằng thông tin từ Step 1:

```markdown
---
title: "{PROJECT_DISPLAY_NAME} — Project Documentation"
sot_for: [project_overview, decisions, architecture_overview]
updated: {DATE}
---

# {PROJECT_DISPLAY_NAME} — Project Documentation

> {PROJECT_DESCRIPTION}
> **{N} modules**, **{M} entities**, **{P} UI pages**.
> Route UI = `{ROUTE_PREFIX}` ({THEME_NAME}).

---

## 📐 Architecture (Source of Truth)

| Doc | SoT cho | File |
|:---|:---|:---|
| **Entity Schema** | Entities + fields + relationships | [entity_schema.md](architectures/entity_schema.md) |
| **Module Archs** (×{N}) | Code structure + routing per module | [architectures/](architectures/) |
| **UI Page Map** | Pages + AJAX + API routes | [ui_page_map.md](architectures/ui_page_map.md) |
{WORKER_ROW_IF_EXISTS}

### Module Architecture Docs

| Module | Architecture Doc |
|:---|:---|
{MODULE_TABLE_ROWS}

---

## 📋 Tracking

| Doc | Mô tả |
|:---|:---|
| **Tasks** | [tasks.md](tasks.md) — Master Checklist |
| **Test Cases** | [testing/test_cases.md](testing/test_cases.md) |

---

## 📝 SoT Rules (Anti-Drift)

> [!IMPORTANT]
> **Khi sửa entity fields** → sửa `entity_schema.md` (SoT) + module arch tương ứng.
> **Khi sửa route** → sửa module arch routing section (SoT).
> **Khi thay đổi UI page** → sửa `ui_page_map.md` (SoT).
> **KHÔNG duplicate** chi tiết vào `modules_functions.md` hay file này — chỉ link.

---

## ✅ Quyết Định Đã Xác Nhận

| # | Câu hỏi | Quyết định |
|:--|:---|:---|
| 1 | | _chưa có_ |

> Cập nhật bảng này mỗi khi có design decision mới.

---
*(Updated: {DATE})*
```

## Step 4: Tạo entity_schema.md

```markdown
---
title: Entity Schema Reference
sot_for: [entities, fields, relationships, entity_types]
updated: {DATE}
---

# {PROJECT_DISPLAY_NAME} — Entity Schema Reference

Tài liệu tham chiếu cho tất cả Entities trong hệ thống.

---

## Phân loại Entity

| Entity | Drupal Type | Bundles | Class | Module sở hữu |
|:---|:---|:---|:---|:---|
{ENTITY_TABLE_ROWS_OR_PLACEHOLDER}

### Code Structure mỗi Entity

\```text
src/
├── Entity/{EntityName}.php              # #[ContentEntityType], baseFieldDefinitions()
├── {EntityName}ListBuilder.php
├── {EntityName}AccessControlHandler.php
└── Form/
    ├── {EntityName}Form.php             # ContentEntityForm
    └── {EntityName}DeleteForm.php       # ContentEntityDeleteForm
\```

---

{ENTITY_SECTIONS_OR_PLACEHOLDER}
```

> Nếu entities chưa được xác định, để placeholder `<!-- TODO: Add entity definitions -->`.

## Step 5: Tạo Module Architecture files

Với **MỖI module** từ Step 1, tạo file `.agent/docs/architectures/{module_name}_architecture.md`:

```markdown
---
title: "Module: {MODULE_NAME}"
sot_for: [{MODULE_NAME}_code, {ENTITY_NAME}_entity_details]
updated: {DATE}
---

# Kiến trúc Module: `{MODULE_NAME}`

{MODULE_DESCRIPTION}

> **Entity sở hữu:** `{ENTITY_ID}` — {ENTITY_TYPE}
> [Schema Reference](entity_schema.md#{ANCHOR})

---

## 1. Directory Tree

\```text
web/modules/custom/{MODULE_NAME}/
├── {MODULE_NAME}.info.yml
├── {MODULE_NAME}.module
├── {MODULE_NAME}.routing.yml
├── {MODULE_NAME}.services.yml
├── {MODULE_NAME}.links.menu.yml
├── templates/
├── config/install/
├── src/
│   ├── Entity/
│   ├── Controller/
│   ├── Form/
│   └── Service/
\```

## 2. Entity: `{ENTITY_ID}`

<!-- Chi tiết entity: xem entity_schema.md -->
<!-- Code-level details (attribute, baseFieldDefinitions) bổ sung ở đây -->

## 3. Controllers

<!-- Add controllers specific to this module -->

## 4. Services

<!-- Add services specific to this module -->

## 5. Routing

\```yaml
# Routes for this module
\```

## 6. Info

\```yaml
name: {MODULE_DISPLAY_NAME}
type: module
description: {MODULE_DESCRIPTION}
core_version_requirement: ^11
package: {PROJECT_PACKAGE}
dependencies:
  - {DEPENDENCIES}
\```
```

## Step 6: Tạo ui_page_map.md

```markdown
---
title: "UI Page Map — {PROJECT_DISPLAY_NAME}"
sot_for: [ui_pages, ajax_routes, api_route_summary, shared_components]
updated: {DATE}
---

# UI Page Map — {PROJECT_DISPLAY_NAME}

## Global Layout

| Layer | Mô tả |
|:---|:---|
| **Theme** | {THEME_NAME} |
| **Route Prefix** | `{ROUTE_PREFIX}` |

---

## Page-by-Page Map

### 1. `{ROUTE_PREFIX}/dashboard` — Dashboard

| Thuộc tính | Giá trị |
|:---|:---|
| **Handler** | `DashboardController::index` |
| **Permission** | `access administration pages` |

<!-- Add more pages as they are designed -->

---

## AJAX Trigger Routes

| Path | Handler | Trigger | Response |
|:---|:---|:---|:---|

---

## Inbound API Routes

| Path | Method | Handler | Mô tả |
|:---|:---|:---|:---|

---

## Shared UI Components

| Component | Dùng ở | Mô tả |
|:---|:---|:---|
```

## Step 7: Tạo modules_functions.md

```markdown
---
title: Module & Function Summary
sot_for: [dependency_graph, module_summary_table]
updated: {DATE}
---

# {PROJECT_DISPLAY_NAME} — Module & Function Summary

> Tổng quan {N} Drupal modules. **Chi tiết code xem module architecture docs.**

---

## Dependency Graph (SoT)

\```mermaid
graph TD
    {DEPENDENCY_EDGES}
\```

---

## Module Overview

{MODULE_SUMMARIES_WITH_LINKS}

---

## Summary Table

| Module | Entities | Pages | Controllers | Services |
|:---|:---|:---|:---|:---|
{SUMMARY_TABLE_ROWS}
```

## Step 8: Tạo tasks.md

```markdown
# {PROJECT_DISPLAY_NAME} — Master Tasks

> Checklist tổng thể theo dõi tiến độ.
> `[ ]` chưa làm · `[/]` đang làm · `[x]` xong

---

## Phase 0: Setup
- [ ] DDEV + Docker
- [ ] Drupal 11 core
- [ ] Contrib modules
- [ ] Theme setup
- [ ] Export config + Backup

---

## Phase 1: Core Module
<!-- Scaffold core module: firewall, dashboard, settings -->

---

## Phase 2: Entities
<!-- Create custom entities -->

---

## Phase 3: Feature Controllers & UI
<!-- Controllers, templates, Views configs -->

---

## Phase 4: Polish & Deploy
- [ ] Custom CSS
- [ ] Final quality check
- [ ] Final backup
```

> **Phase structure linh động** — thêm/bớt phases tùy project. Nhưng luôn có Phase 0 (Setup) và Phase cuối (Polish).

## Step 9: Tạo test_cases.md và worker_pipeline (nếu cần)

Tạo `.agent/docs/testing/test_cases.md`:

```markdown
# {PROJECT_DISPLAY_NAME} — Test Cases

> Test plan cho project. Cập nhật khi thêm feature mới.

## Unit Tests
<!-- PHPUnit tests -->

## Browser Tests
<!-- Playwright E2E tests -->
```

Nếu có worker (Step 1 câu 5 = Có), tạo thêm:
`.agent/docs/architectures/worker_pipeline_architecture.md` theo cấu trúc tương tự AffHub.

## Step 10: Xác nhận với user

Sau khi tạo xong, hiển thị:
1. Cây thư mục cuối cùng
2. Số files đã tạo
3. Hỏi: "Bạn muốn bổ sung thêm file nào không?"

---

## Tips khi dùng

- **Chưa biết entities?** → Tạo skeleton trước, điền chi tiết khi thiết kế xong.
- **Thêm module mới?** → Tạo `{module}_architecture.md` + update `modules_functions.md` + `INDEX.md`.
- **Thêm entity mới?** → Update `entity_schema.md` (SoT) + module arch + `modules_functions.md` summary table.
- **Design decision?** → Ghi vào bảng "Quyết Định" trong `INDEX.md`.
