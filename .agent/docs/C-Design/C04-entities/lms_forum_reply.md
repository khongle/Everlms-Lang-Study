# C04 Entity - lms_forum_reply

**Display name:** Forum Reply
**Owning module(s):** [lms_forum](../C05-modules/lms_forum.md)
**Mode:** as-built (Content Entity)

## Purpose
A reply within a forum topic; supports threading (parent), best-answer marking, and upvotes.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | author |
| field_topic | ref → lms_forum_topic |
| field_body | formatted reply |
| field_parent | self-ref (threaded replies) |
| field_best_answer | accepted-answer flag |
| field_upvotes | vote count |
| created | timestamp |

## Relationships
- N→1 `lms_forum_topic`; self-ref `field_parent` for threads.

## Editorial/data management
Authored by users; best-answer set by topic owner/instructor; updates topic counters/last-reply. See [F-013](../../B-Requirements/B03-features/F-013-community-forum.md).
