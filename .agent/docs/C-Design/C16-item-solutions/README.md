# C16-item-solutions - Item Solution Workbook

Layer chuyển đổi sang implementation: mỗi sheet đặt tên cho một concept, mỗi dòng đặt tên cho một item, và cột **"Drupal primitive to use"** cho biết cách tiếp cận để build. Đây là cầu nối từ "cái gì được yêu cầu" (C01–C15) sang "build nó như thế nào" (D). Source-of-truth là code thực tế đang chạy; các sheet này được derive/duy trì dưới dạng markdown (không có file `.xlsx` riêng cho lần rebuild này).

Generated: 2026-06-21 (as-built). Sheet 10 (migration) là **N/A** — EverLMS được tài liệu hóa tại chỗ (in place), không phải migrate từ platform khác.

| Sheet | Markdown | Rows | Purpose |
| ----- | -------- | ---- | ------- |
| 01 | [01-entities.md](01-entities.md) | 29 | data objects → primitive |
| 02 | [02-modules.md](02-modules.md) | 28 | module build choices |
| 03 | [03-pages-routes.md](03-pages-routes.md) | 51 | pages → build approach |
| 04 | [04-forms-actions.md](04-forms-actions.md) | key forms | Form API + actions |
| 05 | [05-views-lists.md](05-views-lists.md) | lists | list/report solutions |
| 06 | [06-workflows.md](06-workflows.md) | 10 | workflow/state solutions |
| 07 | [07-usecases.md](07-usecases.md) | 10 | usecase → work |
| 08 | [08-roles-access.md](08-roles-access.md) | 7 | role/permission solutions |
| 09 | [09-import-export.md](09-import-export.md) | tools | importer/export solutions |
| 11 | [11-templates-ui.md](11-templates-ui.md) | components | template/component solutions |
| 12 | [12-objects.md](12-objects.md) | 5 | object-level solutions |
| 13 | [13-release-ops.md](13-release-ops.md) | gates | quality/release/ops solutions |

Dùng C16 trước khi implementation khi cần quyết định một thứ gì đó là content entity, config entity, taxonomy, route/controller, Form API form, Views display, workflow service, importer, exporter, component, hay release gate.
