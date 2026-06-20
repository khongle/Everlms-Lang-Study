# Task: Student Portal Pages (Orders, Membership, Quiz Results)

**ID:** TASK-048
**Unit:** — (cross-unit: U-025, U-026, U-017)
**Group:** G-UI-POLISH (extension)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
UI pages cho student portal có trong sitemap nhưng chưa có UI task: order history, order detail, membership page, quiz results listing.

## Context
Sitemap C07 liệt kê:
- `/orders` — Order history
- `/orders/{id}` — Order detail
- `/membership` — My membership
- `/my-quiz-results` — All quiz results
- `/quiz/{id}/result/{result_id}` — Quiz result detail

Entities exist nhưng student-facing UI chưa có task riêng.

## Acceptance Criteria
- [ ] Order history page: list, status badges, date, total
- [ ] Order detail page: items, payment info, status, invoice download
- [ ] My membership page: current plan, expiry, upgrade/cancel options
- [ ] Quiz results listing: all attempts, scores, date, course/quiz name
- [ ] Quiz result detail: per-question breakdown, correct answers
- [ ] Responsive layout

## Technical Notes
- Modules: `lms_order` (controller exists), `lms_membership` (controller exists), `lms_quiz` (result views exist)
- Templates: extend existing or create new student-facing templates
- SCSS: `_orders.scss`, `_membership.scss`

## Dependencies
- TASK-001 (Checkout workflow)
- TASK-004 (Membership lifecycle)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] All sitemap student routes functional
- [ ] Mobile responsive verified
