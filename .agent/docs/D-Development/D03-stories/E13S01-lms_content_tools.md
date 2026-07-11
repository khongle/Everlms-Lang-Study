---
story_id: E13S01
story_type: module-foundation
primary_source: C05-modules/lms_content_tools.md
owning_modules: [lms_content_tools]
adequacy: PASS
glue: demo-seed
---

# E13S01 - lms_content_tools build spine

Module chuẩn Content Tools: import/seed/clean + UI + Drush. **Status:** done.

**Glue role (demo-seed):** `lms_content_tools` host service/Drush import-seed-clean chuẩn (xem [C05](../../C-Design/C05-modules/lms_content_tools.md)); [X00 scaffold bootstrap](../D04-tasks/done/X00-scaffold-bootstrap.md) gọi `ddev drush lms:generate-demo && ddev drush lms:mock-certs` để seed demo data + mock certificate cho bootstrap. As-built dùng story-model gọn (1 story/module) thay vì một story `glue: demo-seed` tách riêng — tag này ánh xạ vai trò đã có sẵn, không tạo story mới.

## Tasks
| Task | Status |
| ---- | ------ |
| Import/seed/clean service + Drush | resolved |
| Seed input form | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_content_tools.md) · [F-023](../../B-Requirements/B03-features/F-023-content-tools-and-seeding.md)

## Source Trace
- **Module:** `lms_content_tools` — [C05](../../C-Design/C05-modules/lms_content_tools.md) · [D07](../D07-code-map/lms_content_tools.md)
- **Entity:** n/a — tooling (import/seed/clean) thao tác trên entity đã có, không định nghĩa entity riêng
- **Feature:** [F-023](../../B-Requirements/B03-features/F-023-content-tools-and-seeding.md) content-tools-and-seeding
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E13S01T01` Import/seed/clean service + Drush | resolved |
| `E13S01T02` Seed input form | resolved |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: seed input form route đã resolved
- [x] Access/permission: chỉ admin dùng Content Tools, đã resolved
- [ ] Test: Drush command đã smoke-test thủ công; automated test — gap chung R-006/X01
