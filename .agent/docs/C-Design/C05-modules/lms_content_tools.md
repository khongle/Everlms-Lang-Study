# C05 Module - lms_content_tools

**Name:** Content tools / seed · **Phase:** 13 · **Epic:** [E13](../../D-Development/D02-epics/E13-content-tools.md) · **Status:** done

## Responsibility
Module chuẩn **Content Tools** của framework: generate/import/clean nội dung demo+seed từ `.agent/generated/*.json` và `.agent/seed/seed-input.json`, có UI form và Drush command. ~3 route.

## Owned / related entities
- Không có (tạo entity cho các module khác).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-023](../../B-Requirements/B03-features/F-023-content-tools-and-seeding.md) | Content tools & seeding |

## Business workflows (B04)
- —

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Seed input form | `/admin/lms/content-tools` |

## Cross references
- Module chuẩn theo [C01](../C01-architecture-decision/_index.md); input [SRC-10/11](../../00-project-input/README.md).

## Dependencies
- lms_base.

## Implementation notes
- `ContentToolsManager` + `ContentToolsController` + `SeedInputForm`; Drush `lms:seed-input`, `lms:import`, `lms:clean`. Tạo ra data state đã được tài liệu hóa (40 course / 600 lesson / …).
