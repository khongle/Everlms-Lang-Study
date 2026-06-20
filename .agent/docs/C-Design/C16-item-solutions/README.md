# C16-item-solutions - Item Solution Workbook

The implementation-translation layer: each sheet names a concept, each row names an item, and **"Drupal primitive to use"** gives the build approach. Bridge from "what is required" (C01–C15) to "how to build it" (D). Source-of-truth is the live code; these sheets are derived/maintained as markdown (no separate `.xlsx` for this rebuild).

Generated: 2026-06-21 (as-built). Sheet 10 (migration) is **N/A** — EverLMS is documented in place, not migrated from another platform.

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

Use C16 before implementation when deciding whether something is a content entity, config entity, taxonomy, route/controller, Form API form, Views display, workflow service, importer, exporter, component, or release gate.
