# Task: Critical CSS Inlining

**ID:** TASK-214
**Unit:** — (lms_theme build pipeline)
**Group:** G-PERFORMANCE
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Extract above-the-fold CSS for the highest-traffic page types (homepage, course detail, lesson player, user dashboard) and inline it in `<head>`. Defer loading of non-critical CSS to eliminate render-blocking resources. Implementation is build-pipeline-based to avoid runtime PHP overhead.

## Acceptance Criteria

- [ ] **Critical CSS extracted** for 4 page templates:
  - [ ] Homepage (`page--front.html.twig`)
  - [ ] Course detail (`page--courses--{id}.html.twig`)
  - [ ] Lesson player (`page--courses--{id}--lessons--{id}.html.twig`)
  - [ ] User dashboard (`page--my-dashboard.html.twig`)
- [ ] **Inlining:**
  - [ ] Critical CSS embedded as `<style>` block in `<head>` before any external `<link>` tags
  - [ ] Critical CSS files committed as build artifacts: `lms_theme/css/critical/{template}.critical.css`
  - [ ] Template includes: `{% include '@lms_theme/critical/homepage.critical.css.twig' %}` (or similar pattern)
- [ ] **Non-critical CSS deferred:**
  - [ ] Main stylesheet loaded with `media="print" onload="this.media='all'"` pattern (or `<link rel="preload">` + `as="style"`)
  - [ ] `<noscript>` fallback includes normal `<link>` for users without JS
- [ ] **Build tooling:**
  - [ ] `npm run build:critical` script added to `package.json` using `critical` npm package (or Puppeteer-based extraction)
  - [ ] Script configuration in `lms_theme/build/critical.config.js`: page URLs, viewport size (1280×768)
  - [ ] CI/CD: `build:critical` runs on deploy to staging/prod; output committed or artifact-uploaded
- [ ] **Baseline vs after measurement:**
  - [ ] Lighthouse LCP improvement documented in task artifacts
  - [ ] No visual regression (above-fold appearance unchanged)
- [ ] No Drupal PHP runtime changes; pure build-time concern

## Technical Notes

- Module: `lms_theme` — build pipeline additions only; no new PHP service
- Critical CSS tool: `npm install --save-dev critical` (penthouse under the hood)
- `critical.config.js`: define `src` (local DDEV URL), `target` (output file), `width`, `height`, `ignore` (third-party fonts)
- Twig include pattern: `{{ source('@lms_theme/css/critical/homepage.critical.css') }}` wrapped in `<style>` tag in page template
- Non-critical defer: modify `lms_theme.libraries.yml` to add `attributes: {media: print, onload: "this.media='all'"}` on main stylesheet
- Ensure CloudFlare (TASK-097) doesn't strip `onload` attribute from `<link>` tags (Rocket Loader can interfere)

## Dependencies

- TASK-021 (cache strategy — critical CSS complements server-side caching)
- TASK-097 (CloudFlare — ensure no attribute stripping)

## Definition of Done

- [ ] Code complete — build script, critical CSS files, Twig inline includes, deferred link tags
- [ ] G1-G4 gates passed
- [ ] Lighthouse render-blocking resources: 0 for 4 target pages
- [ ] `npm run build:critical` reproducible in CI (no browser dependency issues)
- [ ] No new debt introduced
