# Tài liệu Tính năng Notification

Tài liệu này tổng hợp toàn bộ các tính năng UI/UX và hệ thống đã được xây dựng hoàn thiện cho tính năng Notification của dự án EverLMS.

## 1. Notification Bell Dropdown (Quick View)
- **Vị trí**: Thanh điều hướng trên cùng (Header), ở khu vực User Menu.
- **Tính năng**:
  - Cập nhật số lượng thông báo chưa đọc (Unread badge màu đỏ thắm) trực tiếp.
  - Khi hover chuột (di chuột) vào icon chuông sẽ hiển thị menu thả xuống (Dropdown) chứa tối đa 5 thông báo mới nhất.
  - Mỗi thông báo được hiển thị sắc nét với tiêu đề, nội dung tóm tắt, icon đại diện (tương ứng với loại sự kiện như Enrollment, Quiz, Forum...) và thời gian nhận.
  - Thiết kế Dark Mode bóng bẩy với animation thả xuống mượt mà.
  - Có nút "View all notifications" ở cuối để dễ dàng điều hướng về trang quản lý tổng.

## 2. Trang Danh sách Thông báo (`/notifications`)
- **Đường dẫn**: `/notifications`
- **Tính năng**:
  - Tái cấu trúc lại giao diện danh sách hiển thị với hệ sinh thái Dark Theme.
  - **Hệ thống Lọc (Filter Tabs)**: Bộ nút phân loại trực quan cho phép người học xem thông báo theo từng nhóm cụ thể (Tất cả, System Announcements, Forum Replies, Enrollments, Quiz Results, ...).
  - **Phân trang (Pagination)**: Hàng loạt các thông báo được chia trang nhỏ gọn (15 item/trang) bằng chuẩn kỹ thuật Pager của Drupal, không còn lo quá tải màn hình.
  - Nút chuyển hướng thông minh tới bài viết gốc (Topic gốc, Bài tập trắc nghiệm gốc...).

## 3. Tùy chọn Nhận Thông Báo (Settings Modal)
- **Truy cập**: Nút "Settings" (Icon Sliders) ở phần đầu của trang `/notifications`.
- **Tính năng**:
  - Giao diện thiết lập chuyển đổi hoàn toàn sang **Modal Popup** hiện đại, thay vì phải nhảy sang link trang settings kiểu cũ.
  - Cho phép người dùng bật/tắt nhận thông báo (In-App hoặc Email) ứng với 9-10 nhóm tác vụ chính của lớp học.
  - **Giao diện Modal Dark Mode**: Đề đè toàn bộ UI Dialog trắng tinh mặc định của Drupal, nhuộm đen chuẩn chỉ từ background, đường viền, nút Close dạng font-awesome.
  - **AJAX Tự đóng (Seamless Save)**: Bấm "Save Preferences" sẽ âm thầm gọi qua AJAX, báo thành công chặn redirect và tự động ĐÓNG modal lại. Thêm nút "Cancel" tiện ích.

## 4. Kiến trúc Hệ thống Ngầm (Backend System)
- **Admin Broadcast**: Route `/admin/lms/notifications/broadcast` giúp Admin tạo các thông điệp chung và gửi hàng loạt cho toàn bộ học sinh thông qua Batch Process chống sập server.
- **Auto Cleanup**: Hệ thống thiết lập Cron tự động quyét các nội dung đã đọc (Read = True) lâu hơn 90 ngày và tự động hủy, chống lạm phát bảng database log.
- **Event-Driven Architect**: Được thiết kế lõi để bắt sóng các module như Forum (trả lời topic sẽ bắn thông báo), Quiz (thi đậu thông báo), Course (Enroll khóa học thành công).
