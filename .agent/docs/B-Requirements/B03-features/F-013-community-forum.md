# F-013 - Community forum

**Status:** built
**Primary module:** [lms_forum](../../C-Design/C05-modules/lms_forum.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-008 Forum discussion](../B04-business-workflows/WF-008-forum-discussion.md)
**Acceptance:** [B05 F-013](../B05-acceptance-criteria/F-013-community-forum.md)
**Source:** `web/modules/custom/lms_forum/`

## Requirement
Learners and instructors post discussion topics (`lms_forum_topic`) and replies (`lms_forum_reply`), organised by `forum_category` taxonomy and optionally scoped to a course. Posting can notify subscribers/instructors (F-017).

## Business value
Community Q&A increases engagement, peer learning, and instructor presence, reducing support load and improving retention.

## Scope notes
- Two entities: `lms_forum_topic` + `lms_forum_reply`.
- Category via `forum_category` vocabulary.
- Moderation/permissions per role; new replies can trigger notifications.

## Cross references
- Design: [C04 lms_forum_topic](../../C-Design/C04-entities/lms_forum_topic.md), [C04 lms_forum_reply](../../C-Design/C04-entities/lms_forum_reply.md), [C12 WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md)
