---
story_id: E01S01
story_type: module-foundation
primary_source: C05-modules/lms_base.md
owning_modules: [lms_base]
adequacy: PASS
glue: site-assembly
---

# E01S01 - lms_base build spine

Base service dùng chung + lệnh demo Drush. **Status:** done (as-built-backed).

**Glue role (site-assembly):** `lms_base` là module nền tảng mà mọi module khác phụ thuộc vào (xem [D02 E01](../D02-epics/E01-foundation.md): "Nền tảng chung ... mà mọi epic khác đều dựa vào"); nó tương đương vai trò install-profile/recipe lắp ráp site trong A06's 5-story-spine model. As-built dùng story-model gọn (1 story/module, xem [D01](../D01-dev-overview/_index.md)) thay vì một story `glue: site-assembly` tách riêng — tag này ánh xạ vai trò đã có sẵn, không tạo story mới. Bằng chứng site-assembly thực tế: [X00 scaffold bootstrap](../D04-tasks/done/X00-scaffold-bootstrap.md) (DDEV + Drupal 11 + `config/sync` (396 file) + 28 module `lms_*`/`open_switcher` enabled, `drush config:status` sạch).

## Tasks
| Task | Status |
| ---- | ------ |
| Base services/traits | resolved |
| Demo content Drush command | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_base.md) · [D07](../D07-code-map/lms_base.md)

## Source Trace
- **Module:** `lms_base` — [C05](../../C-Design/C05-modules/lms_base.md) · [D07](../D07-code-map/lms_base.md)
- **Entity:** n/a — module nền tảng (base services/traits dùng chung cho các module khác), không sở hữu entity riêng
- **Feature:** hạ tầng dùng chung cho toàn bộ B03 features, không gắn 1 F-xxx cụ thể
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E01S01T01` Base services/traits dùng chung | resolved |
| `E01S01T02` Demo content Drush command | resolved |

## Adequacy Checklist
- [x] Entity: n/a — module nền tảng, không định nghĩa entity
- [x] Route: n/a — không expose route riêng (chỉ service/trait + Drush command)
- [x] Access/permission: kế thừa permission hệ thống, không cần permission riêng
- [x] Test: Drush command đã smoke-test thủ công (as-built); automated test coverage nằm trong gap chung R-006/X01
