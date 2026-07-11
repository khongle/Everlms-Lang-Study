# F-023 - Content tools & seeding

**Status:** built
**Primary module:** [lms_content_tools](../../C-Design/C05-modules/lms_content_tools.md)
**Epic:** [E13 Content tools](../../D-Development/D02-epics/E13-content-tools.md)
**Related workflows:** —
**Acceptance:** [B05 F-023](../B05-acceptance-criteria/F-023-content-tools-and-seeding.md)
**Source:** `web/modules/custom/lms_content_tools/`, `lms_base/`, `.agent/generated/`, `.agent/seed/`

## Requirement
Admin generate và import nội dung demo/seed để một bản cài mới (fresh install) có một catalog thực tế. Các drush command: `lms:seed-input` (load `.agent/seed/seed-input.json`), `lms:import` (import `.agent/generated/*.json`), `lms:generate-demo` (demo dạng procedural), `lms:clean` (xóa sạch nội dung đã generate); một `SeedInputForm` cung cấp giao diện UI. Đây chính là hiện thực hóa standard module **Content Tools** của framework.

## Business value
Seeding nhanh, lặp lại được (repeatable) giúp demo, QA và onboarding trở nên đơn giản, đồng thời đảm bảo trạng thái dữ liệu đã document có thể tái tạo (reproducible).

## Scope notes
- `ContentToolsManager` + `ContentToolsController` + `SeedInputForm` (UI) + các drush command.
- JSON được generate trong `.agent/generated/` là source-of-truth nội dung cho seed.
- Một trong 3 standard module bắt buộc (Content Tools); xem kiến trúc ở C01.

## Cross references
- Design: [C05 lms_content_tools](../../C-Design/C05-modules/lms_content_tools.md), [C01 standard modules](../../C-Design/C01-architecture-decision/_index.md)
- Input: [00-project-input SRC-10/11](../../00-project-input/README.md)
