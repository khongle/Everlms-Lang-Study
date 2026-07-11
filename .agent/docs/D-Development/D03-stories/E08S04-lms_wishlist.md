---
story_id: E08S04
story_type: module-data
primary_source: C05-modules/lms_wishlist.md
owning_modules: [lms_wishlist]
adequacy: PASS
---

# E08S04 - lms_wishlist build spine

Course lưu để xem sau. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Wishlist entity + AJAX toggle | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_wishlist.md) · [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md)

## Source Trace
- **Module:** `lms_wishlist` — [C05](../../C-Design/C05-modules/lms_wishlist.md) · [D07](../D07-code-map/lms_wishlist.md)
- **Entity:** [lms_wishlist](../../C-Design/C04-entities/lms_wishlist.md)
- **Feature:** [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md) learner-notes-and-wishlist
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E08S04T01` Wishlist entity + AJAX toggle | resolved |

## Adequacy Checklist
- [x] Entity: `lms_wishlist` (C04)
- [x] Route: AJAX toggle trên course catalog/detail (C05/D07)
- [x] Access/permission: chỉ owner xem/sửa wishlist của mình
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
