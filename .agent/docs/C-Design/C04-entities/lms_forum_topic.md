# C04 Entity - lms_forum_topic

**Display name:** Forum Topic
**Owning module(s):** [lms_forum](../C05-modules/lms_forum.md)
**Mode:** as-built (Content Entity)

## Purpose
A discussion thread, optionally scoped to a course and categorised; carries engagement counters and moderation flags.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | author |
| name | topic title |
| field_body | formatted body |
| field_course | ref → lms_course (scope) |
| field_category | `forum_category` taxonomy |
| field_replies_count, field_views | engagement counters |
| field_pinned, field_locked, field_resolved | moderation/state flags |
| field_last_reply_date | activity sort |
| created, changed | timestamps |

## Relationships
- 1→N `lms_forum_reply` (`field_topic`); N→1 `lms_course`.

## Editorial/data management
Authored by learners/instructors; moderated by staff (pin/lock/resolve). See [F-013](../../B-Requirements/B03-features/F-013-community-forum.md), [WF-008](../C12-workflows/WF-008-forum-discussion.md).
