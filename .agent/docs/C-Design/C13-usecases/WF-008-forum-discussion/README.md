# C13 Usecases - WF-008 Forum discussion

## Workflow
- C12: [WF-008](../../C12-workflows/WF-008-forum-discussion.md) · B04: [WF-008](../../../B-Requirements/B04-business-workflows/WF-008-forum-discussion.md)
- Modules: lms_forum, lms_notification
- Business flow: login -> đăng topic -> reply -> notify owner -> đánh dấu best answer
  -> resolved -> hiển thị trên index anonymous.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-008-001](UC-WF-008-001-post-topic-and-reply.md) | Post a topic and reply | Student | Forum discussion | P013, P014 |

## Completion rule
Đóng khi hai user đã authenticated thật có thể hoàn tất việc tạo topic, reply, và
resolve best-answer; một `lms_notification` được tạo cho topic owner; và
`/forum` ở chế độ anonymous hiển thị topic resolved vừa tạo mà không cần rebuild cache thủ công.
Bằng chứng (evidence) phải bao gồm screenshot cho từng trạng thái trang/form và verify DB
với các record topic/reply/notification.
