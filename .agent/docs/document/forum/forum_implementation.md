# Kế hoạch Triển khai Tính năng Diễn đàn (Forum)

## Mô tả Mục tiêu
Triển khai hoàn chỉnh tính năng Diễn đàn (Forum) bám sát các tài liệu yêu cầu trong thư mục `docs` (đặc biệt là `08-communication.md`, `07-communication-entities.md` và `08-forum-flow.md`). Module `lms_forum` hiện tại cần được cập nhật cấu trúc thực thể (Entities) cho khớp với thiết kế kiến trúc, sau đó tạo Form, kết nối giao diện và định tuyến.

## Các Thay đổi Đề xuất

### 1. Cập nhật Thực thể (Entities) theo thiết kế Architecture
Hiện tại `ForumTopic` và `ForumReply` trong codebase đang thiếu một số trường so với tài liệu `07-communication-entities.md`.

#### [CHỈNH SỬA] `web/modules/custom/lms_forum/src/Entity/ForumTopic.php`
- Thêm `field_category`: `entity_reference` tới vocabulary `forum_category`.
- Thêm `field_last_reply_date`: `timestamp`.
- Thêm `field_views`: `integer`, mặc định 0.
- Thêm `field_resolved`: `boolean`, mặc định `FALSE`.

#### [CHỈNH SỬA] `web/modules/custom/lms_forum/src/Entity/ForumReply.php`
- Đổi tên `field_best_answer` thành `field_is_answer` (tùy chọn để khớp 100% doc, hoặc giữ nguyên nhưng phải map chuẩn logic). Theo plan sẽ giữ nguyên tên db cũ `field_best_answer` hoặc đổi thành `field_is_answer` theo đúng doc. Sẽ thêm trường `field_upvotes` (`integer`).

#### [MỚI] Thiết lập Taxonomy `forum_category`
- Cần tạo (thủ công hoặc qua config/code_update) vocabulary `forum_category` để dùng cho `field_category`.

### 2. Form & Định tuyến (Routing)
#### [MỚI] `web/modules/custom/lms_forum/src/Form/ForumTopicForm.php`
- Kế thừa `ContentEntityForm` cho `ForumTopic`.
- Hiển thị các trường: Tiêu đề, Nội dung, Danh mục (`field_category`), và Khóa học (`field_course` - ẩn nếu đang tạo trong context khóa học).
- Xử lý lưu và chuyển hướng.

#### [MỚI] `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php`
- Kế thừa `ContentEntityForm` cho `ForumReply`.
- Trường Nội dung (Body).
- Khi submit: lưu reply, cập nhật `field_last_reply_date` và `field_replies_count` cho Topic.
- **Tích hợp Notification**: Gửi thông báo loại `forum_reply` qua `lms_notification` tới người tạo Topic.

#### [CHỈNH SỬA] `web/modules/custom/lms_forum/lms_forum.routing.yml`
- Thêm các route: tạo topic mới, tạo reply, và đánh dấu câu trả lời hay nhất.

### 3. Cập nhật Controller
#### [CHỈNH SỬA] `web/modules/custom/lms_forum/src/Controller/ForumController.php`
- `index()`: Nâng cấp truy vấn để tải cả Category, Views, Reply count thực tế.
- `viewTopic()`: Tăng views bằng code khi tải trang. Truyền `ForumReplyForm` vào Twig.
- `markBestAnswer()`: Bật flag best answer cho Reply, đồng thời update `field_resolved = TRUE` cho Topic.

### 4. Tích hợp Giao diện (Templates)
#### [CHỈNH SỬA] `web/modules/custom/lms_forum/templates/lms-forum-index.html.twig`
- Hiển thị nhãn Category, Views và trạng thái Resolved của từng topic.

#### [CHỈNH SỬA] `web/modules/custom/lms_forum/templates/lms-forum-topic.html.twig`
- Thay thế form tĩnh bằng form Drupal (`{{ reply_form }}`).
- Hiển thị nút đánh dấu Best Answer (`is_answer`) và highlight nếu topic đã `resolved`.

## Kế hoạch Kiểm thử (Verification Plan)
1. **Schema Check**: Kiểm tra db update xem các trường mới (category, views, resolved, upvotes) đã được tạo thành công cho các Entity.
2. **Luồng Topic/Reply**: Tạo topic chọn danh mục. Đăng reply và xem `field_last_reply_date` cập nhật, kiểm tra notification sinh ra trong hệ thống.
3. **Luồng Resolved**: Đánh dấu best answer -> Topic chuyển state `resolved`. Thử view xem số page views nhảy.
