# WF-008 - Forum discussion

## Business flow
A learner or instructor starts a discussion topic (`lms_forum_topic`) in a category (`forum_category`), optionally scoped to a course. Other users post replies (`lms_forum_reply`). New topics/replies can notify the instructor or topic subscribers (F-017). Staff can moderate (edit/delete/lock) per role permissions.

## Participating modules
[lms_forum](../../C-Design/C05-modules/lms_forum.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Related features (B03)
[F-013](../B03-features/F-013-community-forum.md), [F-017](../B03-features/F-017-notifications.md)

## Trigger
User creates a topic or posts a reply.

## Outcome
Topic/reply persisted and visible; relevant users notified.

## Design link
[C12 WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md)
