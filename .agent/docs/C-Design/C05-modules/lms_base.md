# C05 Module - lms_base

**Name:** Base / shared services · **Phase:** 1 · **Epic:** [E01](../../D-Development/D02-epics/E01-foundation.md) · **Status:** done

## Responsibility
Nền tảng dùng chung mà mọi module `lms_*` khác đều phụ thuộc vào: base class chung, service/trait dùng chung, helper utility, và Drush command demo-content. Là gốc dependency của toàn bộ module graph.

## Owned / related entities
- Không có (không có domain entity); cung cấp các building block base/abstract được các module domain tái sử dụng.

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-023](../../B-Requirements/B03-features/F-023-content-tools-and-seeding.md) | Content tools & seeding (Drush demo command) |

## Business workflows (B04)
- — (hạ tầng)

## Integrations / APIs (B06 / C06)
- — (không có trực tiếp)

## Backing pages
| Label | URL |
| ----- | --- |
| (none — services only) | — |

## Cross references
- Được mọi module domain `lms_*` phụ thuộc vào. [C01 architecture](../C01-architecture-decision/_index.md).

## Dependencies
- không có (chỉ Drupal core).

## Implementation notes
- Cung cấp `src/Commands/LmsDemoContentCommand.php` (helper liên quan tới `lms:generate-demo`, `lms:mock-certs`).
- Route group chính: n/a (library module). Runtime shape: services + Drush.
