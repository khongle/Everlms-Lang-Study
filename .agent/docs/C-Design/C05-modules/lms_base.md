# C05 Module - lms_base

**Name:** Base / shared services · **Phase:** 1 · **Epic:** [E01](../../D-Development/D02-epics/E01-foundation.md) · **Status:** done

## Responsibility
The shared foundation every other `lms_*` module depends on: common base classes, shared services/traits, helper utilities, and the demo-content Drush command. The dependency root of the module graph.

## Owned / related entities
- None (no domain entity); provides base/abstract building blocks reused by domain modules.

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-023](../../B-Requirements/B03-features/F-023-content-tools-and-seeding.md) | Content tools & seeding (Drush demo command) |

## Business workflows (B04)
- — (infrastructure)

## Integrations / APIs (B06 / C06)
- — (none directly)

## Backing pages
| Label | URL |
| ----- | --- |
| (none — services only) | — |

## Cross references
- Depended on by all `lms_*` domain modules. [C01 architecture](../C01-architecture-decision/_index.md).

## Dependencies
- none (Drupal core only).

## Implementation notes
- Ships `src/Commands/LmsDemoContentCommand.php` (`lms:generate-demo`, `lms:mock-certs` related helpers).
- Primary route group: n/a (library module). Runtime shape: services + Drush.
