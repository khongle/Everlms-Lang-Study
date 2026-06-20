# ISSUE-012 — lms_badge: Entities Exist but Zero Award Mechanism; field_criteria Never Evaluated

| Field | Value |
|---|---|
| ID | `ISSUE-012` |
| Title | lms_badge entities exist but zero award mechanism; field_criteria JSON format documented but never evaluated |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | N/A (no routes exist); affects `/manage/badges` admin CRUD only |
| Owning Epic | E-06 (Gamification) — |
| Owning Story | [`lms_badge`](../../../D-Development/D03-stories/lms_badge.md) |
| Owning module(s) | `lms_badge` |

## Summary

`lms_badge` is implemented as a **minimal scaffold**: the module delivers only two entity definitions — `lms_badge` (badge definition with `field_criteria` storing a JSON rule) and `lms_user_badge` (issued badge junction entity). Beyond entity CRUD (via auto-generated admin routes at `/manage/badges`), **nothing in the codebase evaluates, awards, or queries badges**:

- No `lms_badge.services.yml` — no services registered.
- No `lms_badge.routing.yml` — no learner-facing or API routes.
- No `lms_badge.module` — no `hook_cron()`, `hook_entity_insert()`, or any hook that could trigger badge evaluation.
- No `lms_badge.install` — no schema or setup hooks.
- No controllers, forms (beyond core default), services, or event subscribers.
- `field_criteria` has a documented JSON format in code comments (`{"type":"courses_completed","count":5}`) but there is no parser, no evaluator, and no trigger for this field.

The `lms_user_badge` table exists in the database with zero rows (`AUTO_INCREMENT` not set, no data). No badge has ever been awarded on the live system despite ~175 user-course enrollments being present.

## Reproduction

1. Check module directory: `ls web/modules/custom/lms_badge/src/` — only `Entity/` directory with two entity class files.
2. Check for any code that loads or evaluates `field_criteria`:
   ```bash
   grep -rn "lms_badge\|lms_user_badge\|field_criteria" web/modules/custom/ | grep -v "lms_badge/src/Entity"
   ```
   Returns no results (no consumer of badge entities outside their own entity class).
3. Check `lms_user_badge` table row count in the database: zero rows despite live learner activity.
4. Navigate to `/manage/badges/add` — can create a badge with `field_criteria` JSON but it has no effect.

## Root Cause

The `lms_badge` module was implemented at the data-model layer only, with the award logic deferred as a future feature. The `field_criteria` JSON format was documented as a design intent but the business logic layer — event subscriber listening to `QuizPassedEvent` or course completion, a cron job evaluating thresholds, or a service method called from `ProgressApiController` — was never built. The module is classified as **near-stub** in the facts base.

This is a **feature-completeness gap**, not a crash bug, but it means the Gamification/Badges feature listed in [B03-features](../../../B-Requirements/B03-features/06-certification-badges.md) is entirely absent at runtime.

Source files (scope of missing work):
- `web/modules/custom/lms_badge/` — no services, no hooks, no controllers beyond entity scaffolding
- `web/modules/custom/lms_badge/src/Entity/Badge.php` — `field_criteria` documented but not consumed
- `web/modules/custom/lms_badge/src/Entity/UserBadge.php` — junction entity, never created programmatically

## Resolution (filled at RESOLVED)

Minimum viable implementation requires:

1. **Award service** — create `BadgeAwardService` that:
   - Loads all active `lms_badge` entities.
   - Parses `field_criteria` JSON per badge.
   - Evaluates user eligibility (e.g., for `courses_completed` type: count `lms_user_course` with `field_status=completed` for uid).
   - Creates `lms_user_badge` entity if not already awarded.
2. **Trigger** — register as event subscriber on `QuizPassedEvent` (for quiz-completion badges) and/or `lms_progress.course_completed` event (if/when defined), or add to `ProgressApiController::markComplete()` callback.
3. **Cron fallback** — `hook_cron()` in `lms_badge.module` to evaluate pending criteria for recently-active users.
4. **Routes** — add learner-facing badge display at `/my-badges` and admin-facing award management.

Until implemented, badge functionality should be documented as **planned / not yet active** in user-facing documentation and the admin panel.

## Trace links

- **Story:** `D-Development/D03-stories/lms_badge.md`
- **C05 module spec:** `C-Design/C05-modules/lms_badge.md`
- **Facts base:** `modules-cert-integration.md` §lms_badge — "CRITICAL NOTE: field_criteria JSON format is documented but no service/cron/event subscriber evaluates criteria or awards badges"
- **DB schema:** `db-schema.md` — `lms_badge` (NO data), `lms_user_badge` (NO data)
- **Feature requirement:** `B-Requirements/B03-features/06-certification-badges.md`
- **K04:** Reusable rule — when introducing a JSON-configured field that drives business logic, the evaluator must ship in the same PR/session; skeleton fields with no consumers create false confidence in feature coverage.
