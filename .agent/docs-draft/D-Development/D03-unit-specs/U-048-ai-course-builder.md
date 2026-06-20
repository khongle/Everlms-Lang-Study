# D03 — Unit Spec: AI Course Builder

**Unit ID:** U-048
**Group:** G-INTEGRATION
**Title:** AI course builder
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned
**Feature Flag:** FF_FEAT_AI_BUILDER

---

## Scope

UI + workflow for generating a course structure using AI. Instructor inputs topic/description, AI generates chapter/lesson outline, instructor reviews/edits, confirms, and entities are created in DB. Includes progress feedback and rollback on failure.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/AiCourseBuilderController.php` | Create | lms_ai |
| `src/Form/AiCourseBuilderForm.php` | Create | lms_ai |
| `src/Service/CourseBuilderService.php` | Modify | lms_ai |
| `src/Service/CourseBuilderStateMachine.php` | Create | lms_ai |
| `src/Plugin/QueueWorker/CourseBuilderWorker.php` | Create | lms_ai |
| `templates/ai-builder-form.html.twig` | Create | lms_ai |
| `templates/ai-builder-preview.html.twig` | Create | lms_ai |

---

## Interfaces / Services

- `CourseBuilderService::generate(string $topic, array $options): BuilderSession`
- `CourseBuilderStateMachine`: states: `idle` → `generating` → `review` → `creating` → `done` / `failed` → `rolled_back`
- `CourseBuilderWorker`: async queue processing for entity creation
- Uses `AiProviderInterface::generateCourseStructure()` (U-047)

---

## Acceptance Criteria

- [ ] Instructor submits topic + level (beginner/intermediate/advanced) + lesson count
- [ ] AI generates structured outline (chapters + lesson titles + descriptions)
- [ ] Instructor can edit outline before confirming
- [ ] On confirmation: Course + Chapter + Lesson entities created atomically (transaction)
- [ ] On AI API failure: no partial entities created; error message shown
- [ ] On entity creation failure: all created entities rolled back (delete)
- [ ] Feature flag `FF_FEAT_AI_BUILDER` gates the builder UI
- [ ] Builder only accessible to users with `instructor` or `administrator` role

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Rollback test on entity creation failure |
| G4 Adversarial | PENDING | Prompt injection test |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-047 | AI provider plugin — must be complete first |
| U-005 | Course entity created by builder |
| U-006 | Chapter entity created by builder |
| U-007 | Lesson entity created by builder |

---

**Feature Flag:** FF_FEAT_AI_BUILDER (depends on FF_FEAT_AI_PROVIDER being enabled)
**Risk Ref:** RISK-003

*End of U-048-ai-course-builder.md*
