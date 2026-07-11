# C12 Workflow - WF-008 Forum discussion

**Object:** forum-topic

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

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | in_review | reply đầu tiên được đăng |
| in_review | approved_or_completed | owner đánh dấu best answer |
| approved_or_completed | archived | moderator lock topic |

## Permission model
- Initiator: bất kỳ learner/instructor đã authenticated.
- Moderator: staff pin/lock/resolve/delete.

## Concrete flow
1. Visitor anonymous mở `/forum` và đọc được danh sách topic công khai.
2. Student đăng nhập qua `/user/login`.
3. Student mở `/forum` và thấy action `New Topic`.
4. Student mở `/forum/new`, điền title/body/category, và lưu.
5. Topic redirect tới `/forum/{id}`. Trang hiển thị topic và một form reply
   inline cho user đã authenticated.
6. Một student thứ hai đăng nhập qua `/user/login`, mở topic, điền
   form reply, và đăng.
7. Reply được lưu, tăng số reply của topic, cập nhật ngày reply gần nhất,
   và tạo một `lms_notification` cho topic owner.
8. Topic owner mở lại topic và đánh dấu reply đó là best answer.
9. Topic chuyển thành resolved (`field_resolved = 1`) và reply được gắn cờ
   best answer (`field_best_answer = 1`).
10. Visitor anonymous vẫn mở được URL của topic nhưng chỉ thấy teaser/CTA thay vì
    thread reply dành cho user đã authenticated.
11. `/forum` ở chế độ anonymous phải hiển thị topic resolved vừa tạo mà không cần
    rebuild cache thủ công.

## Implementation requirements
- `/forum/new` phải render wrapper authoring forum của sản phẩm, không phải
  entity add form thô của Drupal.
- Render array của forum index phải khai báo cache tag `lms_forum_topic_list` và
  các context user/url.
- Render array của topic detail phải thay đổi theo user vì user anonymous thấy
  teaser, author thấy action best-answer, và owner của reply thấy edit/delete.
- Việc lưu topic, lưu reply, và action best-answer phải invalidate cache tag
  list/detail của forum.
- Form reply hướng tới learner phải ẩn chi tiết triển khai của text-format.
- Status message không được lộ debug text và phải render các
  chuỗi `.placeholder` của Drupal thành text inline dễ đọc.

## Acceptance and evidence contract
- Screenshot được lưu tại
  `.agent/docs/E-Quality/E00-workflows/round-1/_screens/008-forum-discussion/`.
- Các bước form ghi lại trạng thái trước và sau khi điền.
- Dữ liệu test phải là user thật và các record `lms_forum_topic`,
  `lms_forum_reply`, `lms_notification` đã được lưu.
- Việc verify phải chứng minh được topic, reply, best-answer, state resolved, notification,
  và khả năng hiển thị ở index anonymous.

## Round-2 QA evidence
Đã verify toàn bộ flow với user thật: danh sách `/forum` anonymous → `maria.santos` tạo topic 44 (`/forum/new`, wrapper sản phẩm, category General Discussion) → `julia.nowak` reply (reply 169) → owner đánh dấu best answer (`field_best_answer=1`, topic `field_resolved=1`) → anonymous thấy post đầu tiên + teaser "Join the Discussion" (thread reply bị ẩn) → topic resolved mới xuất hiện trên danh sách `/forum` anonymous mà không cần rebuild cache thủ công. Đã fix `WF-008-001` (teaser "1 replies" → nhận biết số nhiều). Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/008-forum-discussion/`.

## Notifications
Reply mới → topic owner/subscriber; best-answer → người reply (in_app; email/push mới hoàn thiện một phần theo R-005).
