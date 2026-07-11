# C04 Entity - lms_forum_topic

**Display name:** Forum Topic
**Owning module(s):** [lms_forum](../C05-modules/lms_forum.md)
**Mode:** as-built (Content Entity)

## Purpose
Một discussion thread, có thể scope theo course (tùy chọn) và được phân loại; mang theo engagement counter và moderation flag.

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

## Validation rules
Nguồn: `web/modules/custom/lms_forum/src/Entity/ForumTopic.php`.
- `name`: bắt buộc, tối đa 255 ký tự.
- `field_replies_count`, `field_views`: integer, mặc định 0 (bộ đếm, không nhập tay).
- `field_pinned`, `field_locked`, `field_resolved`: boolean, mặc định FALSE.
- `field_course`: entity_reference tới `lms_course`, không bắt buộc (topic có thể không scope theo course nào).

## Data invariants
- `field_locked = TRUE` kỳ vọng ngăn learner gửi reply mới (theo mô tả "moderation flag"), nhưng cần xác nhận thêm: `ForumReplyForm` không được đọc trong phạm vi grep hiện tại để xác nhận có kiểm tra `field_locked` trước khi cho submit hay không — nếu không có guard tương ứng thì đây là gap as-built.
- `field_last_reply_date` chỉ có ý nghĩa khi ≥1 reply tồn tại; giá trị mặc định 0 (epoch) khi topic mới tạo, dùng để sort "hoạt động gần nhất".

## Calculations & derived values
- Xác nhận trong `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php`: mỗi khi có reply mới, `field_replies_count` += 1 và `field_last_reply_date` = `time()` hiện tại (ghi trực tiếp lên topic entity từ form của reply con).
- Xác nhận trong `web/modules/custom/lms_forum/src/Controller/ForumController.php`: `field_views` += 1 mỗi lần topic được xem (`$views = (int) $lms_forum_topic->get('field_views')->value + 1;`).
- `field_pinned`/`field_resolved` là cờ moderation đặt thủ công bởi staff, không có công thức tự động.

## Editorial/data management
Được viết bởi learner/instructor; được staff kiểm duyệt (pin/lock/resolve). Xem [F-013](../../B-Requirements/B03-features/F-013-community-forum.md), [WF-008](../C12-workflows/WF-008-forum-discussion.md).
