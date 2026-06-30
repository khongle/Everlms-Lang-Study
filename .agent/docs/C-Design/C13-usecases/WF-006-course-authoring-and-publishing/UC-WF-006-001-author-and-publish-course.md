---
usecase_id: UC-WF-006-001
workflow: WF-006-course-authoring-and-publishing
status: as-built
primary_actor: Instructor
modules: [lms_course, lms_chapter, lms_lesson, lms_ai]
processes: [course-authoring-and-publishing]
---

# UC-WF-006-001 - Author and publish a course

## Intent
An instructor builds and publishes a real course with a persisted curriculum.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-006 |
| Business flow | create → curriculum → publish |
| Modules | lms_course, lms_chapter, lms_lesson, lms_ai |
| Related features | F-002, F-018 |
| B04 source | WF-006 |

## Actors
Instructor/content_editor (primary); manager (publish approval, optional).

## Preconditions
- User has the `instructor` or `content_editor` role.
- The role can create courses, edit own courses, view course workspace pages, and
  use the administration theme.
- Workspace course creation renders the `lms_course` entity form inside the
  workspace shell.

## Trigger
Instructor starts a new course from `/workspace/courses/create`.

## Main success scenario
1. Instructor logs in through `/user/login`.
2. Instructor opens `/workspace/courses` and sees an own-course scoped list.
3. Instructor opens `/workspace/courses/create`.
4. Instructor fills course metadata, including title, description, level,
   category/collection fields, and draft status.
5. Instructor adds a chapter through course `field_chapters`.
6. Instructor adds a lesson through the chapter `field_lessons` nested Inline
   Entity Form.
7. Instructor saves the draft course and returns to `/workspace/courses`.
8. Instructor reopens `/workspace/courses/{id}/edit`.
9. Instructor checks published status and saves.
10. Anonymous learner searches `/courses?search=...`, opens `/courses/{id}`, and
    verifies the Curriculum tab shows the chapter and lesson.

## Alternate and exception flows
- Save draft; publish later.
- Review step (manager) before publish where used.
- H5P embed can be authored where configured. The H5P upload widget remains
  hidden until the Drupal 11 widget integration is validated.
- AI builder support belongs to WF-010 and may generate draft structure before
  the instructor reviews and saves it.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login | Account | /user/login |
| Manage courses | Manage | /workspace/courses |
| Create course | Manage | /workspace/courses/create |
| Course editor | Manage | /workspace/courses/{id}/edit |
| Course catalog | Public | /courses |
| Course detail | Public | /courses/{id} |

## Acceptance and test contract
- Real user session uses the login form, not a one-time login URL.
- Course, chapter, and lesson are created through the UI and persist as linked
  entities.
- Draft course is not treated as public until published.
- Published course appears in the catalog and public detail page.
- Curriculum tab on the public detail page displays the saved chapter and lesson.
- Screenshots are stored in the WF-006 round-1 evidence folder, including
  before/after form states.
