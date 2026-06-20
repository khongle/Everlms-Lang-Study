# Task: Maintenance Mode Toggle with Customizable Banner

**ID:** TASK-208
**Unit:** — (lms_base admin extension)
**Group:** G-OPERATIONS
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Extend Drupal core maintenance mode with a customizable site-wide banner, IP/role bypass lists, and a scheduled maintenance window (cron auto-enable/disable). Admins can put the site in maintenance mode from a single UI without touching `settings.php`.

## Acceptance Criteria

- [ ] **Admin UI** (`/admin/config/lms/maintenance`):
  - [ ] Toggle: Enable/Disable maintenance mode (writes to `system.maintenance` config)
  - [ ] Custom banner message (rich text via CKEditor-lite, max 500 chars)
  - [ ] Banner type: info / warning / critical (affects colour scheme)
  - [ ] Schedule: "Enable from" datetime + "Disable at" datetime (optional)
- [ ] **Banner display:**
  - [ ] When maintenance scheduled but not yet active: top-of-page banner "Scheduled maintenance on {date}" visible to all users
  - [ ] When maintenance active: Drupal core 503 page replaced with custom template (`lms_base/templates/maintenance-page.html.twig`) showing banner message
- [ ] **Bypass:**
  - [ ] IP whitelist: admin enters CIDR ranges; requests from matching IPs see site normally
  - [ ] Role bypass: configurable roles (default: `administrator`) see site normally
  - [ ] Bypass implemented in `MaintenanceModeSubscriber` on `kernel.request` with priority 300
- [ ] **Scheduled cron:**
  - [ ] `lms_base_cron()` checks scheduled window; enables/disables maintenance mode if window boundary passed
  - [ ] Cron runs at least every 5 minutes (standard Drupal cron interval)
- [ ] PHPUnit test: bypass logic (IP match, role match); schedule activation/deactivation logic

## Technical Notes

- Module: `lms_base` — new `MaintenanceModeController`, `MaintenanceModeSubscriber`
- Config: `lms_base.maintenance_settings` — fields: message, banner_type, schedule_start, schedule_end, bypass_ips (array), bypass_roles (array)
- IP matching: use `IpUtils::checkIp()` from Symfony (already available in Drupal)
- Custom 503 template overrides `maintenance-page.html.twig` via theme suggestion hook
- Do NOT break Drupal core maintenance mode API; wrap don't replace

## Dependencies

- None (core Drupal maintenance mode extended)

## Definition of Done

- [ ] Code complete — admin form, subscriber, cron, custom maintenance template
- [ ] G1-G4 gates passed
- [ ] Admin role can view site during maintenance; anonymous user sees 503
- [ ] Scheduled window activates/deactivates correctly via cron simulation
- [ ] Tests written + passing
- [ ] No new debt introduced
