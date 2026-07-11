# C04 Entity - lms_rating

**Display name:** Rating / Review
**Owning module(s):** [lms_rating](../C05-modules/lms_rating.md)
**Mode:** as-built (Content Entity)

## Purpose
Star rating của learner + review dạng văn bản tùy chọn cho một course; được tổng hợp để hiển thị trên catalog.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | reviewer |
| field_course | ref → lms_course |
| field_score | star score (1–5) |
| field_review | formatted review text |
| field_approved | moderation flag |
| field_is_featured | featured-review flag |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_course`. Giá trị tổng hợp được cache tại `lms_course.field_rating`.

## Validation rules
Nguồn: `web/modules/custom/lms_rating/src/Entity/Rating.php`.
- `field_course`: bắt buộc, entity_reference tới `lms_course`.
- `field_score`: integer, mặc định 5, mô tả "1-5 stars" nhưng **không có `allowed_values`/range constraint** nào giới hạn thực sự trong khoảng 1-5 (có thể lưu 0, số âm, hoặc >5 nếu form/API không chặn — gap as-built).
- `field_approved`: boolean, mặc định TRUE — nghĩa là review **tự động approved khi tạo**, không có hàng đợi kiểm duyệt bắt buộc mặc định dù mô tả field là "moderation flag".

## Data invariants
- Relationships (phần trên) ghi "Giá trị tổng hợp được cache tại `lms_course.field_rating`", nhưng khi rà soát code (`grep field_rating` trên toàn `web/modules/custom`), **không tìm thấy bất kỳ nơi nào tính trung bình từ các `lms_rating` entity rồi ghi vào `lms_course.field_rating`** — mọi nơi đọc `field_rating` của course chỉ đọc giá trị tĩnh đã lưu (mặc định 5.0), không phải giá trị tổng hợp runtime. Đây là khác biệt giữa mô tả thiết kế và as-built thực tế — cần xác nhận thêm với đội phát triển hoặc coi là gap (rating hiển thị trên course KHÔNG phản ánh review thật).
- Không có ràng buộc "1 user chỉ review 1 lần mỗi course" (unique uid+field_course) được tìm thấy ở entity level.

## Calculations & derived values
- Không tìm thấy service/cron tính trung bình (`AVG`) từ các `lms_rating.field_score` của cùng một course để cập nhật `lms_course.field_rating` — như ghi ở Data invariants, đây là gap as-built giữa thiết kế và triển khai thực tế.
- `field_is_featured` là cờ thủ công do staff chọn hiển thị trên trang chủ, không có logic tự động chọn review "tốt nhất".

## Editorial/data management
Được gửi bởi learner đã enroll; được staff kiểm duyệt (approve/feature). ~79 rating thực tế. Xem [F-014](../../B-Requirements/B03-features/F-014-ratings-and-reviews.md).
