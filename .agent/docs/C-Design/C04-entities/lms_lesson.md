# C04 Entity - lms_lesson

**Display name:** Lesson
**Owning module(s):** [lms_lesson](../C05-modules/lms_lesson.md)
**Mode:** as-built (Content Entity, translatable; most-connected entity — 5 module deps)

## Purpose
The leaf learning unit: delivers content (rich text, video, SCORM, H5P, attachments) and optionally links a quiz. Supports content drip and free preview.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid, langcode, default_langcode | keys + translation |
| name | lesson title |
| field_content | formatted lesson body |
| field_type | lesson type (video/text/scorm/h5p/zoom…) |
| field_video_url, field_video_file | external video URL / uploaded video file |
| field_zoom_meeting_id, field_zoom_schedule | live-session link (lms_zoom) |
| field_scorm_package | SCORM upload (file) |
| field_h5p_embed, field_h5p_upload | H5P interactive content (configurable `h5p` field) |
| field_pptx_attachment | slides attachment |
| field_attachments | **multi → downloadable files** |
| field_quiz | ref → lms_quiz (attached assessment) |
| field_duration | lesson length |
| field_free_preview | preview without enrollment |
| field_drip_days | days after enrollment before unlock |
| field_comments | comments toggle |
| weight, status, created, changed | order, publish, timestamps |

## Relationships
- Parent: referenced by `lms_chapter.field_lessons`.
- Links: `field_quiz` → `lms_quiz`; tracked by `lms_user_lesson`; `lms_note.field_lesson`.

## Editorial/data management
Instructors author lessons via the Lesson entity form; media referenced as files (not inlined). Free-preview + drip control access timing (paired with F-004 gating). All copy in fields. Cache invalidated on save; access varies by enrollment + drip. See [C16 sheet 01](../C16-item-solutions/01-entities.md).
