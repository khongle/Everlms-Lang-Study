# Kế hoạch Triển khai Hệ thống Notification LMS

Sau khi đối chiếu với tài liệu `01-requirement` (đặc biệt là `07-communication-entities.md` & `08-communication.md`) và `02-architecture` (`system-architecture.md`), kế hoạch triển khai chi tiết được chuẩn hóa như sau:

## 1. Cập nhật Entity `lms_notification` (Theo `07-communication-entities.md`)
Entity `lms_notification` đã được khởi tạo cơ bản nhưng cần nâng cấp để bám sát ERD mô tả:

#### [MODIFY] `lms_notification/src/Entity/Notification.php`
- **Người nhận (Recipient)**: Entity hiện đang dùng `EntityOwnerTrait` (`uid`). Đây là chuẩn của Drupal và đóng vai trò hoàn hảo cho `field_recipient` như đặc tả.
- **field_type**: Cập nhật lại danh sách `allowed_values` chính xác gồm: `enrollment`, `quiz_result`, `certificate`, `payment`, `forum_reply`, `membership_expiry`, `new_lesson`, `announcement`, `reminder`.
- **Thêm field_channel**: list_string (multi) với các options `in_app`, `email`, `push`.
- **Thêm field_reference_type**: string (Entity type được reference, VD: course, quiz).
- **Thêm field_reference_id**: integer (Entity ID được reference).

---

## 2. Notification Dispatcher & Event-Driven Architecture (Theo `system-architecture.md` 4.3)
Theo kiến trúc tổng thể, hệ thống ứng dụng mô hình Event-Driven. Việc phát các thông báo cần được thực thi tự động khi các sự kiện xảy ra (VD: `QuizCompletedEvent`).

#### [NEW] `lms_notification/src/NotificationManager.php`
- Core service chịu trách nhiệm lấy Preferences của User, từ đó quyết định phát thông báo qua channel nào.
- Nếu `in_app`: tạo entity `lms_notification`.
- Nếu `email`: chuyển qua Custom MailManager / Queue của Drupal.
- Nếu `push`: thiết lập foundation (log lại hoặc chuẩn bị một service interface) để chuẩn bị tích hợp Firebase (FCM) như trong file `tech-stack.md`.

#### [NEW] Event Subscribers
- Cần tạo các EventSubscriber (VD: `EnrollmentSubscriber`, `QuizResultSubscriber`) lắng nghe các sự kiện lõi của ứng dụng (trong `lms_base` hoặc các module tương ứng) để gọi `$notificationManager->dispatch(...)`.

---

## 3. Quản lý Tùy chọn Nhận thông báo (N-04)
Người dùng có quyền chọn nhận thông báo qua kênh nào cho sự kiện nào.

#### [NEW] `lms_notification/src/Form/NotificationPreferencesForm.php`
- Form nằm tại `/manage/settings/notifications` (tương ứng với Sitemap MS-04).
- Giao diện dạng Grid/Toggle để bật tắt `Email` / `In-App` cho từng loại (enrollment, reply...).
- **Lưu trữ dữ liệu**: Đề xuất tiếp tục dùng **Drupal User Data API (`\Drupal::userData()`)** để hệ thống gọn nhẹ và tránh làm phình `users_field_data` vì các tuỳ chọn này rất động.

---

## 4. In-App Notification (N-01) & Giao diện

#### [NEW] `lms_notification/src/Plugin/Block/NotificationBellBlock.php`
- Tạo Notification Bell ở thanh điều hướng. 
- Filter `uid` = current_user và `field_read` = FALSE để hiển thị unread count badge.
- List thông báo sẽ hiển thị ở trang `/notifications` (Route đã có sẵn trong codebase).

---

## 5. Các tính năng Bổ trợ (N-05, N-07)

#### [NEW] Tính năng Admin Announcements (N-05)
- Thêm Backend Controller/Form tại `/admin/lms/notifications/broadcast` để Manager/Admin có thể soạn một thông báo (type `announcement`) và phát gửi (Broadcast) cho tất cả user hoặc một role nhất định. Cần dùng Batch API của Drupal để tạo nhiều Record tránh timeout.

#### [NEW] Tính năng Reminder (N-07)
- Cần bổ sung logic (thể hiện bằng 1 Drush cron command hoặc 1 Queue Worker chạy theo lịch) quét các accounts của học viên không có activity trong vòng X ngày. Kích hoạt trigger `$manager->dispatch('reminder', ...)` hệ thống sẽ tự sinh Notification kéo họ quay lại học.

---

## 6. Auto-cleanup
Theo yêu cầu hệ thống phải xóa notification quá 90 ngày.

#### [MODIFY] `lms_notification/lms_notification.module`
- Cài đặt `hook_cron()` query trực tiếp tìm các `lms_notification` có `field_read = TRUE` và `created < 90 ngày trước`, sau đó xóa để tối ưu database.

---

## 7. Phản hồi và Chờ ý kiến (Chờ User Review)
> Bạn hãy đánh giá xem bản phân tích trên đã hoàn toàn khớp theo module logic của folder `docs` chưa.
> 1. Sử dụng User Data API thay vì thêm raw fields vào Entity User cho Notification Preferences có phù hợp với kiến trúc mong muốn?
> 2. Kế hoạch Event-Driven (Dùng EventSubscribers để trigger thông báo) đã khớp với ý định ở Architecture?
> Cấu trúc này sẽ đảm bảo tính mở và hoàn toàn tuân thủ các mã yêu cầu `N-01 .. N-07`. Mong phản hồi từ bạn.
