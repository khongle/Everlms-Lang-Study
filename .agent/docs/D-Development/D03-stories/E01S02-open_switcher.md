---
story_id: E01S02
story_type: module-foundation
primary_source: C05-modules/open_switcher.md
owning_modules: [open_switcher]
adequacy: PASS
---

# E01S02 - open_switcher build spine

Tiện ích impersonation/switch-back cho user. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Switch + restore controllers | resolved |
| Switch permission gate | resolved |

## Links
[C05](../../C-Design/C05-modules/open_switcher.md) · [F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md) · [D07](../D07-code-map/open_switcher.md)

## Source Trace
- **Module:** `open_switcher` — [C05](../../C-Design/C05-modules/open_switcher.md) · [D07](../D07-code-map/open_switcher.md)
- **Entity:** n/a — tiện ích impersonation, không có entity riêng
- **Feature:** [F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md) users-roles-and-impersonation
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E01S02T01` Switch + restore controllers | resolved |
| `E01S02T02` Switch permission gate | resolved |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: switch/restore có route riêng (xem D07)
- [x] Access/permission: permission gate "switch users"/restore đã resolved
- [ ] Test: chưa có automated test riêng — thuộc gap chung R-006/X01
