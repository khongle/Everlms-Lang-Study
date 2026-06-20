# Task: Banner / Announcement System

**ID:** TASK-211
**Unit:** — (lms_banner)
**Group:** G-OPERATIONS
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow admins to compose top-of-page site-wide banners for maintenance notices, promotions, and important news. Banners have three severity levels, support audience filtering (all users / authenticated / role-specific), can be scheduled, and include a dismiss button whose state is stored per-user in a cookie/localStorage.

## Acceptance Criteria

- [ ] **Banner config entity** (`SiteBanner`):
  - [ ] Fields: `label`, `message` (formatted text, max 300 chars), `level` (info/warning/critical), `audience` (all/authenticated/role), `audience_roles` (array), `active_from` (datetime), `active_until` (datetime), `dismissible` (boolean), `created`, `changed`
  - [ ] Multiple banners can be active simultaneously; displayed in order of `level` (critical first)
- [ ] **Admin UI** (`/admin/config/lms/banners`):
  - [ ] CRUD for `SiteBanner` entities
  - [ ] Preview link: shows banner in context on homepage
  - [ ] Toggle active/inactive without editing the entity
- [ ] **Display:**
  - [ ] Banners rendered via a Drupal Block placed in a banner region above the header
  - [ ] Info: blue; Warning: amber; Critical: red
  - [ ] Dismiss button (if `dismissible = true`): hides banner for current session; preference stored in `localStorage` key `lms_dismissed_banners` (array of banner IDs)
  - [ ] Dismissed state is client-side only (no DB write); resets on new session
- [ ] **Audience filtering:**
  - [ ] `BannerAccessService::isVisible(SiteBanner $banner, AccountInterface $account): bool`
  - [ ] `all`: shown to everyone; `authenticated`: only logged-in; `role`: only users with specified role
  - [ ] Filtering computed server-side in block; anonymous users see only `all` banners (cache varies by `roles`)
- [ ] **Schedule:**
  - [ ] `active_from` / `active_until` enforced at render time (not cron-based; avoids cache complexity)
  - [ ] Block cache max-age = min time until next boundary (so banner appears/disappears promptly)
- [ ] PHPUnit test: access service for each audience type; schedule boundary logic; critical banner sorted first

## Technical Notes

- New module: `web/modules/custom/lms_banner/`
- Config entity: `src/Entity/SiteBanner.php` extends `ConfigEntityBase`
- Block plugin: `src/Plugin/Block/SiteBannerBlock.php` — cache contexts `['user.roles']`, max-age computed
- Twig template: `lms_banner/templates/site-banner.html.twig`
- JS: `lms_banner/js/banner-dismiss.js` — reads/writes `localStorage`; hides dismissed banners on DOMContentLoaded
- No external service dependency

## Dependencies

- TASK-208 (maintenance mode — shares banner display concept; do not conflict with maintenance 503 page)

## Definition of Done

- [ ] Code complete — entity, block, access service, Twig, dismiss JS
- [ ] G1-G4 gates passed
- [ ] Critical banner appears above info banner when both active
- [ ] Dismissed banner stays dismissed across page navigation within session
- [ ] Tests written + passing
- [ ] No new debt introduced
