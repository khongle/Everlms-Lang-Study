# C05 Module - lms_chapter

**Name:** Chapters · **Phase:** 2 · **Epic:** [E02](../../D-Development/D02-epics/E02-course-catalog.md) · **Status:** done

## Responsibility
Sở hữu entity chapter — cấp nhóm trung gian, gom các lesson có thứ tự bên trong một course.

## Owned / related entities
- [lms_chapter](../C04-entities/lms_chapter.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md) | Course authoring & structure |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-006](../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md) | Course authoring & publishing |

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Chapter edit (in course authoring) | `/course/{course}/chapter/*` |

## Cross references
- Entity [C04 lms_chapter](../C04-entities/lms_chapter.md); cha là [lms_course](lms_course.md), con là [lms_lesson](lms_lesson.md).

## Dependencies
- lms_base, lms_course.

## Implementation notes
- Chapter được chỉnh sửa trong UI course-authoring; sắp thứ tự qua `weight`. Được tham chiếu bởi `lms_course.field_chapters`.
