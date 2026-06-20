# C12 Workflow - WF-008 Forum discussion

## Business source
[B04 WF-008](../../B-Requirements/B04-business-workflows/WF-008-forum-discussion.md)

## Modules
[lms_forum](../C05-modules/lms_forum.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-013](../../B-Requirements/B03-features/F-013-community-forum.md), [F-017](../../B-Requirements/B03-features/F-017-notifications.md)

## State model
`lms_forum_topic`:
| State | Meaning |
| ----- | ------- |
| submitted (open) | topic posted |
| in_review (active) | replies accruing |
| approved_or_completed (resolved) | best answer / `field_resolved` |
| archived (locked) | `field_locked` / closed |

## Permission model
- Initiator: any authenticated learner/instructor.
- Moderator: staff pin/lock/resolve/delete.

## Notifications
New reply → topic owner/subscribers; best-answer → replier (in_app; email/push partial R-005).
