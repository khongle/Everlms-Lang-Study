# Đề xuất cải thiện tính năng Sinh viên (Ghi chú & Yêu thích)

Mục tiêu: Nâng cao trải nghiệm học tập của sinh viên thông qua hai tính năng chính: **Ghi chú Học tập (Student Notes)** và **Danh sách Yêu thích (Wishlist)**. (Lưu ý: Tính năng "Học thử - Free Preview" sẽ được tạm hoãn chờ cho đến khi hoàn thiện nâng cấp màn hình Lesson Detail).

> **Cập nhật quan trọng:** Sau khi rà soát thư mục `01-requirement` và source code, hệ thống **đã có sẵn** khai báo cho custom module `lms_note` và `lms_wishlist`. Do đó chúng ta sẽ kế thừa chuẩn xác những gì đã có để tránh dư thừa.
> **Trạng thái:** ✅ Tính năng Ghi chú Học tập và Danh sách Yêu thích đã được code và triển khai thành công 100% bao gồm cả Frontend AJAX UI mượt mà không dính Cache và Backend Controller/APIs.

## 1. Tính năng Ghi chú Học tập (Student Notes)

### Backend Data Model
Sử dụng Custom Entity `lms_note` hiện có trong source code. Tuy nhiên, căn cứ theo tài liệu `07-communication-entities.md`, module hiện tại đang thiếu trường `field_course`.
- Cần bổ sung thêm `field_course` (tham chiếu đến khóa học) vào class `Note.php` để phục vụ tối ưu truy vấn danh sách (denormalized). Các trường khác như `uid`, `field_lesson`, `field_video_timestamp`, `field_body` đã đầy đủ.

### REST APIs
- **POST `/api/lms/note/save`**: API nhận nội dung ghi chú và lưu trữ thủ công khi sinh viên bấm Save.
- **POST `/api/lms/note/delete`**: API xóa ghi chú bằng AJAX.
- **GET `/api/lms/note/get`**: API lấy nội dung ghi chú tự động (Bypassing cache).

### Giao diện Bài học (Lesson View)
- Bổ sung UI "Ghi chú" bên dưới video/bài giảng trong màn hình `lms_lesson_view`. Tích hợp thông báo UX, chặn mất dữ liệu (Unsaved changes warning), và nút Save/Delete tĩnh dễ dàng thao tác.
- **Trang Quản lý `/my-notes`**: Trang render toàn bộ ghi chú của tài khoản đang đăng nhập, được gom nhóm theo khóa học mạch lạc.

---

## 2. Tính năng Danh sách Yêu thích (Wishlist)

### Backend Data Model
Sử dụng Custom Entity `lms_wishlist` hiện có. Cấu trúc đã tuân thủ chuẩn chỉnh tài liệu `04-commerce-entities.md` (bao gồm `uid` và `field_course`), không cần tạo mới cấu trúc bảng.

### REST APIs
- **POST `/api/lms/wishlist/toggle`**: Cho phép toggle (thêm vào/xóa khỏi) Wishlist bằng một API duy nhất. API sẽ tự check DB và handle trạng thái tương ứng.

### Giao diện Course Cards & Header
- **Card Khóa Học**: Bổ sung icon ❤️ (Trái tim) trên thumbnail với JS AJAX (Không rỗng = Chưa lưu, Tô đỏ = Đã lưu).
- **Trang Quản lý `/my-wishlist`**: Trang hiển thị danh sách các khóa học sinh viên đã thêm.
- **Header**: Bổ sung bộ đếm số lượng khóa học trong Wishlist lên phần menu header.
