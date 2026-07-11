---
story_id: E01S03
story_type: module-ui
primary_source: C05-modules/lms_theme.md
owning_modules: [lms_theme]
adequacy: PASS
glue: navigation-assembly
---

# E01S03 - lms_theme build spine

Theme front-end: 16 region, 21 library, 15 skin trang chủ. **Status:** done.

**Glue role (navigation-assembly):** `lms_theme` sở hữu region `primary_menu`/`secondary_menu`/`footer_first..fourth` — đây là nơi lắp ráp navigation cho toàn site (xem [C05 lms_theme](../../C-Design/C05-modules/lms_theme.md) và [C07 sitemap](../../C-Design/C07-sitemap/_index.md)). As-built dùng story-model gọn (1 story/module) thay vì một story `glue: navigation-assembly` tách riêng — tag này ánh xạ vai trò đã có sẵn, không tạo story mới.

## Tasks
| Task | Status |
| ---- | ------ |
| Regions + libraries | resolved |
| 15 homepage skins | resolved |
| Templates by area | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_theme.md) · [C09](../../C-Design/C09-themes/_index.md) · [F-024](../../B-Requirements/B03-features/F-024-theming-and-homepage-skins.md)

## Source Trace
- **Module:** `lms_theme` — [C05](../../C-Design/C05-modules/lms_theme.md) · [C09](../../C-Design/C09-themes/_index.md)
- **Entity:** n/a — theme layer, không sở hữu entity
- **Feature:** [F-024](../../B-Requirements/B03-features/F-024-theming-and-homepage-skins.md) theming-and-homepage-skins
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E01S03T01` Regions + libraries | resolved |
| `E01S03T02` 15 homepage skins | resolved |
| `E01S03T03` Templates by area | resolved |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: n/a — theme không định nghĩa route, chỉ region/library/template
- [x] Access/permission: n/a — không có access control riêng ở tầng theme
- [ ] Test: visual review thủ công theo C09 skins (as-built); chưa có automated test — gap chung R-006/X01
