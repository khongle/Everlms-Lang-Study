# C04 Entity - lms_note

**Display name:** Note
**Owning module(s):** [lms_note](../C05-modules/lms_note.md)
**Mode:** as-built (Content Entity)

## Purpose
A learner's private note attached to a lesson/course, optionally timestamped to a video position.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | owner (private) |
| field_lesson | ref → lms_lesson |
| field_course | ref → lms_course |
| field_body | formatted note text |
| field_video_timestamp | linked video position |
| created, changed | timestamps |

## Relationships
- N→1 `user`, `lms_lesson`, `lms_course`.

## Editorial/data management
User-owned and private (only the author reads/edits). Surfaced in the player + dashboard. See [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md).
