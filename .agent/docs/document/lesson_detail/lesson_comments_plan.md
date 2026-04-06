# Kế hoạch Triển khai: Inline Comment Replies (Trả lời bình luận trực tiếp)

## Phân tích vấn đề
Người dùng báo cáo khi bấm nút "Reply" (Trả lời) trên một bình luận trong trang Lesson Details, hệ thống tự động chuyển hướng (redirect) sang một trang `/comment/reply/...` trắng trơn, tách biệt hoàn toàn khỏi ngữ cảnh bài giảng (video, tab content).
Đây là hành vi mặc định của Drupal Core khi chưa được can thiệp bằng Javascript (AJAX).

## Giải pháp Đề xuất (Inline Reply Form)
Thay vì cài đặt thêm các module phức tạp (như `ajax_comments`) dễ gây xung đột, chúng ta có thể áp dụng chiến thuật **"Form Relocation" (Di dời Form)** bằng Vanilla Javascript kết hợp một chút sửa đổi nhỏ trong hook. Giải pháp này nhẹ, mượt và chuyên nghiệp giống 100% Facebook hay Udemy.

## Roadmap Kỹ thuật chi tiết

### 1. Can thiệp Backend (`lms_progress.module`)
Mặc định Form tạo mới bình luận (`comment_lesson_comment_form`) không cho phép truyền `pid` (Parent ID - ID của bình luận cha) qua AJAX tùy ý vì lý do bảo mật. Ta sẽ lách luật bằng cách:
*   [MODIFY] Thêm một input ẩn (`<input type="hidden" name="lms_reply_pid" value="" />`) vào form trong hàm `lms_progress_form_comment_lesson_comment_form_alter`.
*   [NEW] Viết một Custom Submit Handler (`lms_progress_set_comment_parent`) chạy **trước** hàm save chính của Drupal. Hàm này sẽ tóm lấy giá trị của `lms_reply_pid` (nếu có) và set trực tiếp vào thuộc tính `pid` của Entity Comment. Việc này giúp biến một bình luận gốc thành một "Reply" một cách hoàn hảo ở database.

### 2. Can thiệp Frontend (`lms-lesson-view.html.twig`)
Viết một đoạn mã mã Vanilla JavaScript để xử lý trải nghiệm UX chớp nhoáng:
*   **Bắt sự kiện Reply:** Chặn hành vi mở tab mới/chuyển trang của tất cả các thẻ `<a>` có chứa chữ "Reply".
*   **Chiết xuất ID:** Tách lấy ID của bình luận cha từ đường dẫn URL (ví dụ `/comment/reply/.../1` -> Lấy số `1`).
*   **Di dời DOM (DOM Relocation):**
    *   Nhấc nguyên cụm Form Bình Luận gốc (`#lesson-comment-form-ajax-wrapper`) ở cuối trang đang có sẵn.
    *   Thả cái Form đó vào ngay bên dưới bình luận mà người dùng vừa ấn Reply.
*   **Tiêm ID:** Gắn con số `1` (ID) vào cái `<input type="hidden" name="lms_reply_pid">` vừa tạo ở bước 1.
*   **Đổi giao diện phụ:**
    *   Tự động thêm một nút "Cancel" (Hủy).
    *   Chuyển chữ "Add new comment" thành "Reply to [Tên người đó]".
    *   (Tùy chọn) Focus con trỏ chuột vào ô text luôn để gõ ngay lập tức.
*   **Hủy (Cancel):** Nếu ấn "Hủy", Form sẽ được nhấc quay trở lại vị trí gốc dưới cùng, reset `lms_reply_pid` về trống, chuyển lại chữ "Add new comment".

### Lợi ích của phương pháp này:
1. **Zero Full-reload**: Mọi thao tác hoàn toàn ở Client-side, bấm Reply là form hiện lên tức thì ở ngay vị trí đó.
2. **Re-use (Tái sử dụng) AJAX đỉnh cao**: Form Reply cũng dùng chung 100% logic AJAX đang tiết kiệm công sức maintain. Khi ấn Save, bình luận tự động thọc vào DB và load lại DOM mà không cần chớp trang.
3. **Tuyệt đối an toàn bảo mật**: Drupal Form API vẫn bảo vệ toàn vẹn CSRF token.
