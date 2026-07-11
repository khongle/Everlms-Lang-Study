---
story_id: E05S01
story_type: module-data
primary_source: C05-modules/lms_certificate.md
owning_modules: [lms_certificate]
adequacy: PASS
---

# E05S01 - lms_certificate build spine

Certificate template + cert đã cấp; mPDF; cấp phát idempotent khi có QuizPassedEvent. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Template + issued entities | resolved |
| Event subscriber + mPDF render | resolved |
| Idempotency | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_certificate.md) · [WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md) · [F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md)

## Source Trace
- **Module:** `lms_certificate` — [C05](../../C-Design/C05-modules/lms_certificate.md) · [D07](../D07-code-map/lms_certificate.md)
- **Entity:** [lms_certificate_template](../../C-Design/C04-entities/lms_certificate_template.md), [lms_user_certificate](../../C-Design/C04-entities/lms_user_certificate.md)
- **Feature:** [F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md) certificate-issuance
- **Workflow:** [WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md) certificate-issuance

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E05S01T01` Template + issued entities | resolved |
| `E05S01T02` Event subscriber + mPDF render | resolved |
| `E05S01T03` Idempotency (không cấp trùng khi QuizPassedEvent lặp) | resolved |

## Adequacy Checklist
- [x] Entity: `lms_certificate_template` + `lms_user_certificate` (C04)
- [x] Route: download/view certificate route (C05/D07)
- [x] Access/permission: chỉ owner + admin xem/tải certificate
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
