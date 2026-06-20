# U08-admin-guide - Admin Guide

**For:** administrators, managers, content editors, instructors.

## Run / operate
- Start: `ddev start` → `ddev drush cr`. Full commands: [A01-CLAUDE](../../A-Project-Information/A01-CLAUDE/_index.md).
- Deploy/rollback/backup: [H02 runbooks](../../H-Operations/H02-runbooks/).

## Configure (admin)
- **AI providers + keys:** `/admin/config/lms/ai` (⚠️ move secrets to Key — R-001).
- **Payment gateways:** `/admin/config/everlms/payment`.
- **Zoom:** `/admin/config/lms/zoom`.
- **Roles/permissions:** standard Drupal + [C02](../../C-Design/C02-user-roles/_index.md).
- **Theme/skins:** theme settings (15 homepage skins).

## Manage (workspace `/workspace`, `/manage`)
- Courses, quizzes, users, orders, memberships, ratings, blog, FAQ, certificate templates, issued certificates.
- **Seed/demo content:** `/workspace/content-tools` or Drush `lms:import` / `lms:seed-input` / `lms:clean`.
- **Analytics:** `/admin/lms/reports/*`, `/workspace/reports`.
- **Notifications broadcast:** `/admin/lms/notifications/broadcast`.
- **Impersonate a user:** `/admin/switch-user` (then switch back).

## Known operational gaps
Secrets in config (R-001), no payment webhooks (R-002), email/push partial (R-005), ~0% tests (R-006) — see [E06](../../E-Quality/E06-implementation-status/_index.md) / [G02](../../G-Governance/G02-risk-register/_index.md).
