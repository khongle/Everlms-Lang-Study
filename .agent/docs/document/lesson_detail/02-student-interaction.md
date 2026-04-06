# Tương tác của Sinh Viên (Student Interactions)

Trong trang Chi tiết Bài học (Lesson Detail), sinh viên tương tác với hệ thống qua các cơ chế sau:

## 1. Nút "Mark Complete" (Đánh dấu Hoàn Thành)
- Nằm ở trên cùng bên phải của bài học.
- **Tính năng:** Khi sinh viên xem xong video hoặc đọc xong bài viết, họ sẽ bấm nút **"Mark Complete"**.
- **Cơ chế Backend:** Nút bấm gọi API AJAX (POST `/api/lms/progress/mark-complete`) truyền lên `course_id` và `lesson_id`. Backend sẽ tạo/cập nhật Entity `lms_user_lesson` với trạng thái `status = 'completed'`.
- **Cơ chế Frontend:** Ngay khi API trả về thành công:
  1. Nút bấm đổi trạng thái thành dòng chữ xanh *"Completed!"*.
  2. Icon trạng thái bên thanh danh sách bài học (sidebar) chuyển tức thì thành màu xanh lá.
  3. Hệ thống sẽ **tự động chuyển hướng (Redirect)** sang bài học kế tiếp (nếu có), hoặc trở về trang khoá học (nếu là bài cuối cùng). Tính năng này mang lại trải nghiệm dòng chảy học tập (Flow) rất mượt.

## 2. Tracking Thời gian Xem Video
- **Backend:** Cơ sở dữ liệu của `lms_user_lesson` hiện đã có sẵn biến `field_video_position` (ghi nhận số giây hiện tại sinh viên đang xem tới đâu).
- **Trạng thái:** Template `lms-lesson-view.html.twig` đã được truyền biến `video_position`.
- **Khả năng Mở rộng:** Trong tương lai, chúng ta có thể viết thêm một đoạn Javascript để: 
  1. Set thời điểm bắt đầu video bằng `video_position`.
  2. Bắn API theo dõi (Ping) cứ mỗi 10 giây một lần để lưu trữ lại vị trí đang xem, giúp người học xem tiếp chính xác đoạn video đang dở dang.

## 3. Điều hướng Bài học (Curriculum Navigation)
- Thanh menu Sidebar bên tay phải cung cấp khả năng nhảy tắt (Jump) qua các bài học khác.
- Sidebar hiển thị cả tiến độ học tập (dấu tick checkmark xanh lá cho bài đã học, icon chấm xám cho bài chưa tới).
- Phía góc trên bên trái màn hình có nút **"Back to Course"** quay ngược về trang Intro khóa học để xem lại tổng quát.
