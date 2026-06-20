---
usecase_id: UC-WF006-002
workflow: WF-006
title: AI course builder partially creates entities then fails — no rollback
type: error
status: as-built (no rollback = known gap)
---

# UC-WF006-002 — AI Course Builder Partial Failure (Error Path: No Rollback)

Covers the failure scenario in `CourseBuilderService::generateCourseStructure()` where the AI API call succeeds and some `lms_course` / `lms_lesson` entities are created, but an exception occurs before all queue items are dispatched. The system has no compensation mechanism — orphaned draft entities remain in the database.

---

## Actors

| Actor | Role |
|-------|------|
| Admin / Manager | Initiates AI builder form at `/manage/courses/ai-builder` |
| System | Calls AI provider, creates entities, dispatches queue items |
| OpenAI / Gemini (external) | AI provider — generates course JSON outline |
| Drupal Queue | Receives `lms_ai_content_generator` items for background content fill |

---

## Preconditions

1. Actor has `administer lms_course` permission.
2. `lms_ai` module is enabled; at least one AI provider (openai or gemini) is configured with an API key in `lms_ai.settings` (`«secret in config — see security risk»`).
3. `CourseBuilderService` (`lms_ai.course_builder` service) is available.
4. A runtime error condition exists during processing (e.g., database write fails after the first few lesson entities are created, or an entity validation error occurs mid-loop).

---

## Trigger

Admin submits the `CourseBuilderForm` at `/manage/courses/ai-builder` with topic, audience, and tone.

---

## Main Flow (to the point of failure)

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** admin submits the form | `CourseBuilderForm::generateAjax()` calls `CourseBuilderService::generateCourseStructure(topic, audience, tone)` |
| 2 | **When** `generateCourseStructure()` calls the AI provider | `OpenAiProvider::generateJson()` (or `GeminiProvider::generateJson()`) sends a request with 60 s timeout; receives course outline JSON: `{title, description, sections[{title, lessons[{title, type, prompt_for_content}]}]}` |
| 3 | **When** the service creates the course entity | Creates `lms_course` (status=0, draft, uid=current_user, name=AI-generated title); entity is saved to DB |
| 4 | **When** the service iterates sections and lessons | For each lesson: creates `lms_lesson` (status=0, name=`section_title: lesson_title`, field_content=placeholder); saves to DB; dispatches `lms_ai_content_generator` queue item |
| 5 | **When** an exception occurs mid-loop (e.g., lesson entity save fails at lesson 3 of 8) | PHP exception is thrown; propagates up the call stack |
| 6 | **Then** `generateCourseStructure()` catches the exception at its top-level try/catch | Calls `\Drupal::logger('lms_ai')->error(...)` to log the error; returns NULL |
| 7 | **Then** `generateAjax()` receives NULL | Adds a Drupal error message to the AJAX response; the form does NOT redirect |
| 8 | **Then** the DB contains orphaned entities | `lms_course` entity (draft) persists; lessons 1–2 (created before failure) persist; their queue items may or may not have been dispatched; lessons 3–8 are not created; no chapters are created (structure note: `CourseBuilderService` queues lessons directly on the course, does not create `lms_chapter` entities) |

---

## Entities Mutated (partial state after failure)

| Entity | State After Failure | Persistence |
|--------|-------------------|-------------|
| `lms_course` | Created, `status=0` (draft), name populated from AI output | Remains in DB indefinitely |
| `lms_lesson` (0 to N-1 of N) | Created, `status=0`, placeholder `field_content` | Remains in DB indefinitely |
| `lms_ai_content_generator` queue items | May exist for lessons created before failure | Processed by next cron run — those lessons will get AI content and be published (`status=1`) even though the overall course creation failed |
| `lms_lesson` (N to end) | NOT created | Not present |

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| Drupal error log | `CourseBuilderService::generateCourseStructure()` catch block | `\Drupal::logger('lms_ai')->error()` — visible at `/admin/reports/dblog` |
| AJAX error message | `CourseBuilderForm::generateAjax()` | Drupal messenger error message rendered in the AJAX response |
| Orphaned draft course | `/workspace/courses` admin list | Appears as draft course; no chapters linked; some lessons may exist in `/manage/lessons` |
| Queue items | `drush queue:list` | Items for created lessons remain; `drush queue:run lms_ai_content_generator` will process them |

---

## Recovery Steps (manual)

| Step | Action |
|------|--------|
| 1 | Admin navigates to `/workspace/courses` and identifies the orphaned draft course |
| 2 | Admin manually deletes the orphaned course entity |
| 3 | Admin navigates to `/manage/lessons` and deletes any orphaned draft lessons |
| 4 | Admin checks `drush queue:list` for pending `lms_ai_content_generator` items; orphaned queue items can be ignored (lesson load will fail gracefully if lesson was deleted) or the queue can be drained |
| 5 | Admin retries the AI builder form with the same or revised input |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-075](../../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md#ac-075-ai-course-generation--partial-failure-behavior-as-built--known-gap) — Partial failure leaves orphaned drafts; no rollback (as-built / known gap) | E03-TEST-WF006-002-A |
| [AC-076](../../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md#ac-076-ai-rollback-on-partial-failure-target-stateplanned) — Rollback/compensation on partial failure (target-state/planned) | E03-TEST-WF006-002-B |

---

## Notes

- **No chapter entities created by `CourseBuilderService`**: The service creates `lms_course` and `lms_lesson` entities, but does NOT create `lms_chapter` entities or link lessons via the standard `Course → Chapter → Lesson` hierarchy. Lessons are queued with their content; the chapter structure must be added manually if needed.
- **Queue worker retries**: `CourseContentGenerator` re-throws exceptions, causing Drupal's queue to retain the item for retry. If the lesson entity was deleted (recovery step 3), the queue worker's `loadEntity()` will fail silently on the next retry.
- **`AiEngineUserForm` pipeline** (at `/admin/everlms-ai-engine`) has the same no-rollback behaviour for all its AJAX steps. It additionally saves intermediate JSON to `JSON/ai_curriculum.json` and `JSON/ai_users.json` inside the module directory — these files persist across sessions and act as a checkpoint, but they are not a formal saga log and do not enable automatic retry or compensation.
- This gap is tracked as a risk in the project risk register (`G02-risk-register.md` — not yet materialized in this documentation rebuild).
