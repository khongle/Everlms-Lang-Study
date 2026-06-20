# Task: Points / XP System

**ID:** TASK-143
**Unit:** — (new module `lms_xp`)
**Group:** G-GAMIFICATION
**Priority:** P3
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement a points/XP system where users earn experience points for learning actions: completing a lesson, passing a quiz, finishing a course, posting in the forum, and more. Configurable XP amounts per action. Define level-up thresholds. Display total XP, current level, and a progress bar to next level on the user profile and dashboard.

## Context
- XP is a foundation layer consumed by TASK-141 (leaderboard), TASK-144 (achievements), and TASK-063 (badge rules).
- Keep the module minimal and event-driven so other modules can award XP without coupling directly to `lms_xp` internals.
- Level thresholds use a simple configurable table (not a formula) for admin flexibility.

## Acceptance Criteria
- [ ] XP awarded on configurable events: lesson complete, quiz pass (first attempt bonus), course complete, forum post, forum reply, Q&A answer, lesson comment, certificate earned
- [ ] XP amounts configurable per event type in admin UI at `/admin/lms/xp/settings`
- [ ] XP ledger table records each transaction (uid, event_type, xp_delta, reference_id, created)
- [ ] User total XP = sum of ledger; current level derived from threshold table
- [ ] Level threshold table configurable in admin (Level 1: 0 XP, Level 2: 100 XP, etc.); up to 50 levels
- [ ] Dashboard card shows: XP total, current level name, progress bar to next level
- [ ] Profile page `/user/{uid}` shows same XP widget
- [ ] Idempotency: awarding XP for the same event+reference_id twice is a no-op (unique constraint)
- [ ] XP gain triggers a brief toast "You earned +25 XP!" via in-app JS notification
- [ ] Admin can manually adjust XP for any user (add/subtract) with reason note

## Technical Notes
- New module: `web/modules/custom/lms_xp/`
- DB tables: `lms_xp_ledger` (id, uid, event_type, xp_delta, reference_id, reason, created), `lms_xp_levels` (level, label, min_xp)
- Service: `XpService::award(uid, event_type, reference_id, xp_override = NULL)` — checks idempotency, inserts ledger row, dispatches `lms_xp.awarded` event
- `XpService::getTotalXp(uid)`, `getLevel(uid)`, `getLevelProgress(uid)`
- Event subscribers in other modules call `XpService::award()` via the service container (no direct DB coupling)
- Config: `lms_xp.settings` — event_type → xp_amount map; `lms_xp_levels` seeded in `hook_install`
- Admin form: `XpSettingsForm.php`, `XpLevelAdminForm.php`
- Admin manual adjust: `XpManualAdjustForm.php` at `/admin/lms/xp/users/{uid}/adjust`
- JS: toast notification via Drupal.announce or custom `lms_xp/toast` library
- SCSS: `_xp-widget.scss`

## Dependencies
- TASK-063 (Admin badges CRUD — may award XP milestone badges; listen to `lms_xp.awarded`)
- TASK-141 (Leaderboard — reads XP totals)
- TASK-144 (Achievements — listens to `lms_xp.awarded`)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `XpServiceTest` (award, idempotency, total calc, level derivation)
- [ ] Admin settings and level table CRUD verified
- [ ] Toast fires on XP award (JS smoke test noted in PR)
- [ ] No new debt introduced
