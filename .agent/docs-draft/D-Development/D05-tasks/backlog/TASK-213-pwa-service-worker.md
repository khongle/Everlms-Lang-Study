# Task: PWA + Service Worker

**ID:** TASK-213
**Unit:** — (lms_theme + service worker)
**Group:** G-PERFORMANCE
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Transform EverLMS into an installable Progressive Web App. Deliver a Web App Manifest with proper icons and theme metadata, and register a service worker implementing a layered caching strategy: cache-first for static assets, network-first for API responses, and a custom offline fallback page for navigation failures.

## Acceptance Criteria

- [ ] **Web App Manifest** (`/manifest.webmanifest`):
  - [ ] Fields: `name`, `short_name`, `description`, `start_url` ("/"), `display` ("standalone"), `theme_color`, `background_color`, `icons` (192x192 + 512x512 PNG)
  - [ ] Served with `Content-Type: application/manifest+json`
  - [ ] Linked from `<head>` via `<link rel="manifest">`
  - [ ] `theme-color` meta tag matches manifest theme_color
- [ ] **Service Worker** (`/sw.js`):
  - [ ] Registered from `lms_theme/js/sw-register.js` with `scope: '/'`
  - [ ] **Cache-first strategy** for: CSS, JS, fonts, images (extensions: css, js, woff2, png, jpg, svg)
  - [ ] **Network-first strategy** for: `/api/*` routes (fall back to stale cache if offline)
  - [ ] **Navigation fallback:** failed navigation requests (HTML page loads) serve `/offline` page
  - [ ] Cache versioned: `lms-static-v{BUILD_HASH}`; old caches purged on activate
  - [ ] SW update: `skipWaiting()` + `clients.claim()` for immediate take-over
- [ ] **Offline fallback page** (`/offline`):
  - [ ] Drupal route served by `lms_theme` or simple static HTML embedded in SW
  - [ ] Friendly message: "You appear to be offline. Please check your connection."
  - [ ] Cached during SW install phase
- [ ] **PWA installability:**
  - [ ] Lighthouse PWA audit: all installability checks pass (manifest valid, SW registered, HTTPS)
  - [ ] "Add to Home Screen" prompt appears on Android Chrome
- [ ] PHPUnit test (or Jest for SW): cache strategy routing logic unit-tested; verify correct strategy selected per URL pattern

## Technical Notes

- Module: `lms_theme` — manifest route in `lms_theme.routing.yml`; SW file at `web/sw.js` (must be at root scope)
- SW file cannot be inside `web/themes/` subdirectory if scope must be `/` — serve via controller or place directly in `web/`
- Build hash: inject via Drupal JS settings (`drupalSettings.lms.buildHash`) — generated from `filemtime()` of aggregated CSS/JS
- Library: `lms_theme/sw-register` (weight: -1000, loaded in `<head>`) with `sw-register.js`
- Icons: generate from master logo SVG; sizes 192, 512; store in `lms_theme/images/icons/`
- Workbox is NOT required — vanilla SW fetch event handlers sufficient to keep dependencies minimal

## Dependencies

- TASK-021 (cache strategy — aligns SW caching with server-side cache layer)
- TASK-097 (CloudFlare CDN — ensure SW `Cache-Control` headers not overridden by CDN)

## Definition of Done

- [ ] Code complete — manifest, SW, offline page, SW registration script
- [ ] G1-G4 gates passed
- [ ] Lighthouse PWA: all installability checks pass
- [ ] Offline mode: navigating to `/courses` while offline shows offline page
- [ ] Old cache version purged after SW update
- [ ] Tests written + passing
- [ ] No new debt introduced
