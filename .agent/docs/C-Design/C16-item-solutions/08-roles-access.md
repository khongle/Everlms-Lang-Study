# C16 Sheet - 08 roles-access

Giải pháp cho role + permission. Nguồn: [C02](../C02-user-roles/_index.md), `config/sync/user.role.*`.

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Role | administrator | Role (is_admin) | core | super-role | user.role.administrator | access |
| Role | manager | Role + permissions (13) | various | operations scope | user.role.manager | access matrix |
| Role | content_editor | Role + permissions (23) | various | content scope | user.role.content_editor | access matrix |
| Role | instructor | Role + permissions (5) | lms_course | own-content scope | user.role.instructor | own vs all |
| Role | student | Role + permissions (4) | lms_progress | learner scope | user.role.student | gating |
| Permission | manage own courses | custom permission + entity access | lms_course | hook_entity_access / handler | C02 | own-only test |
| Access | content gating | route/entity access check | lms_progress | enrollment/membership check | C02/F-004 | gating matrix |
| Access | API auth | bearer token + scope | lms_mobile_api | token middleware | C06 INT mobile | 401/403 |
| Access | impersonation | switch permission | open_switcher | session switch + restore | C02 | perm-denied |
