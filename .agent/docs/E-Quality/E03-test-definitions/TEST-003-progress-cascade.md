---
id: TEST-003
type: Kernel
priority: P0 — Must
ac: AC-027, AC-028, AC-029
feature: F-003 (Learning Progress)
module: lms_progress
service: N/A (ProgressApiController delegates to entity API directly)
status: NOT_YET_AUTOMATED
---

# TEST-003 — Progress Cascade

> Verifies that marking a lesson complete correctly cascades: `lms_user_lesson` upsert → course progress recalculation → `lms_user_course.field_status` set to `completed` when all lessons are done. Also verifies idempotency (duplicate mark-complete calls do not double-count).

**Story:** [D03 lms_progress](../../D-Development/D03-stories/lms_progress.md)
**AC:** [AC-027 — Mark-complete updates lesson and course progress (B05 AC-03)](../../B-Requirements/B05-acceptance-criteria/AC-03-learning-progress.md) · [AC-028 — Idempotent re-marking (B05 AC-03)](../../B-Requirements/B05-acceptance-criteria/AC-03-learning-progress.md) · [AC-029 — Dashboard displays correct summary (B05 AC-03)](../../B-Requirements/B05-acceptance-criteria/AC-03-learning-progress.md)
**C12 Workflow:** [WF-001 Enrollment & Learning](../../C-Design/C12-workflows/WF-001-enrollment-learning.md)
**C05 Module:** [lms_progress](../../C-Design/C05-modules/lms_progress.md)

---

## TC-003-A: Marking one lesson complete updates `lms_user_lesson` and recalculates course progress

**Layer:** Kernel
**Given:**
- A `lms_course` entity with 1 chapter that has 4 `lms_lesson` entities
- A `lms_user_course` entity for the student with `field_progress = 0`, `field_status = enrolled`
- No `lms_user_lesson` entities exist for this student

**When:** `ProgressApiController::markComplete()` is called with `{course_id, lesson_id}` for the first lesson

**Then:**
- A `lms_user_lesson` entity is created with `field_lesson = <lesson_id>`, `field_status = completed`, `field_completed_at = <now>`
- `lms_user_course.field_progress` is updated to `25.00` (1 of 4 = 25%)
- `lms_user_course.field_status` remains `in_progress` (not 100% yet)
- JSON response: `{success: true, lesson_completed: true, course_completed: false, progress: 25.0, next_lesson_url: ...}`

### Test Data

| Input | Expected Output |
|-------|----------------|
| Lesson 1 of 4 marked complete | `field_progress=25.0`, `field_status=in_progress` |
| All 4 lessons marked complete | `field_progress=100.0`, `field_status=completed` |
| No prior enrollment | API returns error (unauthenticated or no enrollment) |

---

## TC-003-B: Marking all lessons complete sets course status to `completed`

**Layer:** Kernel
**Given:**
- Course with 3 lessons; student has 2 `lms_user_lesson` records (`completed`) already
- `lms_user_course.field_progress = 66.67`, `field_status = in_progress`

**When:** The third lesson is marked complete via `markComplete()`

**Then:**
- `lms_user_course.field_progress` is set to `100.0`
- `lms_user_course.field_status` is set to `completed`
- `lms_user_course.field_completed_at` is set to a non-null timestamp
- JSON response: `{course_completed: true, progress: 100.0, next_lesson_url: null}`

### Test Data

| Input | Expected Output |
|-------|----------------|
| 3rd of 3 lessons completed | `course_completed=true`, `field_status=completed` |
| 2nd of 3 lessons | `course_completed=false`, `field_status=in_progress` |

---

## TC-003-C: Re-marking an already-completed lesson is idempotent

**Layer:** Kernel
**Given:**
- `lms_user_lesson` entity already exists for student + lesson with `field_status = completed`
- `lms_user_course.field_progress = 50.0` (2 of 4 lessons done)

**When:** `markComplete()` is called again for the same lesson

**Then:**
- The existing `lms_user_lesson` entity is **updated** (not duplicated) — only one record per user+lesson
- `lms_user_course.field_progress` remains `50.0` (not double-counted to 75%)
- Response: `{lesson_completed: true, progress: 50.0}`
- `SELECT COUNT(*) FROM lms_user_lesson WHERE uid=X AND field_lesson_target_id=Y` = 1

### Test Data

| Input | Expected Output |
|-------|----------------|
| Re-mark already-completed lesson | Count = 1; progress unchanged |
| Mark incomplete lesson first time | Count = 1; progress incremented |

---

## TC-003-D: Dashboard progress summary is accurate

**Layer:** Kernel
**Given:**
- Student enrolled in 2 courses:
  - Course A: 3 lessons, 1 completed (`field_progress = 33.33`, `field_status = in_progress`)
  - Course B: 2 lessons, 2 completed (`field_progress = 100.0`, `field_status = completed`)

**When:** `UserPortalController::dashboard()` renders for the student

**Then:**
- The render array stats object contains: `enrolled=2`, `in_progress=1`, `completed=1`
- The "continue URL" points to the next incomplete lesson in Course A
- The `lms_dashboard` theme hook is returned

---

## Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_chapter`, `lms_lesson`, `lms_progress`; install entity schemas for `lms_course`, `lms_chapter`, `lms_lesson`, `lms_user_course`, `lms_user_lesson`; create course hierarchy (course → chapter → N lessons); create student user; create `lms_user_course` enrollment record
- Teardown: Entity storage cleared by KernelTestBase

## Notes

- `ProgressApiController::markComplete()` source: `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php`
- `UserPortalController::dashboard()` source: `web/modules/custom/lms_progress/src/Controller/UserPortalController.php`
- Progress recalculation formula (as-built): `floor((completed_lessons / total_lessons) * 100)` — note `floor()` means 1/3 = 33% not 33.33%
- Known code duplication: `dashboard()` and `myCourses()` share near-identical "find next lesson" logic — tests for one implicitly cover the other
- The `markComplete()` endpoint (`/api/lms/progress/mark-complete`) is separate from the mobile API endpoint (`/api/v1/progress/mark-complete`) in `lms_mobile_api`. Both must be tested; this spec covers the web endpoint. See [TEST-008](TEST-008-mobile-api-auth-otp.md) for the mobile variant.
- Expected file: `web/modules/custom/lms_progress/tests/src/Kernel/ProgressCascadeTest.php`
