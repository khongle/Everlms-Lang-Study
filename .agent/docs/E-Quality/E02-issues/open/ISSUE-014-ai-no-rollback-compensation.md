# ISSUE-014 — lms_ai: Partial Course-Builder Failure Leaves Orphan Entities; No Rollback / Compensation

| Field | Value |
|---|---|
| ID | `ISSUE-014` |
| Title | Partial AI course-builder failure leaves orphan entities — no rollback or compensation mechanism |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `/manage/courses/ai-builder` (`lms_ai.course_builder_form`); `/admin/everlms-ai-engine` (`lms_ai.engine_users_form`) |
| Owning Epic | E-09 (AI Features) — |
| Owning Story | [`ai-rollback-compensation`](../../../D-Development/D03-stories/ai-rollback-compensation.md) |
| Owning module(s) | `lms_ai` |

## Summary

The `lms_ai` module provides two AI course-building pipelines — `CourseBuilderService::generateCourseStructure()` (called from `CourseBuilderForm`) and the multi-step `AiEngineUserForm` AJAX wizard — neither of which implements any rollback or compensating-transaction pattern on partial failure.

**`CourseBuilderService::generateCourseStructure()` failure modes:**
1. OpenAI `generateJson()` succeeds → `lms_course` entity created (status=0, draft).
2. Lesson creation loop begins: entity_type.manager creates `lms_lesson` entities one at a time.
3. If a failure occurs (API timeout, entity save error, memory limit) mid-loop:
   - The partially-created `lms_course` and N successfully-created `lms_lesson` entities remain in the database.
   - The queue items for content generation may or may not have been queued for the created lessons.
   - The exception is caught at the top level; the method logs the error and returns `NULL`.
4. **Result:** orphan unpublished `lms_course` + some orphan `lms_lesson` entities with no chapter or course association, and some `lms_ai_content_generator` queue items pointing to lessons that may have no parent context.

**`AiEngineUserForm` AJAX handler failure modes:**
- Each AJAX step (e.g., `ajaxGenerateCurriculum()`, `ajaxGenerateChaptersDetail()`, `ajaxGenerateSlideContent()`) creates entities incrementally and saves partial results to JSON files in `lms_ai/JSON/`.
- On partial failure within a step, successfully-created entities are not rolled back; the method logs errors and counts `$errors` but does not undo DB writes.
- Successive retries of the same step can create duplicate entities (no idempotency guard).

The `lms_ai/JSON/` data files (`ai_curriculum.json`, `ai_users.json`) are committed to the module directory alongside production PHP source files, which is also an inappropriate location for runtime-generated data.

## Reproduction

**CourseBuilderService path:**
1. Navigate to `/manage/courses/ai-builder`.
2. Submit with a topic; observe OpenAI call.
3. Simulate failure by temporarily reducing `max_execution_time` or introducing a throw in the lesson-creation loop.
4. After the error, check `lms_course` entity list (`/manage/courses`) — a draft course with no lessons or chapters exists.
5. Check `lms_lesson` entity list — partially-created lessons with stub content may exist with no parent chapter reference (depending on where in the loop the failure occurred).

**AiEngineUserForm path:**
1. Navigate to `/admin/everlms-ai-engine`.
2. Run "Generate Curriculum" step — entities created.
3. Interrupt the next AJAX step (e.g., by cancelling the request mid-stream).
4. Re-run the same step — duplicate entities may be created (no idempotency check).

## Root Cause

The AI pipeline was implemented as a linear sequence of entity-creation steps without a saga/transaction pattern. PHP does not have native distributed transaction support, and Drupal's database transaction (`\Drupal::database()->startTransaction()`) does not span multiple entity saves across different storage backends. The design accepts partial success as a known risk — evidenced by the explicit code comment in `CourseBuilderService`: "No rollback: if partial creation fails, already-created entities are NOT deleted."

The `AiEngineUserForm` AJAX handlers are intentionally step-by-step and manual to allow human review between steps, but this means no automated cleanup on failure.

Source files:
- `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php` — `generateCourseStructure()` — no rollback
- `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php` — all `ajax*()` handlers — no rollback per step
- `web/modules/custom/lms_ai/JSON/` — runtime-generated data files committed in module directory

## Resolution (filled at RESOLVED)

**Short-term (reduce blast radius):**
1. In `CourseBuilderService::generateCourseStructure()`, wrap the entire entity-creation sequence in a try/catch; on exception, delete already-created course and lesson entities before re-throwing or returning NULL.
2. Add a `$created_entities` tracking array at the start of the method; push each entity to it on successful save; in the catch block, iterate and call `$entity->delete()` on each.

**Medium-term (idempotency):**
1. Add idempotency guards in `AiEngineUserForm` AJAX handlers: before creating an entity, check if one with the same title/course reference already exists; skip creation if found.
2. Move `lms_ai/JSON/` data files to `private://lms_ai/` or `public://lms_ai/` (managed file system), not committed to the module directory.

**Long-term (saga pattern):**
1. Implement a formal saga with a state machine tracking pipeline progress; each step records its created entity IDs in State API; a compensating transaction step deletes all created entities on saga failure.

## Trace links

- **Story:** `D-Development/D03-stories/ai-rollback-compensation.md`; `D-Development/D03-stories/lms_ai.md`
- **C05 module spec:** `C-Design/C05-modules/lms_ai.md`
- **Facts base:** `modules-cert-integration.md` §lms_ai §"Rollback / Compensation pattern" — "NONE. The AI pipeline has no saga or compensation mechanism."
- **K04:** Reusable rule — multi-entity creation pipelines must track created IDs and implement compensating deletes in the exception path; design review gates should require explicit rollback strategy documentation for any pipeline that creates more than one entity per invocation.
