# C04 Entity - lms_note

**Display name:** Note
**Owning module(s):** [lms_note](../C05-modules/lms_note.md)
**Mode:** as-built (Content Entity)

## Purpose
Một ghi chú riêng tư của learner gắn với một lesson/course, có thể gắn timestamp tới một vị trí video (tùy chọn).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | owner (private) |
| field_lesson | ref → lms_lesson |
| field_course | ref → lms_course |
| field_body | formatted note text |
| field_video_timestamp | linked video position |
| created, changed | timestamps |

## Relationships
- N→1 `user`, `lms_lesson`, `lms_course`.

## Validation rules
Nguồn: `web/modules/custom/lms_note/src/Entity/Note.php`.
- `field_course`: bắt buộc, entity_reference tới `lms_course`.
- `field_lesson`: bắt buộc, entity_reference tới `lms_lesson`.
- `field_body`: bắt buộc, `text_long` (formatted).
- `field_video_timestamp`: integer, mặc định 0 (giây), mô tả "Seconds into the video" — không có ràng buộc non-negative trong code.

## Data invariants
- `field_lesson` phải thuộc về `field_course` được chọn cùng note (quan hệ chéo ngầm định), nhưng **không có validate nào enforce** rằng lesson thực sự nằm trong course đó — có thể lưu một cặp course/lesson không liên quan nhau (gap as-built).
- `field_video_timestamp` chỉ có ý nghĩa khi lesson liên kết là `field_type = video`; với lesson dạng khác giá trị này vô nghĩa nhưng vẫn được lưu (mặc định 0) — không có điều kiện ẩn/bỏ qua theo `lms_lesson.field_type`.

## Calculations & derived values
- Không có trường nào được tính toán tự động trên `lms_note` — toàn bộ nội dung và timestamp do learner nhập tay khi ghi chú.

## Editorial/data management
Thuộc sở hữu của user và riêng tư (chỉ tác giả mới đọc/sửa). Được hiển thị trong player + dashboard. Xem [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md).
