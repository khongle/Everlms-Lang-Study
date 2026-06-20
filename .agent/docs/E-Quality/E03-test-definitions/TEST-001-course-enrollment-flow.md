---
id: TEST-001
type: Kernel + Functional
priority: P0 — Must
ac: AC-004, AC-026
feature: F-001 (Course Management), F-003 (Learning Progress)
module: lms_course, lms_progress, lms_order
service: lms_course.breadcrumb
status: NOT_YET_AUTOMATED
---

# TEST-001 — Course Enrollment Flow

> Verifies the full enrollment path: free-course one-click enroll, paid-order-triggered enroll, duplicate-enroll guard, and lesson-view auto-enroll. Golden flow for the most critical user journey in EverLMS.

**Story:** [D03 lms_course](../../D-Development/D03-stories/lms_course.md) · [D03 lms_progress](../../D-Development/D03-stories/lms_progress.md)
**AC:** [AC-004 — Free enrollment (B05 AC-01)](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) · [AC-026 — Lesson view auto-enroll (B05 AC-03)](../../B-Requirements/B05-acceptance-criteria/AC-03-learning-progress.md)
**C12 Workflow:** [WF-001 Enrollment & Learning](../../C-Design/C12-workflows/WF-001-enrollment-learning.md)
**C05 Module:** [lms_course](../../C-Design/C05-modules/lms_course.md) · [lms_progress](../../C-Design/C05-modules/lms_progress.md)

---

## TC-001-A: Free course — direct enroll via `/courses/{id}/enroll-free`

**Layer:** Kernel
**Given:**
- A published `lms_course` entity exists with `field_price_type = free`, `field_price = 0`
- An authenticated user with role `student` is logged in
- No `lms_user_course` record exists for user + course

**When:** `CourseEnrollmentController::enrollFree()` is invoked (HTTP GET `/courses/{lms_course}/enroll-free`)

**Then:**
- A `lms_user_course` entity is created in the `lms_user_course` DB table with `uid = <student>`, `field_course = <course_id>`, `field_status = enrolled`, `field_progress = 0`
- HTTP response is a redirect to the course detail page or the first lesson
- An optional `lms_notification` entity may be created (in-app enrollment notification)

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid free course + unenrolled student | HTTP 302 redirect; `lms_user_course` row created |
| Non-existent `course_id` | HTTP 404 |
| `field_price_type = paid` course | Controller blocks enroll; HTTP 403 or redirect to checkout |
| Student already enrolled (duplicate) | No second `lms_user_course` row; existing enrollment returned |

### Setup / Teardown

- Setup: Install modules `lms_base`, `lms_course`, `lms_progress`; create published free `lms_course`; create `student` user
- Teardown: Delete test entities; entity storage cleared by Kernel test framework

---

## TC-001-B: Paid course — enrollment triggered by order completion

**Layer:** Kernel
**Given:**
- A paid `lms_course` with `field_price_type = paid` exists
- An `lms_order` entity exists for the student with `field_items` referencing an `lms_order_item` that references the course
- The order `field_status` is being set to `completed` (simulating payment success)

**When:** `hook_entity_update()` in `lms_order.module` fires on the order save

**Then:**
- `_lms_order_process_completed_order()` iterates order items and calls `lms_user_course` entity creation
- `lms_user_course` row is created with `field_status = enrolled`
- A confirmation `lms_notification` entity is created for the user
- `plugin.manager.mail` dispatches an email via `order_completed` mail key

### Test Data

| Input | Expected Output |
|-------|----------------|
| Order transitions `pending` → `completed` | `lms_user_course` created; notification created |
| Order already `completed` on insert | `_lms_order_process_completed_order()` called from `hook_entity_insert` |
| Order transitions to `cancelled` | No `lms_user_course` created |

### Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_order`, `lms_payment`, `lms_progress`, `lms_notification`; create course, student user, order + order item entities
- Teardown: Delete test entities

---

## TC-001-C: Lesson view auto-enroll

**Layer:** Functional
**Given:**
- A published `lms_course` with at least one `lms_chapter` → `lms_lesson` hierarchy
- An authenticated `student` user with no existing `lms_user_course` for the course

**When:** The student GETs `/learn/{lms_course}/{lms_lesson}`

**Then:**
- `UserPortalController::lessonView()` creates a `lms_user_course` entity with `field_status = enrolled` before rendering
- The lesson content is rendered (HTTP 200) without requiring a separate enroll step
- The response includes the `lms_lesson_view` theme render

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid course + lesson, no prior enrollment | HTTP 200; `lms_user_course` row created |
| Anonymous user | HTTP 302 redirect to login |
| Lesson not in course | HTTP 403 or 404 |

### Setup / Teardown

- Setup: Full Drupal (`BrowserTestBase`); create course hierarchy with published chapter and lesson; create student user
- Teardown: Standard BrowserTestBase cleanup

---

## TC-001-D: Duplicate enrollment guard

**Layer:** Kernel
**Given:**
- A `lms_user_course` entity already exists for user + course with `field_status = enrolled`

**When:** `CourseEnrollmentController::enrollFree()` is called again for the same user + course

**Then:**
- No second `lms_user_course` entity is created
- `SELECT COUNT(*) FROM lms_user_course WHERE uid = X AND field_course_target_id = Y` returns exactly 1

### Test Data

| Input | Expected Output |
|-------|----------------|
| Existing enrollment + re-enroll call | Count = 1 (no duplicate) |
| Different user, same course | Two rows, one per user |

### Setup / Teardown

- Setup: Install `lms_course`, `lms_progress`; create course, student user, pre-existing `lms_user_course`
- Teardown: Delete test entities

---

## Notes

- `CourseEnrollmentController` source: `web/modules/custom/lms_course/src/Controller/CourseEnrollmentController.php`
- `_lms_order_process_completed_order()` source: `web/modules/custom/lms_order/lms_order.module`
- `UserPortalController::lessonView()` source: `web/modules/custom/lms_progress/src/Controller/UserPortalController.php`
- The auto-enroll in `lessonView()` is a side-effect: it mutates `lms_user_course` before rendering — this makes TC-001-C a Functional (not Kernel) test because it requires a real HTTP request through routing.
- Known bug: `lms_progress.module::lms_progress_comment_form_submit_redirect()` may throw undefined `$response` on non-AJAX path — unrelated to enrollment but may affect Functional test teardown if comments are involved.
- Expected file: `web/modules/custom/lms_course/tests/src/Kernel/EnrollmentFlowTest.php` (Kernel tests TC-001-A/B/D) + `web/modules/custom/lms_progress/tests/src/Functional/LessonViewAutoEnrollTest.php` (TC-001-C)
