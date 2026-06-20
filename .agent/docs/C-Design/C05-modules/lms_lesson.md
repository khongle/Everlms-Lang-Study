# C05 Module - lms_lesson

**Name:** Lessons & media · **Phase:** 2 · **Epic:** [E02](../../D-Development/D02-epics/E02-course-catalog.md) · **Status:** done

## Responsibility
Owns the lesson entity and content delivery: rich text, video (URL/file), SCORM, H5P, Zoom links, attachments, drip, and free-preview. The most-connected content module (depends on 5 modules: datetime, file, comment, lms_base, lms_chapter).

## Owned / related entities
- [lms_lesson](../C04-entities/lms_lesson.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-003](../../B-Requirements/B03-features/F-003-lesson-delivery-and-media.md) | Lesson delivery & media |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |

## Integrations / APIs (B06 / C06)
- H5P (`field_h5p_upload` configurable field); Zoom link fields (lms_zoom, [INT-004](../C06-apis/INT-004-zoom.md)); served via mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| Lesson player | `/course/{course}/lesson/{lesson}` |
| Lesson edit | `/lesson/{lesson}/edit` |

## Cross references
- Entity [C04 lms_lesson](../C04-entities/lms_lesson.md); progress [lms_progress](lms_progress.md); notes [lms_note](lms_note.md).

## Dependencies
- datetime, file, comment, lms_base, lms_chapter.

## Implementation notes
- Comment integration (core `comment`) for lesson discussion; `datetime` for scheduling/drip; `file` for media/SCORM/attachments. Access gated by enrollment + drip + free-preview.
