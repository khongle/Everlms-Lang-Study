# WF-008 - Forum discussion

## Business flow
Learner hoặc instructor tạo một discussion topic (`lms_forum_topic`) trong một category (`forum_category`), có thể giới hạn theo course. Các user khác đăng reply (`lms_forum_reply`). Topic/reply mới có thể gửi thông báo cho instructor hoặc các subscriber của topic (F-017). Staff có thể moderate (edit/delete/lock) theo quyền của từng role.

## Participating modules
[lms_forum](../../C-Design/C05-modules/lms_forum.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Related features (B03)
[F-013](../B03-features/F-013-community-forum.md), [F-017](../B03-features/F-017-notifications.md)

## Trigger
User tạo một topic hoặc đăng một reply.

## Outcome
Topic/reply được lưu và hiển thị; các user liên quan được thông báo.

## Design link
[C12 WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md)
