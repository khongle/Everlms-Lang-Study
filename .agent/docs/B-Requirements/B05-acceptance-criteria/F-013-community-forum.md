# F-013 Acceptance Criteria - Community forum

## Core criteria
1. **Given** an authenticated user, **when** they create a topic (`lms_forum_topic`) in a `forum_category`, **then** it persists and is listed.
2. **Given** a topic, **when** a user posts a reply (`lms_forum_reply`), **then** it appears under the topic.
3. **Given** a new reply, **when** posted, **then** relevant subscribers/instructor can be notified (F-017).
4. **Given** a moderator role, **when** they edit/delete/lock a topic, **then** the action is permitted; ordinary users cannot.

## Evidence expected
- Module: [lms_forum](../../C-Design/C05-modules/lms_forum.md).
- Test: topic/reply CRUD + permission test by role (E03).
