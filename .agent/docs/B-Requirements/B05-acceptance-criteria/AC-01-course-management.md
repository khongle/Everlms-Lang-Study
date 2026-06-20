---
feature: F-001
status: DRAFT
---

# B05 — Acceptance Criteria for F-001 — Course Management

> Feature source: `../B03-features/01-course-management.md`
> Implementing modules: `lms_course`, `lms_chapter`, `lms_lesson`, `lms_faq`
> Implementation maturity: **as-built** (all controllers, forms, entity classes are fully implemented)

---

## AC-001: Admin or manager creates a course with chapters and lessons

**Priority:** Must
**Given** a user with the `administer lms_course` or `create lms_course` permission is logged in
**When** the user submits the course create form at `/workspace/courses/create` (or `/manage/courses/add`) with a `name`, at least one `field_chapters` reference pointing to a `lms_chapter` entity that itself references at least one `lms_lesson` entity
**Then** a `lms_course` entity is saved to `lms_course` / `lms_course_field_data` tables with `status=1` (published by default)
**And** the course appears in the course list at `/workspace/courses`

**Status:** as-built — `AdminController::courseCreateWizard()` and `CourseForm` are fully implemented.

---

## AC-002: Catalog page lists published courses with pagination

**Priority:** Must
**Given** at least one `lms_course` entity with `status=1` exists
**When** any visitor (including anonymous) requests `/courses`
**Then** `CoursePublicController::catalog()` returns an HTML page listing course cards in pages of 12
**And** the total course count and pager metadata are present in the rendered output

**Status:** as-built — `catalog()` is fully implemented with pager(12).

---

## AC-003: Course search and filter return accurate results

**Priority:** Must
**Given** published courses exist with varying categories, levels, and price types
**When** a visitor applies filters (search keyword, category term, level term, price type `free`/`paid`, rating range) on `/courses` or calls `/api/courses/search` with equivalent query parameters
**Then** only courses matching all active filters are returned
**And** an empty result set returns a list with zero items (no crash, no 500 error)

**Status:** as-built — `catalog()` and `searchApi()` implement multi-value filters with sort options (newest / price / default).

---

## AC-004: Course detail page shows curriculum, enrollment status, and continue URL

**Priority:** Must
**Given** a published `lms_course` entity with chapters and lessons exists
**When** any visitor requests `/courses/{lms_course}`
**Then** the page renders chapters, lessons, category, level, and instructor stats
**And** an authenticated enrolled student sees a "Continue Learning" link pointing to their next incomplete lesson (found by `detail()` logic traversing chapter/lesson weight order)
**And** an unenrolled visitor sees an enroll or purchase call-to-action

**Status:** as-built — `CoursePublicController::detail()` is fully implemented.

---

## AC-005: Free course enrollment creates a UserCourse record

**Priority:** Must
**Given** a published `lms_course` with `field_price_type=free` exists and the requesting user is authenticated and not already enrolled
**When** the user sends a GET request to `/courses/{lms_course}/enroll-free`
**Then** one `lms_user_course` entity is created with `field_course` pointing to the course, `uid` = the requesting user, and `field_status=enrolled`
**And** the response redirects to the course detail page

**Status:** as-built — `CourseEnrollmentController::enrollFree()` validates auth, price type, and duplicate enrollment before creating the entity.

---

## AC-006: Duplicate enrollment is prevented

**Priority:** Must
**Given** a student is already enrolled in a course (a `lms_user_course` record exists for uid + course)
**When** the student requests `/courses/{lms_course}/enroll-free` again
**Then** no second `lms_user_course` entity is created
**And** the response redirects to the course detail page without error

**Status:** as-built — `enrollFree()` checks for an existing `lms_user_course` before creating.

---

## AC-007: Membership-type course access is blocked for non-members

**Priority:** Must
**Given** a `lms_course` with `field_price_type=membership` exists
**When** a user without an active `lms_user_membership` (as determined by `MembershipManager::hasActiveMembership()`) attempts to access a lesson within that course
**Then** `hook_entity_access()` in `lms_course.module` denies view access to the lesson entity
**And** `MembershipAccessSubscriber` intercepts the resulting `AccessDeniedHttpException` on `/learn/*` paths and redirects to `/pricing` with a warning message

**Status:** as-built — both the hook and the event subscriber are implemented.

---

## AC-008: Admin course list supports filters and AJAX partial refresh

**Priority:** Must
**Given** the user has `administer lms_course` permission and is on `/workspace/courses`
**When** the user applies a filter (status, category, instructor, search keyword) or passes `ajax=1` as a query parameter
**Then** `AdminController::courseList()` returns the filtered table; with `ajax=1` the response is the table partial only (not the full page)

**Status:** as-built — `courseList()` is fully implemented with AJAX capability.

---

## AC-009: Manager cannot view or edit admin accounts

**Priority:** Must
**Given** a user with the manager role (but not `administrator`) is logged in
**When** the user accesses `/workspace/users`
**Then** `AdminController::userList()` excludes users who have the administrator role from the returned list
**And** `hook_entity_access()` blocks managers from viewing administrator user entities

**Status:** as-built — security filter is implemented in `userList()` and `hook_entity_access()`.

---

## AC-010: FAQs are scoped correctly to global, course, and lesson contexts

**Priority:** Must
**Given** `lms_faq` entities exist with `field_scope` values of `global`, `course`, and `lesson`
**When** the homepage is rendered (via `CoursePublicController::homepage()`) and the course detail page is rendered (via `detail()`)
**Then** the homepage loads only `field_scope=global` FAQs
**And** the course detail page loads only `field_scope=course` FAQs filtered by `field_course` matching the current course
**And** no `field_scope=lesson` FAQs appear on either public page

**Status:** as-built — FAQ loading logic is implemented in `homepage()` and `detail()`.

---

## AC-011: Editor can manage course content via workspace UI

**Priority:** Must
**Given** a user with `administer lms_course` permission and the manage UI at `/workspace/courses/{lms_course}/edit`
**When** the user changes the `name`, `field_description`, `field_thumbnail`, or `field_price` field and saves
**Then** `lms_course_field_data` reflects the updated values
**And** the public course detail page at `/courses/{lms_course}` shows the updated values after a page reload

**Status:** as-built — `AdminController::courseEditForm()` (admin_edit or manager_edit form mode depending on role) is fully implemented.

---

## AC-012: Homepage aggregates all required sections

**Priority:** Must
**Given** the site has published courses, membership plans, ratings, and `node:introduction` and `node:blog` content
**When** any visitor requests `/homepage`
**Then** `CoursePublicController::homepage()` returns a page containing featured courses, categories with thumbnails, membership plans (sorted by price), testimonials from `lms_rating`, and latest blog posts from `node:blog`
**And** the page supports 7 homepage style variants driven by `lms_theme` settings

**Status:** as-built — `homepage()` aggregates all sections. Note: the About page team data (visible at `/about`) is hardcoded placeholder content, not entity-backed.

---

## Local traceability (F-001)

| AC ID | Test Case | Implementing Story / Module | Status |
|-------|-----------|-----------------------------|--------|
| AC-001 | T-001 | course-create / lms_course | PENDING |
| AC-002 | T-002 | course-catalog / lms_course | PENDING |
| AC-003 | T-003 | course-search / lms_course | PENDING |
| AC-004 | T-004 | course-detail / lms_course | PENDING |
| AC-005 | T-005 | enroll-free / lms_course | PENDING |
| AC-006 | T-006 | enroll-dedupe / lms_course | PENDING |
| AC-007 | T-007 | membership-access / lms_course + lms_membership | PENDING |
| AC-008 | T-008 | admin-course-list / lms_course | PENDING |
| AC-009 | T-009 | manager-user-visibility / lms_course | PENDING |
| AC-010 | T-010 | faq-scope / lms_faq + lms_course | PENDING |
| AC-011 | T-011 | course-edit / lms_course | PENDING |
| AC-012 | T-012 | homepage-sections / lms_course | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
