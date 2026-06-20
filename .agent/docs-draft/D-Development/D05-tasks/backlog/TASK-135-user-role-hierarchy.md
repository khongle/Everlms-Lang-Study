# Task: Role Hierarchy and Delegation

**ID:** TASK-135
**Unit:** U-010 (lms_user)
**Group:** G-USER-MGMT (extension)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement a role hierarchy with scoped delegation rules: Managers can assign the Instructor role (but not Admin or Manager); Admins can assign any role. A UI for role assignment replaces direct user edit, making the flow safe against privilege escalation. The role matrix is documented in design doc C02.

## Context

- Module: `lms_user` (or extend `lms_base`)
- Current state: Drupal's default role assignment is not scoped — any user with `administer users` can assign any role
- Roles in use: `learner`, `instructor`, `manager`, `admin` (and Drupal `administrator`)

## Acceptance Criteria

- [ ] Role hierarchy config: `lms_user.role_hierarchy` config object maps `{role: [assignable_roles]}`
  - `admin` → can assign: `learner`, `instructor`, `manager`
  - `manager` → can assign: `learner`, `instructor`
  - `instructor` → can assign: none
- [ ] Role assignment UI at `/admin/lms/users/{uid}/roles`: shows only assignable roles based on current user's role
- [ ] Saving a role the current user is not permitted to assign returns 403
- [ ] Existing Drupal user edit form (`/user/{uid}/edit`) role checkboxes hidden; redirects to custom role assignment page
- [ ] Role delegation log: every role change recorded in `watchdog` with granter UID, grantee UID, role, action (add/remove)
- [ ] Admin dashboard widget: list of recent role changes (last 20)
- [ ] Role matrix documented in C02 design doc reference (link in Technical Notes)

## Technical Notes

- New service: `web/modules/custom/lms_user/src/RoleHierarchyService.php`
- Method: `getAllowedRolesToAssign(AccountInterface $granter): array`
- Route guard: `RoleAssignmentAccessCheck` checks that granter may modify the requested role
- Config schema: `web/modules/custom/lms_user/config/schema/lms_user.schema.yml` — add `role_hierarchy` mapping
- Drupal user edit form alter: `hook_form_user_form_alter` — hide `roles` element; add inline link to role assignment page
- Audit log: `\Drupal::logger('lms_role_delegation')->info(...)`
- C02 doc reference: `.agent/docs/C-Design/C02-data-model/` (to be updated separately)

## Dependencies

- TASK-027 (user CRUD, existing role definitions)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
