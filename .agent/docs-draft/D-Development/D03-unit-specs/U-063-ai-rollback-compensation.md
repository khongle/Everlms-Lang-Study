# D03 — Unit Spec: AI Rollback + Compensation Logic

**Unit ID:** U-063
**Group:** G-HARDENING
**Title:** AI rollback + compensation logic
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Feature Flag:** FF_FEAT_AI_PROVIDER
**Risk Ref:** RISK-003 (Medium — AI generation thiếu rollback/compensation)

---

## Scope

Harden `CourseBuilderService` (U-048) with production-grade rollback: if entity creation fails mid-way, delete all partially-created entities. Add BuilderSession tracking to support async status queries and compensation.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/CourseBuilderService.php` | Modify | lms_ai |
| `src/Service/CourseBuilderStateMachine.php` | Modify | lms_ai |
| `src/Entity/BuilderSession.php` | Create | lms_ai |
| `src/Plugin/QueueWorker/CourseBuilderWorker.php` | Modify | lms_ai |

---

## Interfaces / Services

- `BuilderSession` entity: tracks state + created entity IDs for rollback
  - States: `generating` → `review` → `creating` → `done` / `failed` → `rolled_back`
- `CourseBuilderService::rollback(BuilderSession $session): void`
  - Iterates `session->created_entity_ids`, deletes each in reverse order
  - Handles partial deletes (entity may not exist if creation failed mid-way)
- On failure: state → `failed`, trigger rollback, update session, notify user

---

## Acceptance Criteria

- [ ] If chapter 3 entity creation fails, chapters 1-2 and course are deleted (clean state)
- [ ] BuilderSession entity records all created entity IDs during creation
- [ ] `rollback()` deletes all recorded entities even if some don't exist (handles partial state)
- [ ] After rollback, BuilderSession state → `rolled_back`
- [ ] User notified of failure + rollback completion via notification system
- [ ] Manual rollback: admin can trigger rollback for any `failed` session
- [ ] Feature flag `FF_FEAT_AI_PROVIDER` gates this code path

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Rollback correctness test (inject failure at step N) |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-047 | AI provider plugin |
| U-048 | Course builder service — modified |

---

*End of U-063-ai-rollback-compensation.md*
