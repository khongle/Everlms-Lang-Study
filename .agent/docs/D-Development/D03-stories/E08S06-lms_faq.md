---
story_id: E08S06
story_type: module-data
primary_source: C05-modules/lms_faq.md
owning_modules: [lms_faq]
adequacy: PASS
---

# E08S06 - lms_faq build spine

Entity FAQ (có category, giới hạn theo course). **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| FAQ entity + category | resolved |
| Public accordion | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_faq.md) · [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md)

## Source Trace
- **Module:** `lms_faq` — [C05](../../C-Design/C05-modules/lms_faq.md) · [D07](../D07-code-map/lms_faq.md)
- **Entity:** [lms_faq](../../C-Design/C04-entities/lms_faq.md)
- **Feature:** [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md) blog-and-faq-content
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E08S06T01` FAQ entity + category | resolved |
| `E08S06T02` Public accordion | resolved |

## Adequacy Checklist
- [x] Entity: `lms_faq` (C04, translatable, giới hạn theo course)
- [x] Route: public FAQ accordion route (C05/D07)
- [x] Access/permission: admin quản lý, public đọc — đã resolved
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
