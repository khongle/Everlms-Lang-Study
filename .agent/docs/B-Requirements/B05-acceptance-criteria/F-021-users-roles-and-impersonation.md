# F-021 Acceptance Criteria - Users, roles & impersonation

## Core criteria
1. **Given** the 7 configured roles, **when** a user holds a role, **then** route/entity access matches that role's permissions.
2. **Given** a new web registration, **when** the account is created, **then** it receives the `student` role by default.
3. **Given** an admin/support user with switch permission, **when** they impersonate another user (`open_switcher`), **then** they browse as that user and can switch back.
4. **Given** a user without switch permission, **when** they attempt impersonation, **then** it is denied.

## Evidence expected
- Module: [open_switcher](../../C-Design/C05-modules/open_switcher.md); roles in [C02](../../C-Design/C02-user-roles/_index.md).
- Test: per-role access matrix test; impersonate + restore test; permission-denied test (E03).
