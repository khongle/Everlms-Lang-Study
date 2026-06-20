---
usecase_id: UC-WF006-003
workflow: WF-006
title: User without administer lms_course is blocked from course creation
type: permission
status: as-built
---

# UC-WF006-003 — Course Creation Permission Gate (Permission Path)

Covers the access-denial path when an authenticated user who lacks the `administer lms_course` permission attempts to access course creation routes (`/workspace/courses/create`, `/manage/courses/add`, `/manage/courses/ai-builder`). The routing layer enforces permission checks at the route requirement level.

---

## Actors

| Actor | Role |
|-------|------|
| Student / restricted user | Authenticated user without `administer lms_course` |
| System | Drupal route access check; HTTP 403 response |

---

## Preconditions

1. User is authenticated.
2. User does NOT have the `administer lms_course` permission (i.e., not an `administrator` or `manager` role).
3. Routes `lms_course.admin_course_create`, `entity.lms_course.add_form`, and `lms_ai.course_builder_form` all require `_permission: administer lms_course`.

---

## Trigger

User navigates to any of: `/workspace/courses/create`, `/manage/courses/add`, or `/manage/courses/ai-builder`.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** the user is authenticated but has a role without `administer lms_course` | User has, e.g., the default `authenticated` role or a custom role without this permission |
| 2 | **When** the user visits `/workspace/courses/create` | Drupal routing evaluates `_permission: administer lms_course` for route `lms_course.admin_course_create` |
| 3 | **Then** the route returns HTTP 403 | Drupal raises `AccessDeniedHttpException`; the standard 403 page is rendered (theme-level); controller code is never executed |
| 4 | **When** the user visits `/manage/courses/add` | Drupal evaluates `create` access via `CourseAccessControlHandler::checkCreateAccess()`; requires `create lms_course` permission (not `administer lms_course`); if user lacks `create lms_course`, HTTP 403 is returned |
| 5 | **When** the user visits `/manage/courses/ai-builder` | Route `lms_ai.course_builder_form` requires `_permission: administer lms_course`; Drupal returns HTTP 403 |
| 6 | **When** the user visits `/workspace/courses` (list) | Route `lms_course.admin_courses` requires `_permission: administer lms_course`; HTTP 403 |

---

## Entities Mutated

None — no entity is created or modified. The access check terminates before any controller logic runs.

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| HTTP 403 response | Drupal routing layer | Standard Drupal 403 page rendered via active theme |
| No log entry | — | Route permission denials are not individually logged by default |

---

## Permission Reference

| Route | Path | Permission Required |
|-------|------|---------------------|
| `lms_course.admin_course_create` | `/workspace/courses/create` | `administer lms_course` |
| `lms_course.admin_courses` | `/workspace/courses` | `administer lms_course` |
| `lms_course.admin_quiz.create` | `/workspace/quizzes/create` | `_entity_create_access: lms_quiz` |
| `lms_ai.course_builder_form` | `/manage/courses/ai-builder` | `administer lms_course` |
| `lms_ai.engine_users_form` | `/admin/everlms-ai-engine` | `administer site configuration` |
| `entity.lms_course.add_form` | `/manage/courses/add` | `create lms_course` (via `CourseAccessControlHandler`) |
| `entity.lms_course.edit_form` | `/manage/courses/{id}/edit` | `edit lms_course` OR `edit own lms_course` AND owner |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-001](../../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) — Course creation requires `administer lms_course` or `create lms_course` permission | E03-TEST-WF006-003-A |

---

## Notes

- **`administer lms_course` vs. `create lms_course`**: The workspace UI routes (`/workspace/*`) require `administer lms_course`. The raw Drupal entity CRUD routes (`/manage/courses/add`) require `create lms_course`. A user with only `create lms_course` can add courses via `/manage/courses/add` but cannot access the workspace UI or the AI builder.
- **Manager role**: The `manager` role in EverLMS has `administer lms_course` and so can access all creation routes. See [C02-user-roles](../../../C-Design/C02-user-roles.md) for the full role-permission matrix.
- **`hook_entity_create_access` for users**: `lms_course.module::hook_entity_create_access()` grants user-creation access to managers with `administer lms_course` — this is separate from course creation and applies only to the user entity type at `/workspace/users/create`.
- **`CourseAccessControlHandler`**: The custom access handler (`lms_course/src/Access/CourseAccessControlHandler.php`) enforces `view lms_course` (view), `edit lms_course` or `edit own lms_course` (update), `delete lms_course` (delete), `create lms_course` (create). These are distinct from the workspace route permissions.
