# C13 Usecases - WF-006 Course authoring & publishing

## Workflow
- C12: [WF-006](../../C12-workflows/WF-006-course-authoring-and-publishing.md) · B04: [WF-006](../../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md)
- Modules: lms_course, lms_chapter, lms_lesson, lms_ai
- Business flow: form login -> create draft -> build curriculum -> publish -> verify catalog.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-006-001](UC-WF-006-001-author-and-publish-course.md) | Author and publish a course | Instructor | Course authoring | P032, P033 |

## Completion rule
Closed when a real instructor/content editor logs in through `/user/login`,
creates a draft course from `/workspace/courses/create`, adds at least one
chapter and one lesson through the nested UI, publishes the course from
`/workspace/courses/{id}/edit`, and an anonymous learner can see the course plus
curriculum on `/courses` and `/courses/{id}`.

Evidence must include screenshots for every step, before/after form-fill states,
and a database verification of the saved course/chapter/lesson hierarchy.
