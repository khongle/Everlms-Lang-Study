# C05 Module - lms_lesson

**Name:** Lessons & media · **Phase:** 2 · **Epic:** [E02](../../D-Development/D02-epics/E02-course-catalog.md) · **Status:** done

## Responsibility
Sở hữu entity lesson và việc phân phối nội dung: rich text, video (URL/file), SCORM, H5P, link Zoom, attachment, drip, và free-preview. Là module content có nhiều kết nối nhất (phụ thuộc vào 5 module: datetime, file, comment, lms_base, lms_chapter).

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
- H5P (configurable field `field_h5p_upload`); field link Zoom (lms_zoom, [INT-004](../C06-apis/INT-004-zoom.md)); phục vụ qua mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| Lesson player | `/course/{course}/lesson/{lesson}` |
| Lesson edit | `/lesson/{lesson}/edit` |

## Cross references
- Entity [C04 lms_lesson](../C04-entities/lms_lesson.md); progress ở [lms_progress](lms_progress.md); note ở [lms_note](lms_note.md).

## Dependencies
- datetime, file, comment, lms_base, lms_chapter.

## Implementation notes
- Tích hợp comment (core `comment`) cho thảo luận lesson; `datetime` cho scheduling/drip; `file` cho media/SCORM/attachment. Access được gate bởi enrollment + drip + free-preview.
