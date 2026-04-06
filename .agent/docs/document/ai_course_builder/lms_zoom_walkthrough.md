# Tích hợp LMS Zoom - Tài liệu & Hướng dẫn sử dụng

**Module**: `lms_zoom`
**Trạng thái**: Hoàn thành (Giai đoạn 1)
**Phụ thuộc**: `lms_lesson`, `lms_base`

## 1. Tổng quan Kiến trúc Kỹ thuật
Module `lms_zoom` cung cấp khả năng tích hợp liền mạch giữa entity `lms_lesson` của Drupal và Zoom V2 API.

### Các thành phần lõi:
- **`ZoomService.php`**: Xử lý xác thực Server-to-Server OAuth bằng Guzzle. Token được lưu vào `State` API của Drupal để giảm thiểu request gọi ra ngoài. Chịu trách nhiệm thực hiện các thao tác API cốt lõi (`createMeeting`, `deleteMeeting`, `getMeeting`).
- **`ZoomMeetingManager.php`**: Đóng vai trò cầu nối giữa Lesson của Drupal và Zoom API. Khi một lesson có `field_type` = 'zoom' được tạo mới hoặc xóa, nó sẽ tự động kích hoạt API để tạo/xóa phòng họp (meeting) tương ứng.
- **Entity Hooks (`lms_zoom.module`)**: `hook_entity_presave` và `hook_entity_delete` tự động đánh chặn các sự kiện của bài học (Lesson) để đồng bộ thông tin phòng họp Zoom.

### Các thành phần Giao diện (Frontend):
- **`ZoomJoinBlock.php`**: Một block plugin tùy chỉnh được đặt trên trang bài học. Block này tự động đọc ngữ cảnh của lesson hiện tại.
- **`zoom-countdown.js`**: Native JS hiển thị bộ đếm ngược thời gian thực. Nút "Join Class" (Vào lớp) sẽ tự động sáng lên và cho phép bấm ở thời điểm 15 phút trước khi lớp bắt đầu.
- **`ZoomController.php`**: Route `/zoom/join/{meeting_id}` đóng vai trò tự động lấy đường dẫn `join_url` thật sự (giúp tránh lỗi link hết hạn) và chuyển hướng học viên thẳng vào app Zoom.

---

## 2. Hướng dẫn dành cho Quản trị viên (Cấu hình Zoom)

Để kích hoạt Server-to-Server OAuth cho Zoom:

1. Truy cập [Zoom App Marketplace](https://marketplace.zoom.us/).
2. Tạo một ứng dụng **Server-to-Server OAuth**.
3. Cấp quyền (Scopes) cho: `meeting:write:admin`, `meeting:read:admin`, `recording:read:admin`.
4. Copy các mã: **Account ID**, **Client ID**, và **Client Secret**.
5. Đăng nhập vào EverLMS. Điều hướng đến: `Configuration > LMS Configs > Zoom Integration` (`/admin/config/lms/zoom`).
6. Dán các thông tin cấu hình vào form và nhấn Lưu.

---

## 3. Hướng dẫn dành cho Giảng viên (Tạo Lớp Zoom)

1. Trượt đến một Khóa học bất kỳ và ấn Thêm bài học mới (Add Lesson).
2. Ở phần **Lesson Type**, chọn: `Zoom Live`.
3. Cài đặt **Thời lượng (Duration)** tính bằng phút.
4. Cài đặt **Zoom Schedule** (Ngày và giờ diễn ra lớp học).
5. Bấm **Lưu (Save)**.
   *Hệ thống sẽ tự động gọi sang Zoom, tạo phòng họp và gắn Meeting ID vào bài học một cách hoàn toàn tự động và ẩn danh.*

---

## 4. Hướng dẫn dành cho Học viên (Tham gia Lớp học)

1. Học viên truy cập vào bài học Zoom của khóa học.
2. Một bộ đếm ngược tuyệt đẹp sẽ hiển thị thời gian còn lại trước khi lớp bắt đầu.
3. Khi thời gian chạm mốc **15 phút** trước giờ học, nút **"Join Class Now"** sẽ đổi màu và có thể click.
4. Bấm vào nút này để hệ thống tự động chuyển hướng và khởi động phòng họp Zoom trực tiếp.
