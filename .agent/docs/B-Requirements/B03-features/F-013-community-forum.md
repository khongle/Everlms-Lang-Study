# F-013 - Community forum

**Status:** built
**Primary module:** [lms_forum](../../C-Design/C05-modules/lms_forum.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-008 Forum discussion](../B04-business-workflows/WF-008-forum-discussion.md)
**Acceptance:** [B05 F-013](../B05-acceptance-criteria/F-013-community-forum.md)
**Source:** `web/modules/custom/lms_forum/`

## Requirement
Learner và instructor đăng discussion topic (`lms_forum_topic`) và reply (`lms_forum_reply`), được tổ chức theo taxonomy `forum_category` và có thể giới hạn phạm vi (scope) theo một course. Việc đăng bài có thể notify subscriber/instructor (F-017).

## Business value
Community Q&A làm tăng engagement, peer learning và sự hiện diện của instructor, giảm tải cho support và cải thiện retention.

## Scope notes
- Hai entity: `lms_forum_topic` + `lms_forum_reply`.
- Category qua vocabulary `forum_category`.
- Moderation/permission theo từng role; reply mới có thể trigger notification.

## Cross references
- Design: [C04 lms_forum_topic](../../C-Design/C04-entities/lms_forum_topic.md), [C04 lms_forum_reply](../../C-Design/C04-entities/lms_forum_reply.md), [C12 WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md)
