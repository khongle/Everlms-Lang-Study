# F-023 - Content tools & seeding

**Status:** built
**Primary module:** [lms_content_tools](../../C-Design/C05-modules/lms_content_tools.md)
**Epic:** [E13 Content tools](../../D-Development/D02-epics/E13-content-tools.md)
**Related workflows:** —
**Acceptance:** [B05 F-023](../B05-acceptance-criteria/F-023-content-tools-and-seeding.md)
**Source:** `web/modules/custom/lms_content_tools/`, `lms_base/`, `.agent/generated/`, `.agent/seed/`

## Requirement
Admins generate and import demo/seed content so a fresh install has a realistic catalog. Drush commands: `lms:seed-input` (load `.agent/seed/seed-input.json`), `lms:import` (import `.agent/generated/*.json`), `lms:generate-demo` (procedural demo), `lms:clean` (wipe generated content); a `SeedInputForm` provides a UI. This realises the framework's **Content Tools** standard module.

## Business value
Fast, repeatable seeding makes demos, QA, and onboarding trivial and guarantees the documented data state is reproducible.

## Scope notes
- `ContentToolsManager` + `ContentToolsController` + `SeedInputForm` (UI) + Drush commands.
- Generated JSON under `.agent/generated/` is the content source-of-truth for the seed.
- One of the 3 mandatory standard modules (Content Tools); see C01 architecture.

## Cross references
- Design: [C05 lms_content_tools](../../C-Design/C05-modules/lms_content_tools.md), [C01 standard modules](../../C-Design/C01-architecture-decision/_index.md)
- Input: [00-project-input SRC-10/11](../../00-project-input/README.md)
