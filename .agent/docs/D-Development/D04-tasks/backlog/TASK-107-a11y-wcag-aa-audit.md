# Task: WCAG 2.1 AA Audit — Top 20 Pages

**ID:** TASK-107
**Unit:** — (cross-cutting: a11y)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thực hiện manual WCAG 2.1 AA audit trên top 20 pages quan trọng nhất của EverLMS. Công cụ: axe DevTools (browser extension) + manual screen reader spot-check (NVDA hoặc VoiceOver). Output: danh sách prioritized issues với severity (P0/P1/P2/P3). Fix tất cả P0/P1 issues trong task này. P2/P3 tạo issues riêng.

Audit scope bao gồm cả tiếng Việt (primary language). Focus vào keyboard navigation, ARIA roles, form labels, và color contrast.

## Acceptance Criteria
- [ ] Danh sách 20 pages được audit (xem Technical Notes) — axe DevTools chạy trên từng page
- [ ] Manual keyboard navigation test: Tab qua tất cả interactive elements trên mỗi page không có traps
- [ ] axe DevTools: 0 "Critical" violations sau khi fix P0/P1 issues
- [ ] axe DevTools: "Serious" violations documented + fixed nếu P1, hoặc ticketed nếu P2
- [ ] Screen reader spot-check (NVDA + Chrome hoặc VoiceOver + Safari) trên 5 pages quan trọng nhất: homepage, course detail, lesson view, quiz taking, checkout
- [ ] Report file tại `.agent/docs/K-QA/K04-a11y-audit/AUDIT-001-wcag-aa-2026.md` với:
  - [ ] Danh sách tất cả violations + severity + page + element selector
  - [ ] Fix status cho từng issue (fixed / ticketed / accepted risk)
- [ ] P0/P1 issues fixed: code changes committed, tested trên staging
- [ ] P2/P3 issues: GitLab issues created với label `a11y`

## Technical Notes
- 20 pages cần audit:
  1. Homepage (`/`)
  2. Course catalog (`/courses`)
  3. Course detail (`/course/{slug}`)
  4. Lesson view (`/lesson/{id}`)
  5. Quiz taking (`/quiz/{id}`)
  6. Quiz result
  7. Checkout page
  8. Payment success
  9. Student dashboard
  10. My courses
  11. Certificate detail
  12. User profile
  13. Login page
  14. Register page
  15. Forgot password
  16. Search results
  17. Forum thread
  18. Notification center
  19. Learning path view
  20. Admin course edit
- axe DevTools: chạy ở context authenticated user + anonymous user
- Tham chiếu: WCAG 2.1 AA success criteria (wcag.com/); TASK-085 (a11y test automation); TASK-108 (screen reader pass)

## Dependencies
- TASK-085 (a11y test automation — nếu exist, reuse axe-core results)
- TASK-108 (screen reader pass — có thể chạy song song)

## Definition of Done
- [ ] Code complete (a11y fixes committed)
- [ ] G1-G4 gates passed
- [ ] Audit report committed tại `K04-a11y-audit/`
- [ ] 0 Critical axe violations trên tất cả 20 pages
- [ ] P0/P1 fixes tested trên staging bởi người khác (cross-check)
- [ ] GitLab issues created cho P2/P3
