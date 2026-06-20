# F-021 - Users, roles & impersonation

**Status:** built
**Primary module:** [open_switcher](../../C-Design/C05-modules/open_switcher.md) (+ Drupal user/RBAC)
**Epic:** [E01 Foundation](../../D-Development/D02-epics/E01-foundation.md)
**Related workflows:** [WF-007 User registration & onboarding](../B04-business-workflows/WF-007-user-registration-and-onboarding.md)
**Acceptance:** [B05 F-021](../B05-acceptance-criteria/F-021-users-roles-and-impersonation.md)
**Source:** `web/modules/custom/open_switcher/`, `config/sync/user.role.*`

## Requirement
The platform defines 7 roles — administrator, manager, instructor, content_editor, student, authenticated, anonymous — with permission-scoped access to catalog, learning, commerce, and admin surfaces. Support staff can **impersonate** a user (`open_switcher`) to reproduce issues, then switch back safely.

## Business value
A clear role model is the basis of all access control and monetisation; impersonation cuts support resolution time dramatically.

## Scope notes
- Roles/permissions defined in config (`user.role.*`); enforced on routes/entities.
- `open_switcher` provides the user-switch/impersonation utility (note: package "Open Platform", not `lms_*`).
- Registration/login flows are Drupal core + customisations; onboarding is WF-007.

## Cross references
- Design: [C02 user roles](../../C-Design/C02-user-roles/_index.md), [C05 open_switcher](../../C-Design/C05-modules/open_switcher.md)
