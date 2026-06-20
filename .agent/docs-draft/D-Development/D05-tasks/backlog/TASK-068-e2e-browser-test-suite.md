# Task: E2E Browser Test Suite Setup (Playwright)

**ID:** TASK-068
**Unit:** — (test infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập Playwright E2E test suite trong project và tích hợp vào GitLab CI (gitlab.weebpal.com). Cover 4 golden flows từ business workflows: WF-001 (enrollment → learning), WF-002 (quiz submission → grading), WF-003 (cert issuance), WF-004 (order management). Tests chạy headless Chromium + Firefox trên mọi PR.

## Acceptance Criteria
- [ ] Playwright installed: `package.json` có `@playwright/test`, `playwright.config.ts` cấu hình
- [ ] Test directory: `tests/e2e/` với subdirs `flows/`, `pages/` (Page Object Model)
- [ ] WF-001: `tests/e2e/flows/enrollment-learning.spec.ts` — anonymous browse → register → enroll (free course) → complete lesson → check progress
- [ ] WF-002: `tests/e2e/flows/quiz-grading.spec.ts` — student take quiz → submit → auto-grade result visible → (essay) appears in grading queue
- [ ] WF-003: `tests/e2e/flows/cert-issuance.spec.ts` — complete course (mock progress) → certificate generated → verify at `/certificate/verify/{code}`
- [ ] WF-004: `tests/e2e/flows/order-management.spec.ts` — add to cart → checkout (Stripe test mode) → order appears in `/orders`
- [ ] GitLab CI stage `e2e` runs Playwright headless Chromium + Firefox
- [ ] Test fixtures: seed test data (users, courses) via Drush commands trong CI script
- [ ] Screenshot on failure: lưu vào GitLab CI artifacts
- [ ] Flaky test threshold: <5% flakiness per spec over 10 runs (enforce via retry config)

## Technical Notes
- `playwright.config.ts` tại project root, baseURL = `http://web` (DDEV service name trong CI)
- GitLab CI job: stage `e2e`, image `mcr.microsoft.com/playwright:v1.x-focal`
- Seed script: `.gitlab-ci/scripts/seed-test-data.sh` dùng `drush php-script`
- Page Objects: `tests/e2e/pages/LoginPage.ts`, `CoursePage.ts`, `CheckoutPage.ts`, etc.
- WF-004 dùng Stripe test card `4242 4242 4242 4242` (test mode only)
- Tham khảo WF docs: `B-Requirements/B04-business-workflows/WF-001.md` .. `WF-004.md`
- `.gitignore`: thêm `test-results/`, `playwright-report/`

## Dependencies
- TASK-069 (CI pipeline) — E2E stage thuộc CI pipeline; có thể phát triển song song
- TASK-001 (Checkout flow), TASK-006 (cert issuance), TASK-009 (notification) — WF-003/004 cần impl done

## Definition of Done
- [ ] Code complete
- [ ] 4 flow specs pass locally (`npx playwright test`)
- [ ] GitLab CI `e2e` stage green trên sample PR
- [ ] Artifacts (screenshots, HTML report) upload đến GitLab CI khi fail
- [ ] `tests/e2e/README.md` mô tả cách chạy local + seed data
