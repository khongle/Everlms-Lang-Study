# Task: FIND-006 — Resolve `RatingController::toggleApprove` / `toggleFeatured` Dead Code

**ID:** TASK-057
**Unit:** lms_rating cleanup (no specific unit — code hygiene)
**Group:** G-HARDENING
**Priority:** P3 (Code Quality — Low severity, but easy win)
**Estimate:** 2h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
`RatingController` defines `toggleApprove()` and `toggleFeatured()` methods, but no routes are registered for them in `lms_rating.routing.yml`. The methods are dead code reachable only by direct PHP call (none exist). Decide between (a) registering the routes and exposing them via an admin moderation UI, or (b) removing the methods.

## Acceptance Criteria
- [ ] Decision documented (route or remove) — default recommendation: **register routes** since rating moderation is in scope per C02 user-roles (Manager+ has moderation rights)
- [ ] If routes registered: add to `lms_rating.routing.yml` with `_permission: 'administer lms ratings'` and a manage UI page showing the toggle buttons (or extend existing `manage/forum.html` style admin)
- [ ] If methods removed: delete from controller, run `vendor/bin/phpstan` to confirm no other code references them
- [ ] PHPUnit smoke test for the chosen path (route reachable by Manager / 403 for Student)

## Technical Notes
- Module: `lms_rating`
- File: `web/modules/custom/lms_rating/src/Controller/RatingController.php`
- Routing: `web/modules/custom/lms_rating/lms_rating.routing.yml`
- If choosing (a), use existing pattern in admin moderation pages (forum has similar approve/feature flow)

## Dependencies
- None

## Definition of Done
- [ ] Decision committed to main
- [ ] FIND-006 status in G02-risk-register.md → CLOSED with date and commit ref + decision rationale
