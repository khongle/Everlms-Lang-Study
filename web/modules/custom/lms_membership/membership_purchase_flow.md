# Quy Trình Hoạt Động Của Hệ Thống Khi Mua Membership
Tài liệu này giải thích chi tiết toàn bộ luồng kỹ thuật (Technical Flow) xảy ra hệ thống từ lúc ấn nút **"Complete Purchase"** cho đến khi User chính thức được cấp quyền truy cập khóa học.

---

## 🚀 Giai đoạn 1: Khởi tạo Đơn Hàng (Order Creation)
*File xử lý: `lms_order/src/Form/MembershipCheckoutForm.php`*

1.  **Lấy thông tin**: Hệ thống ghi nhận ID gói Membership (Plan ID), số tiền (Price), phương thức thanh toán (Stripe / PayPal / VietQR) và ID của người dùng (User ID) hiện tại.
2.  **Tạo Entity Đơn hàng (Order)**:
    *   Hệ thống sinh ra một `Order` mới (bảng `lms_order`) với trạng thái ban đầu là `pending` (chờ thanh toán).
    *   Tự động sinh mã đơn hàng ngẫu nhiên (Ví dụ: `MEM-167890123-456`).
3.  **Tạo Hạng mục (Order Item)**:
    *   Tạo một `OrderItem` có loại (Type) là `membership`, lưu trữ ID của Gói Membership mà người dùng đang mua.
    *   Gắn `OrderItem` này vào trong `Order` ở bước 2.

---

## 💳 Giai đoạn 2: Xử Lý Thanh Toán (Payment Processing)
*File xử lý: Call cổng thanh toán qua `PluginManager`*

Dựa vào lựa chọn của người dùng, hệ thống gọi API tương ứng:
*   **Thanh toán Online thành công ngay (Stripe / PayPal)**: Trả về trạng thái `success`.
*   **Thanh toán Chuyển khoản (VietQR / Bank Transfer)**: Trả về trạng thái `pending`, hệ thống sẽ redirect (điều hướng) người dùng sang trang hiển thị mã QR Code để họ tự quét chuyển khoản.

> **Lưu ý quan trọng:** Chỉ khi thanh toán được cấu hình báo về `success`, Đơn hàng (Order) mới được chuyển trạng thái sang `completed`.

---

## ⚙️ Giai đoạn 3: Kích Hoạt Quyền Lợi (Order Update Hook)
*File xử lý: `lms_order/lms_order.module` (hook `lms_order_entity_update`)*

Đây là "Trái tim" của hệ thống tự động hóa. Khi hệ thống phát hiện một Đơn hàng (`Order`) vừa được chuyển từ `pending` sang **`completed`**, nó sẽ làm chuỗi các việc sau:

1.  **Đọc Order Item**: Kiểm tra xem khách mua Khóa học lẻ hay mua Gói Membership. Nếu là Membership ➔ Quét ID gói.
2.  **Tính toán thời gian học**:
    *   Lấy số ngày học (`field_duration_days`) của Gói đó (thường là 30 ngày cho gói Tháng).
    *   Ngày bắt đầu (`start_date`) = *Thời điểm hiện tại*.
    *   Ngày hết hạn (`end_date`) = *Thời điểm hiện tại + 30 ngày*.
3.  **Tạo Hồ sơ Hội viên (`lms_user_membership`)**:
    *   Lưu vào cơ sở dữ liệu một bản ghi cấp quyền cho User ID này.
    *   Trạng thái: **`Active`** (Đang hoạt động).
    *   Thuộc tính: Được bật Auto-Renew (Tự động gia hạn) phục vụ cho Cron Job sau này.

---

## 📧 Giai đoạn 4: Chăm Sóc Khách Hàng (Notifications & Emails)
*Ngay lập tức theo sau Giai đoạn 3 trong `lms_order.module`*

1.  **Gửi Email Xác Nhận**: Hệ thống tự động kích hoạt `hook_mail` để gọi Template `order_completed`, gửi thẳng một Email "Cảm ơn & Xác nhận thanh toán" đến hộp thư của người mua kèm đường link vào học.
2.  **Gửi Thông Báo Kênh Web (In-App)**: Ghi một dòng vào bảng `lms_notification` để quả chuông <i class="fas fa-bell"></i> trên góc phải màn hình của web nhảy số `1` màu đỏ, báo cho user biết Order đã hoàn tất.

---

## 🛡️ Giai đoạn 5: All-Access Pass (Mở Khóa Toàn Bộ Khóa Học)
*File xử lý: `lms_course/src/Controller/CoursePublicController.php`*

Bây giờ quyền truy cập đã được mở khóa tự động dưới dạng **Thẻ VIP Toàn Năng**:
*   Tại trang Khóa học (`/courses`), hệ thống sẽ gọi service `MembershipManager::hasActiveMembership($user_id)`.
*   Nếu kết quả trả về `TRUE` (tức là User có chứa bản ghi `lms_user_membership` đang `Active` và chưa bị quá hạn), hệ thống sẽ **bỏ qua hoàn toàn giá tiền hiện tại của mọi khóa học** (dù là khóa lẻ $80 hay khóa Membership).
*   **Kết quả hiển thị:** Chiếc nút "Enroll Now" (Mua ngay) trước đó sẽ vĩnh viễn biến mất và được hệ thống thay nóng bằng nút màu tím **"Continue Learning"** (Vào học ngay).

---

## 📈 Giai đoạn 6: Nhập Môn Tự Động (Auto-Enroll & Dashboard)
*File xử lý: `lms_progress/src/Controller/UserPortalController.php` (hàm `lessonView`)*

Dù khách hàng VIP có quyền xem 1000 khóa học, bảng Dashboard (`/dashboard`) sẽ KHÔNG bị rác bởi tất cả 1000 khóa đó. Nguyên lý theo dõi tiến trình (Tracking) diễn ra như sau:
1.  Hội viên bấm "Continue Learning" để vào xem Bài 1 của một khóa học bất kỳ.
2.  Ngay khoảnh khắc tải xong giao diện bài giảng, hệ thống sẽ kiểm tra xem User này đã từng có lịch sử học khóa này chưa (bảng `lms_user_course`).
3.  Nếu CHƯA có, hệ thống **âm thầm tự động tạo Data ghi danh (Auto-Enroll)** ngay lập tức với tiến độ bắt đầu là `0%` (`in_progress`).
4.  Cũng từ đúng giây phút đó trở đi, khóa học này mới chính thức nhảy vào nằm ngay ngắn trong trang Dashboard mục "In Progress" của hội viên!

---

Toàn bộ quá trình từ Giai đoạn 1 đến Giai đoạn 6 đảm bảo người dùng có trải nghiệm Premium mượt mà nhất mà hệ thống vẫn lưu trữ đủ Database logic bên dưới!
