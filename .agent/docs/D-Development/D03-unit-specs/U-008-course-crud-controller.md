# D03 — Unit Spec: Course CRUD Controller + Templates

**Unit ID:** U-008
**Group:** G-CONTENT
**Title:** Course CRUD controller + templates
**Status:** DONE
**Priority:** P0

---

## Scope

Admin-facing controller and templates for course management: workspace dashboard, course list, course create/edit wizard, and related admin views (quiz list, order list, user list, reports). Also covers the `CourseEnrollmentController` for free enrollment and the `WorkspaceContentController` for generic content CRUD.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_course/src/Controller/AdminController.php` | Admin dashboard + CRUD actions |
| `web/modules/custom/lms_course/src/Controller/CourseEnrollmentController.php` | Free enrollment endpoint |
| `web/modules/custom/lms_course/src/Controller/WorkspaceContentController.php` | Generic workspace CRUD |
| `web/modules/custom/lms_course/src/Controller/PageController.php` | Misc static pages |
| `web/modules/custom/lms_course/src/Form/CourseForm.php` | Course entity form |
| `web/modules/custom/lms_course/src/Form/ApproveOrderForm.php` | Order approval form |
| `web/modules/custom/lms_course/lms_course.routing.yml` | Admin + workspace routes |
| `web/modules/custom/lms_course/templates/lms-admin-dashboard.html.twig` | Admin dashboard |
| `web/modules/custom/lms_course/templates/lms-admin-courses.html.twig` | Course list |
| `web/modules/custom/lms_course/templates/lms-admin-courses-table.html.twig` | Course table partial |
| `web/modules/custom/lms_course/templates/lms-admin-course-create.html.twig` | Create wizard |
| `web/modules/custom/lms_course/templates/lms-admin-content.html.twig` | Content list |
| `web/modules/custom/lms_course/templates/lms-admin-content-table.html.twig` | Content table partial |
| `web/modules/custom/lms_course/templates/lms-admin-orders.html.twig` | Order list |
| `web/modules/custom/lms_course/templates/lms-admin-quizzes.html.twig` | Quiz list |
| `web/modules/custom/lms_course/templates/lms-admin-ratings.html.twig` | Ratings list |
| `web/modules/custom/lms_course/templates/lms-admin-reports.html.twig` | Reports view |
| `web/modules/custom/lms_course/templates/lms-admin-users.html.twig` | User list |
| `web/modules/custom/lms_course/templates/lms-admin-users-table.html.twig` | User table partial |
| `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig` | Admin sidebar nav |
| `web/modules/custom/lms_course/templates/lms-admin-entity-form.html.twig` | Generic entity form wrapper |
| `web/modules/custom/lms_course/templates/lms-admin-entity-view.html.twig` | Generic entity view wrapper |
| `web/modules/custom/lms_course/templates/lms-admin-user-form.html.twig` | User form wrapper |

---

## Entities / Services / Controllers / Templates

**AdminController methods:** `dashboard()`, `courseList()`, `quizList()`, `orderList()`, `userList()`, `bulkUsers()`, `reports()`, `courseCreateWizard()`, `courseEditForm()`, `quizEditForm()`, `quizCreateForm()`, `userEditForm()`, `userCreateForm()`, `orderEditForm()`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | All routes resolve; templates render |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Create/edit/delete cycle verified in workspace |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `/workspace` renders admin dashboard
- [x] Course create wizard at `/workspace/courses/create`
- [x] Course list at `/workspace/courses`
- [x] Free enrollment endpoint at `/courses/{lms_course}/enroll-free`
- [x] Order approval form functional
- [x] Admin routes protected by appropriate permissions

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-005 | Course entity required |
| U-006 | Chapter entity for wizard |
| U-007 | Lesson entity for wizard |
| U-009 | Catalog shares public routes |
| U-025 | Order entity used in order list |

---

*End of U-008-course-crud-controller.md*
