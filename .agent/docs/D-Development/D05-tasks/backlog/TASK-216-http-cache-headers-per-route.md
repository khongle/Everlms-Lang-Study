# Task: HTTP Cache Headers Per Route

**ID:** TASK-216
**Unit:** — (lms_base cache config)
**Group:** G-PERFORMANCE
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Configure appropriate HTTP cache headers on a per-route basis via Drupal's `pageCacheKillSwitch` and `Cache-Control` response headers. Public anonymous pages get long cache lifetimes; authenticated and API routes are not cached. This enables effective caching at the CDN (TASK-097) and reverse-proxy layers.

## Acceptance Criteria

- [ ] **Long-cache routes (anonymous users):**
  - [ ] `/` — `Cache-Control: public, max-age=3600, s-maxage=86400`
  - [ ] `/courses`, `/courses/*` (listing + detail) — `public, max-age=1800, s-maxage=3600`
  - [ ] `/blog`, `/blog/*` — `public, max-age=3600, s-maxage=86400`
  - [ ] `/about`, `/pricing`, `/faq` — `public, max-age=86400, s-maxage=604800`
  - [ ] `Vary: Accept-Encoding` added; `Vary: Cookie` removed for these routes
- [ ] **No-cache routes:**
  - [ ] `/my-*` (dashboard, account, subscription, payment methods) — `Cache-Control: no-store, private`
  - [ ] `/api/*` — `Cache-Control: no-store` (unless individual API controller overrides)
  - [ ] `/user/*`, `/admin/*` — `Cache-Control: no-store, private`
  - [ ] `pageCacheKillSwitch` called in applicable controllers/event subscribers
- [ ] **Implementation:**
  - [ ] `CacheHeaderSubscriber` on `kernel.response` event (priority -10, after Drupal core)
  - [ ] Route matching via `RouteMatchInterface::getRouteName()` prefix or path pattern
  - [ ] Config: `lms_base.cache_headers_settings` — arrays of long-cache paths and no-cache paths (admin-editable)
- [ ] **CDN integration:**
  - [ ] `Surrogate-Control: max-age=86400` header added for CloudFlare (TASK-097) on long-cache routes
  - [ ] Cache tags added to responses: `http_cache_tag_route_{route_name}` for targeted CloudFlare purge
- [ ] **Verification:**
  - [ ] `curl -I https://site.com/` shows `Cache-Control: public` and correct `max-age`
  - [ ] `curl -I https://site.com/my-dashboard` (authenticated session) shows `no-store`
- [ ] PHPUnit test: subscriber sets correct header per route pattern; no-cache routes have `no-store`

## Technical Notes

- Module: `lms_base` — new `CacheHeaderSubscriber` in `src/EventSubscriber/`
- Priority -10 ensures subscriber runs after Drupal's own response subscriber (priority 0)
- `pageCacheKillSwitch`: inject `\Drupal\Core\PageCache\ResponsePolicy\KillSwitch` service; call `trigger()` for no-cache routes
- Path matching: use `$request->getPathInfo()` with `str_starts_with()` checks (no regex for performance)
- Drupal internal page cache and Dynamic Page Cache must also be disabled for no-cache routes — handled by kill switch
- Do NOT override `Cache-Control` on routes that serve binary assets (images, files) — those are handled by Drupal file serving

## Dependencies

- TASK-097 (CloudFlare CDN — `Surrogate-Control` header consumed here)
- TASK-021 (cache strategy — HTTP headers align with server-side strategy)

## Definition of Done

- [ ] Code complete — subscriber, config schema, admin settings form
- [ ] G1-G4 gates passed
- [ ] CloudFlare caches `/courses` listing (verified via `CF-Cache-Status: HIT` header)
- [ ] `/my-dashboard` never cached by CDN or proxy
- [ ] Tests written + passing
- [ ] No new debt introduced
