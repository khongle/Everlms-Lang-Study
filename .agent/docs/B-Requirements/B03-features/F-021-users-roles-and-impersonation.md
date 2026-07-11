# F-021 - Users, roles & impersonation

**Status:** built
**Primary module:** [open_switcher](../../C-Design/C05-modules/open_switcher.md) (+ Drupal user/RBAC)
**Epic:** [E01 Foundation](../../D-Development/D02-epics/E01-foundation.md)
**Related workflows:** [WF-007 User registration & onboarding](../B04-business-workflows/WF-007-user-registration-and-onboarding.md)
**Acceptance:** [B05 F-021](../B05-acceptance-criteria/F-021-users-roles-and-impersonation.md)
**Source:** `web/modules/custom/open_switcher/`, `config/sync/user.role.*`

## Requirement
Platform định nghĩa 7 role — administrator, manager, instructor, content_editor, student, authenticated, anonymous — với quyền truy cập được scope theo permission tới catalog, learning, commerce và các bề mặt admin. Nhân viên support có thể **impersonate** một user (`open_switcher`) để tái hiện (reproduce) sự cố, rồi switch trở lại một cách an toàn.

## Business value
Một role model rõ ràng là nền tảng của toàn bộ access control và monetisation; impersonation rút ngắn đáng kể thời gian xử lý support.

## Scope notes
- Role/permission được định nghĩa trong config (`user.role.*`); được enforce trên route/entity.
- `open_switcher` cung cấp tiện ích user-switch/impersonation (lưu ý: thuộc package "Open Platform", không phải `lms_*`).
- Luồng registration/login dùng Drupal core + tùy biến (customisation); onboarding thuộc WF-007.

## Cross references
- Design: [C02 user roles](../../C-Design/C02-user-roles/_index.md), [C05 open_switcher](../../C-Design/C05-modules/open_switcher.md)
