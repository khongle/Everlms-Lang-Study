# Task: Visual Regression Baseline — Flagship Screens

**ID:** TASK-083
**Unit:** — (cross-cutting)
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập visual regression testing baseline dùng BackstopJS (hoặc Percy nếu budget cho phép) cho các flagship screens của EverLMS. Capture screenshots baseline của 6 màn hình chính trên desktop (1280px) và mobile (375px). CI pipeline (TASK-069) block PR nếu pixel diff >2% mà không có reviewer approval.

Approach BackstopJS (open-source, self-hosted) ưu tiên trước Percy (SaaS, có cost). Nếu switch sau này, config abstracted qua npm script interface.

## Acceptance Criteria
- [ ] BackstopJS config tại `tests/visual/backstop.json` — cover 6 screens × 2 viewports = 12 scenarios:
  - [ ] Homepage (`/`)
  - [ ] Course catalog (`/courses`)
  - [ ] Course detail (`/courses/{slug}`)
  - [ ] Lesson view (`/courses/{slug}/learn/{lesson}`)
  - [ ] Student dashboard (`/dashboard`)
  - [ ] Checkout page (`/checkout/{id}`)
  - [ ] Certificate verify page (`/certificate/verify/{uuid}`)
- [ ] Baseline screenshots committed tới repo tại `tests/visual/bitmaps_reference/`
- [ ] CI job `visual-regression` chạy `backstop test` — fail nếu diff >2%
- [ ] Approval workflow: reviewer run `backstop approve` để update baseline khi intentional UI change
- [ ] Dark theme (`lms_theme`) đúng màu sắc kiểm tra không bị color regression
- [ ] DDEV command: `ddev exec npm run visual:test` và `ddev exec npm run visual:approve`

## Technical Notes
- Theme: `lms_theme` (Bootstrap 5.3 dark)
- BackstopJS config: `tests/visual/backstop.json`
- Screenshots path: `tests/visual/bitmaps_reference/` (git-committed baseline)
- CI stage: after functional tests, before deploy
- Authenticated screens (dashboard, lesson, checkout): BackstopJS engine = Playwright, dùng `onBefore` script để login
- Login script: `tests/visual/engine_scripts/puppet/clickAndHoverHelper.js` → adapt for Drupal login
- Mobile viewport: 375×812 (iPhone 12); Desktop: 1280×800
- Percy alternative: `percy snapshot` — requires `PERCY_TOKEN` env var in CI

## Dependencies
- TASK-068 (Playwright E2E — reuse login helpers)
- TASK-069 (CI pipeline — add visual-regression stage)

## Definition of Done
- [ ] Code complete — backstop.json + engine scripts
- [ ] Baseline screenshots captured + committed
- [ ] CI job created + blocking PRs on >2% diff
- [ ] Approval workflow documented trong `tests/visual/README.md`
- [ ] G1-G4 gates passed
