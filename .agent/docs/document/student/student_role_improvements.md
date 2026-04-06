# Đề xuất cải thiện vai trò Sinh viên (Student Role Improvements)

Dựa trên việc xem xét kỹ các tài liệu hiện tại trong hệ thống (đặc biệt là các tính năng thiết yếu và gamification), phần giao diện Dashboard cơ bản đã đáp ứng đúng định hướng (UI Revamp đã có thumbnail, progress bar, layout lưới). 

Để tiếp tục nâng tầm trải nghiệm của Sinh viên (Learner), dưới đây là 3 tính năng hiện đại cần được ưu tiên xem xét để phát triển:

## 1. Ghi chú Học tập (Student Notes)
Hỗ trợ sinh viên ghi chú lại các ý quan trọng trực tiếp ngay trên màn hình bài giảng (Lesson view), thay vì phải dùng ứng dụng bên ngoài. 

### Chi tiết chức năng:
- **Ngay trong bài giảng**: Bổ sung một Side-panel hoặc Tab "Ghi chú" bên cạnh nội dung video/text.
- **Trình soạn thảo nhanh**: Có các định dạng văn bản cơ bản (Bold, Italic, List).
- **Auto-save**: Tự động lưu trữ nội dung ghi chú lên database trong lúc sinh viên soạn thảo (sau khoảng 2s ngừng gõ).
- **Trang quản lý cá nhân**: Trang `/my-notes` trong User Portal hiển thị tất cả ghi chú, phân loại theo khóa học/bài học để sinh viên dễ dàng tìm kiếm.
- **Tính năng mở rộng**: Cho phép xuất file PDF/TXT toàn bộ ghi chú của một khóa học.

### Mô hình dữ liệu:
- **Entity**: `lms_note` với các trường `user_id`, `course_id`, `lesson_id`, `text_content`, `created_at`.

---

## 2. Danh sách Yêu thích (Wishlist)
Khuyến khích sinh viên lưu lại các khoá học quan tâm nhưng chưa có ý định (hoặc chưa đủ điều kiện) đăng ký ngay lập tức. Đây là một tính năng thiết yếu để cải thiện tỷ lệ chuyển đổi.

### Chi tiết chức năng:
- **Nút "Thả tim" (Add to Wishlist)**: Xuất hiện trên tất cả các thẻ khoá học (Course Cards) và trên trang Chi tiết khoá học.
- **Header Icon**: Hiển thị nhanh số lượng khoá học trong Wishlist trên thanh điều hướng.
- **Trang quản lý cá nhân**: Trang `/my-wishlist` tổng hợp các khóa học đã đánh dấu.

### Mô hình dữ liệu:
- **Entity**: `lms_wishlist` map giữa `user_id` và `course_id`.

---

## 3. Chế độ Học Thử (Free Course Previews)
Giúp người dùng vãng lai hoặc sinh viên chưa đăng ký khóa học có thể xem trước nội dung một vài bài giảng đầu tiên (như Video Trailer, Giới thiệu) nhằm thuyết phục đăng ký học.

### Chi tiết chức năng:
- **Đánh dấu bài học**: Giảng viên/Admin có thể bật cờ "Free Preview" trên từng bài học cụ thể.
- **Mở khóa truy cập**: Hệ thống thay thế icon ổ khóa bằng icon mở khóa (🔓) cho các bài học này trên trang chi tiết khóa.
- **Phân quyền truy cập**: Controller sẽ bỏ qua yêu cầu "must be enrolled" nếu user đang truy cập một lesson có cờ free preview.

### Mô hình dữ liệu:
- **Field bổ sung**: Thêm field `field_is_preview` (Boolean) vào cấu trúc `lms_lesson`.

---
> **Lưu ý:** Vui lòng chọn tính năng anh muốn ưu tiên triển khai trước (ví dụ: Student Notes) để em tiến hành lên kế hoạch sửa code và cập nhật database.
