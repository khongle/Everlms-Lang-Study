# C04 Entity - lms_user_lesson

**Display name:** User Lesson (per-lesson progress)
**Owning module(s):** [lms_progress](../C05-modules/lms_progress.md)
**Mode:** as-built (Content Entity)

## Purpose
Granular per-lesson progress for a learner (completion + video resume position), aggregated into `lms_user_course`.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | learner |
| field_lesson | ref → lms_lesson |
| field_status | not-started / in-progress / completed |
| field_video_position | resume position (seconds) |
| field_completed_at | completion timestamp |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_lesson`; rolls up to `lms_user_course`.

## Editorial/data management
System-managed by the player (mark-complete / video progress). Not editor-authored. See [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md).
