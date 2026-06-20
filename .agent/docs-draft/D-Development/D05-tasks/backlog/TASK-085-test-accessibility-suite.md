# Task: Automated Accessibility Suite — axe-core via Playwright (WCAG 2.1 AA)

**ID:** TASK-085
**Unit:** — (cross-cutting)
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Tích hợp axe-core accessibility scanner vào Playwright test suite (TASK-068) để tự động kiểm tra WCAG 2.1 AA compliance trên 20 trang quan trọng nhất của EverLMS. CI pipeline (TASK-069) block PR nếu có critical hoặc serious violation mới xuất hiện (so với baseline). Serious violation được allow nếu có documented exception với owner + timeline.

EverLMS phục vụ học viên đa dạng — a11y là requirement chức năng, không phải nice-to-have.

## Acceptance Criteria
- [ ] axe-core tích hợp vào Playwright: `@axe-core/playwright` package
- [ ] A11y test file: `tests/e2e/accessibility.spec.ts` — cover 20 pages:
  - Homepage, Course catalog, Course detail, Lesson view, Dashboard, My Courses
  - Quiz take, Quiz result, Checkout, Certificate verify
  - Blog listing, Blog detail, FAQ, About, Pricing
  - Login, Register, Profile, Admin course list, Search results
- [ ] Mỗi page: `await checkA11y(page, null, { runOnly: ['wcag2a', 'wcag2aa'] })`
- [ ] CI fail condition: bất kỳ violation mới có impact `critical` hoặc `serious`
- [ ] Violation report: `tests/e2e/results/a11y-report.html` (artifact trong CI)
- [ ] Existing violations tại baseline commit được suppressed via `axe-html-reporter` snapshot
- [ ] `lms_theme` dark mode: kiểm tra color contrast ratio ≥4.5:1 (automated)
- [ ] Focus management test: keyboard-only navigation qua quiz form không bị trapped
- [ ] ARIA landmarks: main, nav, header, footer present trên tất cả pages

## Technical Notes
- Package: `@axe-core/playwright` + `axe-html-reporter`
- Playwright config: `playwright.config.ts` — reuse existing (TASK-068)
- Test file: `tests/e2e/accessibility.spec.ts`
- Bootstrap 5.3 dark theme: `lms_theme/templates/` — check `aria-label` trên icon-only buttons
- Common violations to pre-check: missing `alt` trên course thumbnail images, form `<label>` association
- CI artifact: upload `a11y-report.html` kể cả khi pass
- Bypass annotation: `// axe-skip: reason + owner + JIRA-XXX` cho known exceptions
- DDEV run: `ddev exec npx playwright test tests/e2e/accessibility.spec.ts`

## Dependencies
- TASK-068 (Playwright E2E — base setup + page helpers)
- TASK-069 (CI pipeline — block on critical violations)

## Definition of Done
- [ ] Code complete — 20-page axe scan test file
- [ ] G1-G4 gates passed
- [ ] CI blocking on new critical/serious violations
- [ ] Baseline snapshot committed
- [ ] Color contrast + keyboard navigation verified
