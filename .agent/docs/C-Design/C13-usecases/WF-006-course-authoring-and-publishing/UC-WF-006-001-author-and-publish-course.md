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
An instructor builds and publishes a course.

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
Authoring role.

## Trigger
Instructor creates a course.

## Main success scenario
1. Create course (P033); set metadata.
2. Add chapters → lessons (media/attachments); optionally use AI builder (WF-010).
3. Attach quizzes (WF-002).
4. Review and publish → appears in catalog (P002).

## Alternate and exception flows
- Save draft; publish later.
- Review step (manager) before publish where used.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Manage courses | Manage | /workspace/courses |
| Course editor | Manage | /workspace/courses/{id}/edit |

## Acceptance and test contract
- 3-level hierarchy persists; publish → catalog visibility (B05 F-002).
