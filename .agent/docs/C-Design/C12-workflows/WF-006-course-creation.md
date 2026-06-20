---
workflow: WF-006
slug: course-creation
mode: as-built
b04_source: B04/WF-006-course-creation.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-006 Course Creation

**Trigger:** Admin or Manager initiates course creation manually via `/workspace/courses/create`, or via the AI-assisted builder at `/manage/courses/ai-builder`.
**Outcome:** A published `lms_course` entity exists with at least one `lms_chapter` and one `lms_lesson`; lesson-type-specific sync (SCORM extraction, Zoom meeting creation) is complete; AI-generated content is queued or fully populated.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-006-course-creation.md`](../../B-Requirements/B04-business-workflows/WF-006-course-creation.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Administrator | All steps | `administer lms_course` |
| Manager | All steps (same as Admin for course creation) | `administer lms_course` |
| System | SCORM extraction, Zoom sync, AI queue processing | — |
| AI Provider | Steps 3–6 (AI path only) | Valid API key in `lms_ai.settings` (`«secret in config — see security risk»`) |

## Entry points

| URL (C07) | Page (C11) | Method | Form | Feature flag |
|---|---|---|---|---|
| `/workspace/courses/create` | course-create-wizard | GET | — (wizard template, no form) | — |
| `/workspace/courses/{id}/edit` | course-edit | GET/POST | `lms_course` `admin_edit` form mode | — |
| `/manage/courses/ai-builder` | ai-course-builder | GET/POST | `CourseBuilderForm` | `lms_ai` module enabled |
| `/manage/chapters/add` | chapter-add | GET/POST | `ChapterForm` | — |
| `/manage/chapters/{id}/edit` | chapter-edit | GET/POST | `ChapterForm` | — |
| `/manage/lessons/add` | lesson-add | GET/POST | `LessonForm` | — |
| `/manage/lessons/{id}/edit` | lesson-edit | GET/POST | `LessonForm` | — |
| `/workspace/quizzes/create` | quiz-create | GET/POST | `lms_quiz` form | — |

## Entities touched

| Entity (C04) | Operation | Fields written | State transition |
|---|---|---|---|
| [`lms_course`](../C04-entities/) | CREATE / UPDATE | `name`, `field_description`, `field_short_description`, `field_what_learn`, `field_prerequisites`, `field_who_for`, `field_thumbnail`, `field_banner`, `field_chapters`, `field_category`, `field_level`, `field_language`, `field_instructor`, `field_price`, `field_price_type`, `field_certificate_template`, `status` | — (no state machine; `status` boolean) |
| [`lms_chapter`](../C04-entities/) | CREATE / UPDATE | `name`, `field_description`, `field_lessons`, `weight` | — |
| [`lms_lesson`](../C04-entities/) | CREATE / UPDATE | `name`, `field_type`, `field_content`, `field_video_url`, `field_video_file`, `field_quiz`, `field_h5p_embed`, `field_scorm_package`, `field_pptx_attachment`, `field_zoom_meeting_id`, `field_zoom_schedule`, `field_free_preview`, `field_drip_days`, `status` | AI path: `status=0` (draft) → `status=1` (published) after queue worker |
| [`lms_quiz`](../C04-entities/) | CREATE (optional) | All quiz fields | — |

## State transitions

```
Manual path:
  [Author fills form] → lms_course status=TRUE (published, default)
  [Author fills form] → lms_lesson status=TRUE (published, default)

AI-assisted path:
  [CourseBuilderForm submit] → lms_course status=0 (draft)
  [CourseBuilderForm submit] → lms_lesson status=0 (draft, one per lesson slot)
  [Queue worker: CourseContentGenerator] → lms_lesson status=0 → status=1 (per lesson, after content generated)
  [Manual review] → lms_course status=0 → status=1 (publish decision by Admin/Manager)
```

State guards (who can trigger which transition):

| Transition | Trigger | Guard | Side effects |
|---|---|---|---|
| Create `lms_course` | Admin/Manager form submit | `administer lms_course` | None automatic |
| Publish `lms_course` (`status=1`) | Admin/Manager toggle | `administer lms_course` | Course appears in `/courses` catalog; cache tag `lms_course_list` should be invalidated |
| `lms_lesson status=0 → 1` (AI path) | `CourseContentGenerator` queue worker (cron) | System (no user permission needed for queue) | Lesson content populated via OpenAI `generateText()` |
| `lms_lesson` save with `field_type=scorm` | Any lesson save by Admin/Manager | `edit lms_course` | `_lms_lesson_extract_scorm()` extracts ZIP to `public://scorm_extracted/{lesson_id}/`; reads `imsmanifest.xml`; creates `index.html` redirect |
| `lms_lesson` save with `field_type=zoom` | Any lesson save by Admin/Manager | `edit lms_course` | `lms_zoom.module hook_entity_presave` → `ZoomMeetingManager::syncMeeting()` → Zoom Server-to-Server OAuth API call; creates/updates Zoom meeting; populates `field_zoom_meeting_id` |

## Modules involved (C05)

| Module | Responsibility in this workflow |
|---|---|
| [`lms_course`](../C05-modules/) | `AdminController` — serves `/workspace/courses/*` routes; `courseCreateWizard()`, `courseEditForm()`, `courseListForm()`; `WorkspaceContentController` for node/FAQ management |
| [`lms_chapter`](../C05-modules/) | `ChapterForm` — chapter CRUD; `ChapterAccessControlHandler` |
| [`lms_lesson`](../C05-modules/) | `LessonForm` (conditional `#states` per `field_type`); `_lms_lesson_extract_scorm()` on insert/update |
| [`lms_ai`](../C05-modules/) | `CourseBuilderForm`, `CourseBuilderService::generateCourseStructure()` (OpenAI/Gemini JSON generation); `CourseContentGenerator` queue worker; `AiEngineUserForm` (advanced bulk pipeline) |
| `lms_zoom` | `ZoomMeetingManager::syncMeeting()` via `hook_entity_presave` on `lms_lesson` |
| [`lms_quiz`](../C05-modules/) | Quiz creation at `/workspace/quizzes/create`; `AdminController::quizCreateForm()` |

## Permissions

| Step | Admin | Manager | Student | Anonymous |
|---|---|---|---|---|
| Access `/workspace/courses/create` | ✓ | ✓ | — | — |
| Create `lms_course` entity | ✓ | ✓ | — | — |
| Edit any `lms_course` | ✓ | ✓ | — | — |
| Create/edit `lms_chapter` | ✓ | ✓ | — | — |
| Create/edit `lms_lesson` | ✓ | ✓ | — | — |
| Access `/manage/courses/ai-builder` | ✓ | ✓ | — | — |
| Publish course (`status=TRUE`) | ✓ | ✓ | — | — |

Note: `Manager` role requires `administer lms_course`. The `edit own lms_course` permission exists but workspace routes use `administer lms_course` as the gate.

## Notifications

| Trigger | Recipient | Channel | Template |
|---|---|---|---|
| (none defined in code) | — | — | — |

No notification is dispatched on course creation or publication. Students discover new courses via catalog.

## Cache invalidation

| Step | Cache tag(s) | Search index | List view(s) |
|---|---|---|---|
| `lms_course` saved / published | `lms_course:{id}`, `lms_course_list` (implied) | — | `/courses` catalog, `/workspace/courses` admin list |
| `lms_lesson` saved with `field_type=scorm` | `lms_lesson:{id}` | — | — |
| `lms_lesson` queue worker publishes | `lms_lesson:{id}` | — | — |

No explicit cache tag invalidation code found in `AdminController` — relies on Drupal's default entity cache invalidation.

## Operations queues

| Page (C11) | Filters | Owner role |
|---|---|---|
| `/workspace/courses` (`AdminController::courseList()`) | status, category, instructor, search | Admin, Manager |
| `/workspace` dashboard | recent orders, KPI counts | Admin, Manager |

AI queue (`lms_ai_content_generator`): processed by Drupal cron → `CourseContentGenerator` worker. No UI to inspect queue depth; use `drush queue:list` and `drush queue:run lms_ai_content_generator`.

## Editorial / management surfaces

| Entity / content source | Create/Edit UI | Role / permission | Revision/moderation state | Translation | Visible C11 surfaces |
|---|---|---|---|---|---|
| `lms_course` | `/workspace/courses/{id}/edit` (admin_edit form mode) | `administer lms_course` | No moderation workflow; `status` boolean only | Translatable (langcode field) | `/courses`, `/courses/{id}`, `/workspace/courses` |
| `lms_chapter` | `/manage/chapters/add`, `/manage/chapters/{id}/edit`, `/workspace/chapters/{id}/admin-edit` | `administer lms_course` or `edit lms_course` | status boolean | Translatable | `/courses/{id}` curriculum panel |
| `lms_lesson` | `/manage/lessons/add`, `/manage/lessons/{id}/edit`, `/workspace/lessons/{id}/admin-edit` | `administer lms_course` or `edit lms_course` | status boolean; AI path uses `status=0` draft | Translatable | `/learn/{course}/{lesson}` |

## Failure modes

| Failure | Detection | Handling |
|---|---|---|
| OpenAI/Gemini API timeout or error (AI path, step 3) | `CourseBuilderService::generateCourseStructure()` catches exception; logs error; returns NULL; `generateAjax()` shows Drupal error message | Re-submit `CourseBuilderForm` with same or revised inputs |
| Partial entity creation before AI failure (AI path) | Orphaned `lms_course` + `lms_lesson` entities with `status=0` remain in DB (no rollback/compensation) | Admin manually deletes via `/workspace/courses` or `drush everlms:clean` |
| Queue worker fails on a lesson | Drupal re-queues item (standard queue retry); lesson stays `status=0` | Monitor via `drush queue:list`; run `drush queue:run lms_ai_content_generator` |
| Zoom API error during lesson save | `ZoomMeetingManager::syncMeeting()` logs error; falls back to 'me' user if instructor lookup fails; lesson still saved | Verify `lms_zoom.settings` credentials (`«secret in config — see security risk»`); retry lesson save |
| SCORM ZIP extract fails | `ZipArchive` error logged; lesson saved but SCORM content inaccessible at runtime | Re-upload valid SCORM ZIP; check `public://scorm_extracted/{id}/` |

## Implementation status

| Step | Status | Notes |
|---|---|---|
| Manual creation (wizard, course/chapter/lesson forms) | DONE (as-built) | `AdminController`, `ChapterForm`, `LessonForm` all real |
| SCORM extraction on lesson save | DONE (as-built) | `_lms_lesson_extract_scorm()` in `lms_lesson.module` |
| Zoom sync on lesson save | DONE (as-built) | `ZoomMeetingManager::syncMeeting()` via presave hook |
| AI builder (`CourseBuilderForm` + `CourseBuilderService`) | DONE (as-built) | Real OpenAI/Gemini call; queue-based lesson content generation |
| `CourseContentGenerator` queue worker | DONE (as-built) | Processes `lms_ai_content_generator` queue items |
| Advanced AI Engine (`AiEngineUserForm`) | DONE (as-built) | Multi-step bulk pipeline; no rollback on partial failure |
| Rollback/compensation on AI failure | NOT IMPLEMENTED (target-state) | No saga pattern; orphaned drafts on failure |
| Course duration auto-calculation | NOT IMPLEMENTED (manual only) | `field_duration` on `lms_course` is a plain integer field; no hook in `lms_course.module` or `lms_lesson.module` auto-sums lesson durations. Value is set manually by admins or by the AI engine pipeline (`AiEngineUserForm` sets `field_duration` directly). `lms_lesson` also has its own `field_duration` integer, read independently by `ZoomMeetingManager`, `MobileCourseController`, and `WishlistController` — none of them update the parent course total. |

## Acceptance (B05)

- B05 acceptance criteria not yet materialized.

## Related

- Modules: [`lms_course`](../C05-modules/), [`lms_chapter`](../C05-modules/), [`lms_lesson`](../C05-modules/), [`lms_ai`](../C05-modules/)
- Entities: [`C04-entities/`](../C04-entities/)
- Roles: [`C02-user-roles.md`](../C02-user-roles.md)
- B04 source: [`../../B-Requirements/B04-business-workflows/WF-006-course-creation.md`](../../B-Requirements/B04-business-workflows/WF-006-course-creation.md)
