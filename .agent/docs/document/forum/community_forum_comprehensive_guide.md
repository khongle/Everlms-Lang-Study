# Comprehensive Guide: Community (Forum) Module
*Last Updated: March 2026*

## 1. Tổng quan (Overview)
Module `lms_forum` (tên hiển thị trên giao diện là **Community**) là trung tâm thảo luận nội bộ dành cho người học và giảng viên trên hệ thống EverLMS, cho phép trao đổi kiến thức, giải đáp thắc mắc và tương tác học thuật.

Giao diện (UI) đã được thiết kế lại theo hướng **Seamless Flat-Chat Thread** (Luồng trò chuyện phẳng hiện đại) kết hợp chức năng **Quote Reply** (Trích dẫn trả lời) làm nổi bật tính kết nối mạch lạc giữa các bình luận. 

## 2. Cấu trúc Thực thể (Entities Structure)
Module hoạt động dựa trên 2 custom entities chính, thay vì Node tiêu chuẩn của Drupal, giúp tối ưu hóa ranh giới quản lý.

### 2.1. Thực thể Chủ đề (`ForumTopic`)
Đại diện cho một câu hỏi hoặc bài đăng thảo luận gốc.
- `field_title`: Tiêu đề chủ đề.
- `field_body`: Nội dung chuẩn Rich Text (CKEditor).
- `field_category`: Tham chiếu đến Taxonomy `forum_category`.
- `field_course`: Tham chiếu (Tùy chọn) đến khóa học nếu thảo luận bắt nguồn từ 1 khóa học.
- `field_views`: Số lượt xem (tăng tự động qua Event/Controller).
- `field_replies_count`: Tự động cộng/trừ số lượng Comments.
- `field_last_reply_date`: Thời gian tạo phản hồi cuối cùng để sắp xếp Topic mới nổi.
- `field_resolved`: Đánh dấu `TRUE` khi chủ đề đã có "Best Answer" (đóng bình luận).

### 2.2. Thực thể Phản hồi (`ForumReply`)
Đại diện cho các bình luận trả lời cho một `ForumTopic`.
- `field_topic_id`: Tham chiếu ngược lại bài gốc.
- `field_body`: Nội dung trả lời (Rich Text).
- `field_is_answer`: Đánh dấu `TRUE` nếu được chọn là câu trả lời xuất sắc (Best Answer).

## 3. Chức năng hệ thống (System Features)
- **Tạo & Hiển thị Topic:** Danh sách Community ở trang `/forum`. Click vào bài để xem toàn bộ luồng. Hiển thị Lượt xem, Số phản hồi và Thời điểm cập nhật cuối bằng hàm `time_ago`.
- **Trả lời (Quick Reply):** Khung trả lời nằm ngay cuối cùng (Bottom) của luồng theo phong cách "Nhắn tin" (Chat Box). Form Reply được nhúng trực tiếp bằng EntityForm API.
- **Auto-Quote (Trích dẫn siêu tốc):** Sử dụng Javascript thuần túy. Bấm `Reply` ở một comment bất kỳ, hệ thống sẽ chép text nội dung và tên tác giả, bọc trong thẻ `<blockquote>`, ném thẳng xuống editor dưới cùng và cuộn trang xuống.
- **Mark Best Answer (Câu trả lời hay nhất):** Tác giả hoặc Quản trị viên khi ấn nút ✔️ (Check mark) sẽ khóa Topic (Resolved), highlight Reply đó lên đầu với viền Gradient xanh lá bọc ngoài.
- **Edit / Delete Reply AJAX:** Cho phép chỉnh sửa nội dung hoặc xóa bài thông qua `Drupal.dialog.ajax` (Popup Modal) không cần tải lại trang (No Page Reload).

## 4. Thiết kế Giao diện (UI/UX Architecture)
- Quên đi khái niệm *Cards rời rạc*. Toàn bộ Topic và các Reply nằm trong một cấu trúc phẳng bọc chung.
- Khớp phong cách **Dark Theme Morden** của hệ thống tổng.
- **Pills Action Menu:** Tích hợp nút `Reply/Like/Share` thành các mảng Pills ẩn hiện ở footer cho Topic.
- **Hover Micro-Actions:** Nút Edit/Delete ở reply thu nhỏ thành các Icons siêu gọn, nằm cuối dòng thời gian và chỉ **Sáng lên (100% opacity) khi Hover**.
- **Blockquote Overrides:** Cấu trúc Trích dẫn được bảo vệ mạnh mẽ khỏi Drupal HTML Filter bằng Global CSS: `border-left: 4px solid #667eea; padding-left: 1rem; ...`
- **Teaser Mode cho Guest:** Khách chưa đăng nhập (Anonymous User) truy cập vào Topic sẽ chỉ đọc được câu hỏi bài gốc (Main Post) và bị che khuất toàn bộ Replies bên dưới. Thay vào đó hiển thị banner CTA mồi nhử kêu gọi đăng nhập để tiếp tục tham gia.

## 5. Danh mục định tuyến (Routing Dictionary)
Một số định tuyến cốt lõi đang xử lý:
- `lms_forum.index` (`/forum`): Hiển thị hometap Community.
- `entity.lms_forum_topic.canonical` (`/forum/{forum_topic}`): Đọc chi tiết 1 chủ đề.
- `lms_forum.new_topic` (`/forum/new`): Tạo chủ đề.
- `lms_forum.reply` (`/forum/{forum_topic}/reply`): Bắn hàm xử lý Form trả lời.
- `lms_forum.mark_best_answer` (`/forum/{forum_topic}/best-answer/{reply}`): Xử lý Backend nút chọn Checkmark.
- `lms_forum.edit_reply` và `lms_forum.delete_reply`: Controller nạp Form Entity dưới chế độ AJAX Modal.

## 6. Hướng dẫn Phát triển & Xử lý rắc rối (Troubleshooting)
1. **Thay đổi CSS trong luồng Chat không ăn?**  
   Bạn phải xóa cache liên tục thông qua lệnh `ddev drush cr`. Đặc biệt các thẻ tag `<blockquote>` dễ bị bộ lọc Basic HTML của Drupal lược bỏ class hoặc style -> Buộc phải style đè thông qua CSS cấp module hoặc cấp Theme toàn cục.

2. **Nút "Quote và Quote Editor" nhấn không trượt?**  
   Tìm kiếm khối mã JS nằm tại cuối file `lms-forum-topic.html.twig`. Nếu CKEditor load chậm hơn Javascript, đoạn lệnh `CKEDITOR.instances` có thể báo null. Cần wrap bằng `window.onload` hoặc listener tương đương.

3. **Chỉnh sửa/Xóa Comment nhưng trình duyệt không mất đi?**  
   Check tab *Network* của Browser F12. Đảm bảo form trả về *AjaxResponse* đi kèm `RemoveCommand` hoặc `ReplaceCommand` chính xác ID `forum-reply-{{ id }}` có trên giao diện.

---
**Tài liệu này bao quát toàn bộ bộ Core tính năng của Community (LMS Forum) của EverLMS do Antigravity biên soạn.**
