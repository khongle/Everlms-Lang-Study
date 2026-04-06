# Course, Chapter, & Lesson Logic for Manager

Vai trò **Manager** tập trung chủ yếu vào khâu Vận hành nội dung (Content Operations) và Quản lý Người học, với phần lớn các quyền tác động mạnh mẽ đến các khóa học nhưng không can thiệp sâu vào System Settings.

## 1. Quyền hạn chung (CRUD Nội dung)
- **Tạo mới & Chỉnh sửa (Create & Edit)**: Quản lý và đóng góp nội dung trên toàn hệ thống. Có quyền tạo Course, Chapter, Lesson và sửa các khóa học của bất kỳ ai.
- **Xóa (Delete)**: Có thẩm quyền xóa Course, Chapter, Lesson (Quyền này Instructor không có).
- **Xuất bản (Publish)**: Phê duyệt từ Draft lên Publish các ấn phẩm, khóa học sau khi Instructor đệ trình để hiển thị khóa học ra ngoài frontend.

## 2. Quản lý Tổ chức Course
- Phụ trách quản lý và cập nhật `course_category`, cấu hình các khóa học Bestseller/Featured.
- Cấu hình mức giá (`field_price_type`: free, paid, membership) cho các Course.
- Khởi tạo, gán các chứng chỉ (Certificate Template) vào cuối khóa học (Final Quiz).

## 3. Cấp quyền truy cập (Enrollment) & Progress
- Xử lý các nghiệp vụ hoàn tiền (Refunds) hoặc xử lý đơn hàng lỗi, dẫn đến việc phải can thiệp trạng thái Enroll của 1 User đối với 1 Course.
- Cấp quyền (Manual Assign) Course cho học viên thủ công từ phía backend thay vì mua tự động. 
- Xem chi tiết bảng xếp hạng, Leaderboard và báo cáo tổng quan số lượng học viên, tiến độ (Progress) trên tất cả các khóa học thuộc diện mình quản lý.

## 4. Trải nghiệm Edit UI cho Manager
- **Form Edit Riêng Biệt (`manager_edit`)**: Để tối ưu trải nghiệm và tránh URL mang tính hệ thống (`/admin-edit`), Manager có giao diện chỉnh sửa khóa học riêng biệt thông qua đường dẫn `/manage/courses/{course}/edit`. 
- **Layout Chuyên Nghiệp (2 Cột)**: 
  - Form được tích hợp trực tiếp vào **LMS Theme (Frontend)** với công nghệ Bootstrap 5.
  - Các input được tự động gán class chuẩn như `.form-control`, `.form-select`.
  - Bố cục được tối ưu thành **2 cột**: Cột trái (phần lớn) là Nội dung mô tả và danh sách bài học. Cột phải (nhỏ gọn) dùng cho Cài đặt trạng thái, Học phí, Phân loại, và Hình ảnh.
- **Phân quyền Text Format**: Manager được cung cấp đầy đủ quyền sử dụng trình soạn thảo văn bản (`full_html`, `basic_html`) trên các trường mô tả để trực tiếp làm phong phú nội dung mà không bị lỗi vô hiệu hóa (Disabled field).
