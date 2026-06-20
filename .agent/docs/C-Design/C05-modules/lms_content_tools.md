# C05 Module - lms_content_tools

**Name:** Content tools / seed · **Phase:** 13 · **Epic:** [E13](../../D-Development/D02-epics/E13-content-tools.md) · **Status:** done

## Responsibility
The framework's **Content Tools** standard module: generate/import/clean demo+seed content from `.agent/generated/*.json` and `.agent/seed/seed-input.json`, with a UI form and Drush commands. ~3 routes.

## Owned / related entities
- None (creates other modules' entities).

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
- Standard module per [C01](../C01-architecture-decision/_index.md); inputs [SRC-10/11](../../00-project-input/README.md).

## Dependencies
- lms_base.

## Implementation notes
- `ContentToolsManager` + `ContentToolsController` + `SeedInputForm`; Drush `lms:seed-input`, `lms:import`, `lms:clean`. Produces the documented data state (40 courses / 600 lessons / …).
