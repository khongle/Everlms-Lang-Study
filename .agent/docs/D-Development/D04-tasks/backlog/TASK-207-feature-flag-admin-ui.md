# Task: Feature Flag Admin UI

**ID:** TASK-207
**Unit:** — (lms_feature_flag)
**Group:** G-OPERATIONS
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Build an admin UI for toggling feature flags without a code deploy, aligned with `H05-feature-flags.md`. Admins can view all registered flags, toggle them on/off, and set a percentage rollout. All state changes are audited. Wraps Drupal config entities; no external service required by default.

## Acceptance Criteria

- [ ] **FeatureFlag config entity:**
  - [ ] Fields: `id` (machine name), `label`, `description`, `status` (on/off), `rollout_percentage` (0–100), `environments` (array: dev/staging/prod), `owner` (team/person), `created`, `changed`
  - [ ] Stored as Drupal config entity (exportable via `drush cex`)
- [ ] **Admin list** (`/admin/config/lms/feature-flags`):
  - [ ] Table: Flag ID, Label, Status toggle (AJAX in-place), Rollout %, Environments, Owner, Last changed, Edit/Delete actions
  - [ ] "Add flag" button → flag edit form
  - [ ] Filter: by status, by environment
- [ ] **Toggle behaviour:**
  - [ ] Status toggled via AJAX; no full page reload
  - [ ] Change written to config entity immediately; config cache invalidated
  - [ ] `FeatureFlagService::isEnabled(string $flag_id, ?int $uid = null): bool` respects rollout %: uses `hash(uid + flag_id) % 100 < rollout_percentage`
- [ ] **Rollout percentage:**
  - [ ] 0% = disabled for everyone; 100% = enabled for everyone
  - [ ] In-between: deterministic per-user rollout (same user always gets same result)
- [ ] **Audit trail:**
  - [ ] Every toggle/edit writes to `lms_base` audit service (TASK-210) with old + new value
- [ ] **Developer integration:**
  - [ ] PHP: `\Drupal::service('lms_feature_flag.service')->isEnabled('my_flag')`
  - [ ] Twig: `{{ feature_flag('my_flag') }}`
  - [ ] README in module documents how to register a new flag
- [ ] PHPUnit test: rollout % determinism (same uid always same result); 0% always false; 100% always true

## Technical Notes

- New module: `web/modules/custom/lms_feature_flag/`
- Config entity: `src/Entity/FeatureFlag.php` extends `ConfigEntityBase`
- Service: `src/FeatureFlagService.php` — reads config entities; result cached in static array per request
- Twig extension: `src/TwigExtension/FeatureFlagTwigExtension.php`
- Rollout hash: `crc32($uid . $flag_id) % 100` — stable, no DB write needed
- Permissions: `administer lms feature flags` (admin only)
- Environment detection: reads `getenv('APP_ENV')` or Drupal settings `$settings['environment']`

## Dependencies

- None (foundational module, used by other tasks)

## Definition of Done

- [ ] Code complete — entity, service, admin UI, Twig extension, audit integration
- [ ] G1-G4 gates passed
- [ ] Toggle changes reflected immediately without cache flush via Drush
- [ ] Tests written + passing
- [ ] No new debt introduced
