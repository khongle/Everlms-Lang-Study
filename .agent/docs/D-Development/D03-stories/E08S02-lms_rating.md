---
story_id: E08S02
story_type: module-data
primary_source: C05-modules/lms_rating.md
owning_modules: [lms_rating]
adequacy: PASS
---

# E08S02 - lms_rating build spine

Rating/review + aggregate + moderation. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Rating entity + aggregate | resolved |
| Approve/feature moderation | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_rating.md) · [F-014](../../B-Requirements/B03-features/F-014-ratings-and-reviews.md)

## Source Trace
- **Module:** `lms_rating` — [C05](../../C-Design/C05-modules/lms_rating.md) · [D07](../D07-code-map/lms_rating.md)
- **Entity:** [lms_rating](../../C-Design/C04-entities/lms_rating.md)
- **Feature:** [F-014](../../B-Requirements/B03-features/F-014-ratings-and-reviews.md) ratings-and-reviews
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E08S02T01` Rating entity + aggregate | resolved |
| `E08S02T02` Approve/feature moderation | resolved |

## Adequacy Checklist
- [x] Entity: `lms_rating` (C04)
- [x] Route: rating submit/list route trên course detail (C05/D07)
- [x] Access/permission: approve/feature moderation đã resolved
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
