---
usecase_id: UC-WF006-001
workflow: WF-006
title: Admin manually creates a course with chapter, lesson, and optional quiz
type: happy
status: as-built
---

# UC-WF006-001 — Manual Course Creation (Happy Path)

Covers an Admin or Manager creating a complete course structure manually via the workspace admin UI: course entity → chapter entity → lesson entity (with SCORM or Zoom side-effects where applicable) → optional quiz link → optional certificate template reference → publish.

---

## Actors

| Actor | Role |
|-------|------|
| Admin / Manager | Authenticated user with `administer lms_course` permission |
| System | Entity creation, SCORM extraction (if applicable), Zoom meeting sync (if applicable) |
| Zoom API (external) | Called by `ZoomMeetingManager::syncMeeting()` for zoom-type lessons |

---

## Preconditions

1. Actor has `administer lms_course` permission (role: `administrator` or `manager`).
2. At least one `course_category` taxonomy term exists (seeded by `lms_base.install` or manually created).
3. For zoom-type lessons: `lms_zoom.settings` has valid `account_id`, `client_id`, `client_secret` (`«secret in config — see security risk»`).
4. For SCORM lessons: a valid SCORM 1.2 or 2004 ZIP file with `imsmanifest.xml` is available.
5. For quiz attachment: a `lms_quiz` entity already exists or will be created separately at `/workspace/quizzes/create`.

---

## Trigger

Admin navigates to `/workspace/courses/create` or clicks "Add Course" from `/workspace/courses`.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** admin visits `/workspace/courses/create` | `AdminController::courseCreateWizard()` renders `lms_admin_course_create` template (UI wizard shell) |
| 2 | **When** admin proceeds to course form at `/workspace/courses/{id}/edit` (or directly `/manage/courses/add`) | `AdminController::courseEditForm()` embeds the `lms_course` entity form in `admin_edit` form mode (or `manager_edit` for manager role) |
| 3 | **When** admin fills in required fields (name, category, level, price_type, thumbnail) and saves | `CourseForm::save()` saves `lms_course` entity: `status=TRUE` (published by default), `uid=current_user`, `field_category`, `field_level`, `field_price_type` (free/paid/membership), `field_price` (if paid) |
| 4 | **When** admin navigates to `/manage/chapters/add` | `ChapterForm` (standard `ContentEntityForm`) renders: `name`, `field_description`, `weight` fields |
| 5 | **When** admin fills in chapter name and saves | Creates `lms_chapter` entity; `ChapterForm::save()` adds status message and redirects to chapter collection |
| 6 | **When** admin links the chapter to the course | Edits the `lms_course` entity; sets `field_chapters` to include the new `lms_chapter` entity reference; saves course |
| 7 | **When** admin navigates to `/manage/lessons/add` | `LessonForm` renders with `field_type` select (video/text/quiz/h5p/scorm/pdf/pptx/zoom/mixed); conditional fields shown via Drupal Form API `#states` |
| 8 | **Given** admin selects `field_type = scorm` and uploads a ZIP file | `LessonForm` shows `field_scorm_package` file widget; admin uploads ZIP |
| 9 | **When** lesson is saved | `hook_entity_insert()` in `lms_lesson.module` fires `_lms_lesson_extract_scorm()`: extracts ZIP to `public://scorm_extracted/{lesson_id}/`; reads `imsmanifest.xml`; auto-detects launch file; creates `index.html` redirect if no launch file identified |
| 9z | **Alternatively, given** admin selects `field_type = zoom` | `LessonForm` shows `field_zoom_meeting_id` + `field_zoom_schedule`; admin sets schedule timestamp |
| 9z2 | **When** zoom lesson is saved | `hook_entity_presave()` in `lms_zoom.module` fires `ZoomMeetingManager::syncMeeting()`: traverses lesson → chapter → course → `field_instructor` to get instructor email; calls Zoom Server-to-Server OAuth; calls `ZoomService::createMeeting()` (POST `/users/{userId}/meetings`); stores returned `meeting_id` in `field_zoom_meeting_id` |
| 10 | **When** admin links lesson to chapter | Edits `lms_chapter` entity; sets `field_lessons` to include new lesson reference; saves chapter |
| 11 | **When** (optional) admin attaches a quiz | Creates/edits `lms_quiz` at `/workspace/quizzes/create`; edits lesson entity; sets `field_quiz` to reference quiz; saves — lesson `field_type` should include `quiz` or `mixed` for the quiz to be surfaced |
| 12 | **When** (optional) admin sets a certificate template | Edits `lms_course` entity; sets `field_certificate_template` to a published `lms_certificate_template`; saves course |
| 13 | **Then** the course is live | Course appears in `/courses` catalog; students can enroll; quiz completion will auto-issue certificate if template is set |

---

## Entities Mutated

| Entity | Fields Set | Step |
|--------|-----------|------|
| `lms_course` | `uid`, `name`, `field_description`, `field_short_description`, `field_category`, `field_level`, `field_language`, `field_price_type`, `field_price`, `field_thumbnail`, `field_banner`, `field_chapters`, `field_certificate_template`, `status=TRUE` | 3, 6, 12 |
| `lms_chapter` | `name`, `field_description`, `field_lessons`, `weight`, `status=TRUE` | 5, 10 |
| `lms_lesson` | `name`, `field_type`, `field_content`, `field_video_url`/`field_video_file`/`field_scorm_package`/`field_zoom_meeting_id`/`field_quiz`/etc., `field_duration`, `weight`, `status=TRUE` | 9 |
| `lms_quiz` (optional) | Created separately via quiz wizard; referenced in `lms_lesson.field_quiz` | 11 |

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| Drupal status message | `CourseForm::save()`, `ChapterForm::save()`, `LessonForm::save()` | Success messages added to Drupal messenger |
| SCORM extraction log | `_lms_lesson_extract_scorm()` via `lms_lesson.module` | Logged if extraction fails or `imsmanifest.xml` not found |
| Zoom API call | `ZoomMeetingManager::syncMeeting()` | `ZoomService::createMeeting()` → POST to Zoom REST API; Zoom access token cached in Drupal State |
| Zoom error fallback | `ZoomMeetingManager::syncMeeting()` | Falls back to user `'me'` if instructor-specific meeting creation fails; error logged |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-001](../../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) — Course entity created with required fields | E03-TEST-WF006-001-A |
| [AC-005](../../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) — SCORM ZIP is extracted on lesson save | E03-TEST-WF006-001-B |

---

## Notes

- **Chapter has no `field_course` back-reference**: The Course → Chapter relationship is stored only as `field_chapters` (forward reference on `lms_course`). There is no `field_course` on `lms_chapter`. Course assignment must be done by editing the `lms_course` entity and adding the chapter to `field_chapters`.
- **Lesson `admin_edit` form mode**: The form mode is registered externally by `lms_course.module::hook_entity_type_alter()` — it is not declared in `lms_lesson`'s entity annotation.
- **SCORM launch detection**: `_lms_lesson_extract_scorm()` reads `imsmanifest.xml` via PHP's `SimpleXMLElement`. If no launch file is detected, it creates a fallback `index.html` redirect in the extracted directory.
- **Zoom token caching**: `ZoomService` caches its Server-to-Server OAuth token in Drupal State under `lms_zoom_access_token` and `lms_zoom_token_expires`. Token is refreshed when expired.
- **`field_drip_days`**: Lesson-level drip scheduling field exists (`field_drip_days`, integer) but no drip enforcement logic is implemented beyond storing the value.
