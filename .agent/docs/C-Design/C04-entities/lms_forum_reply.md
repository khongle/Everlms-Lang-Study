# C04 Entity - lms_forum_reply

**Display name:** Forum Reply
**Owning module(s):** [lms_forum](../C05-modules/lms_forum.md)
**Mode:** as-built (Content Entity)

## Purpose
Một reply bên trong một forum topic; hỗ trợ threading (parent), đánh dấu best-answer, và upvote.

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
- N→1 `lms_forum_topic`; self-ref `field_parent` cho thread.

## Validation rules
Nguồn: `web/modules/custom/lms_forum/src/Entity/ForumReply.php`.
- `field_topic`: bắt buộc, entity_reference tới `lms_forum_topic`.
- `field_body`: bắt buộc, `text_long` (formatted).
- `field_parent`: entity_reference self tới `lms_forum_reply` (threading), không bắt buộc.
- `field_best_answer`: boolean, mặc định FALSE; `field_upvotes`: integer, mặc định 0.

## Data invariants
- `field_parent` cho phép threading nhiều cấp qua self-reference, nhưng **không có giới hạn độ sâu (depth limit)** nào trong code — có thể tạo chuỗi reply lồng nhau vô hạn (gap as-built, rủi ro UI khi hiển thị thread quá sâu).
- `field_parent` phải cùng `field_topic` với reply con để thread hợp lệ, nhưng ràng buộc chéo này **không được enforce** ở entity/form level (gap as-built).

## Calculations & derived values
- Xác nhận trong `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php`: khi một reply mới được submit, form tăng `lms_forum_topic.field_replies_count` thêm 1 (`$count = (int) $topic->get('field_replies_count')->value + 1; $topic->set('field_replies_count', $count);`) và set `lms_forum_topic.field_last_reply_date = time()`. Đây là cập nhật ghi ngược sang entity cha (`lms_forum_topic`), không phải trường tính toán trên chính `lms_forum_reply`.
- `field_upvotes`/`field_best_answer` trên reply này là giá trị cập nhật thủ công qua tương tác user/moderator (vote, đánh dấu best answer), không có công thức tự động.

## Editorial/data management
Được viết bởi user; best-answer do topic owner/instructor đặt; cập nhật counter/last-reply của topic. Xem [F-013](../../B-Requirements/B03-features/F-013-community-forum.md).
