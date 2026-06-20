# C12 Workflow - WF-006 Course authoring & publishing

## Business source
[B04 WF-006](../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md)

## Modules
[lms_course](../C05-modules/lms_course.md), [lms_chapter](../C05-modules/lms_chapter.md), [lms_lesson](../C05-modules/lms_lesson.md), [lms_ai](../C05-modules/lms_ai.md)

## Related features (B03)
[F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md), [F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## State model
`lms_course.status` (+ chapters/lessons):
| State | Meaning |
| ----- | ------- |
| draft (unpublished) | being authored |
| in_review | optional editorial review |
| approved_or_completed (published) | visible in catalog |
| archived (unpublished/retired) | removed from catalog |

## Permission model
- Initiator: instructor (own) / content_editor.
- Approver: manager (publish) where review is used.

## Notifications
Optional editor notifications on submit/publish. Content edited via entity forms (editorial-usability).
