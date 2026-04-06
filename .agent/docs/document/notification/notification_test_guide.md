# Hướng dẫn Kiểm thử Hệ thống Notification

Tài liệu này hướng dẫn chi tiết các bước để kiểm thử toàn bộ các tính năng của Hệ thống Notification vừa được thiết kế và tích hợp vào nền tảng LMS:

## 1. Cập nhật Database (Bắt buộc)
Tính năng Notification vừa được bổ sung các entity field mới (`field_channel`, `field_reference_type`, `field_reference_id`...). Do đó, bạn cần chạy Schema Update trước.
Mở Terminal ở thư mục gốc của dự án (`everlms`) và chạy lệnh sau:
```bash
ddev drush cache:rebuild
ddev drush updb -y
```

## 2. Kiểm thử Icon Chuông Thông Báo (Natively Integrated UI)
Giao diện chuông (Notification Bell) đã được **tích hợp cứng trực tiếp (natively integrated)** vào thanh Header của website để đồng bộ tuyệt đối về styling với các chức năng khác như Giỏ Hàng/Wishlist và Menu User. Bạn không cần phải kéo thả Block thủ công nữa.
1. Đảm bảo chạy lệnh `ddev drush cr` để Drupal nhận ra giao diện đã nhúng mới.
2. Về trang chủ (Frontend) hoặc trang Forum.
3. Quan sát ở góc trên Tận Cùng Bên Phải: Biểu tượng chuông xám thanh lịch đã xuất hiện ngay ngắn bên trái của biểu tượng Trái Tim.
4. Khi có thông báo mới (Unread > 0), nó sẽ đỏ rực báo số, khi hết nó sẽ tàng hình cục đỏ đó đi.

## 3. Kiểm thử Cấu hình Tùy chọn Nhận Thông Báo (User Preferences)
1. Đóng vai là một sinh viên hoặc tự dùng tài khoản hiện tại, truy cập đường dẫn: `/manage/settings/notifications`.
2. Đây là giao diện Quản lý Tùy chọn Notification làm dựa trên `UserData` API.
3. Thử check hoặc un-check vào các cột **In-App Notification** và **Email** ở các dòng tùy thích.
4. Bấm **Save Preferences**. Giao diện sẽ hiển thị báo lưu thành công. Bạn hãy F5 tải lại trang để đảm bảo các tick mark (dấu check) vẫn giữ nguyên theo đúng ý đồ.

## 4. Kiểm thử Chức năng Admin Broadcast
1. Truy cập đường dẫn dành riêng cho Admin: `/admin/lms/notifications/broadcast`.
2. Nhập thông tin:
   - **Title**: Chào mừng các bạn học viên mới!
   - **Message**: Nền tảng vừa được cập nhật, hãy bắt đầu trải nghiệm ngay!
3. Bấm nút **Send Broadcast**.
4. Lúc này hệ thống sẽ chạy thanh tiến trình (Batch Process) và rải thông báo dạng hệ thống xuống tất cả user có trạng thái = Active.
5. Chạy xong thành công, tiến hành kiểm tra biểu tượng Chuông ở header (Tại bước 2) xem nó có nảy số đếm Unread (+1) kèm huy hiệu màu đỏ không.
6. Truy cập danh sách gốc `/notifications` để đọc list thông báo chi tiết mà thư viện sinh ra.

## 5. Kiểm thử Tích hợp Lõi (Event-Driven: Quiz Result)
Bạn có thể tự test luồng học viên tích hợp với module `lms_quiz`:
1. Đăng nhập 1 tài khoản học viên và thực hiện làm một bài Quiz bất kì tới lúc nộp cuối cùng.
2. Khi hoàn thành (**QuizPassedEvent** được sinh ra trong codebase), EventSubscriber sẽ check Preferences của học viên này xem họ có mở luồng nhận thông báo `quiz_result` dạng In-app hay không.
3. Nếu họ bật In-app preference, icon chuông sẽ tự động nảy số; học viên bấm vào sẽ bắt được thông báo trích xuất từ dữ liệu quiz: "Congratulations on passing!".

*(Nếu muốn test nhanh mà không tốn thời gian làm Quiz, bạn có thể chạy lệnh Drush dưới đây. Lệnh này sẽ quét toàn bộ active users và bắn thẳng một thông báo Test tới tất cả mọi người, đảm bảo tài khoản sinh viên/admin của bạn hiện tại chắc chắn nhận được)*:
```bash
ddev drush php-eval "foreach (\Drupal\user\Entity\User::loadMultiple() as \$u) { if (\$u->id() > 0) \Drupal::service('lms_notification.manager')->dispatch('announcement', \$u, 'Test System Broadcast', 'Ping! This is a test broadcast via CLI dispatcher', '/notifications'); }"
ddev drush cr
```
Sau đó F5 lại trang Web để ngắm cái huy hiệu đỏ chói xuất hiện trên chiếc chuông Header! Bấm vào `/notifications` để xem chi tiết và trải nghiệm logic tự động gỡ thông báo: *Sau khi bạn load trang danh sách đó, hệ thống sẽ tự mark-as-read và xóa cục đỏ trên chuông ngay*.

## 6. Kiểm thử Lệnh Tự động Nhắc Nhở (CLI Drush Reminder)
Lệnh này nhắc nhở các users đã không tương tác vượt quá 7 ngày. Bạn đã chạy thành công trước đó (báo không tìm thấy), nhưng logic đầy đủ là:
```bash
ddev drush lms-remind
```
*Ghi chú: Nếu không có user nào inactive trong khoảng 7-14 ngày, CLI sẽ báo "No inactive users found". Bạn có thể vào phpMyAdmin cập nhật sửa cột `access` của 1 record tại bảng DB `users_field_data` lùi về chừng >7 ngày trước để có đối tượng test thực.*

## 7. Kiểm thử Tự Dọn Dẹp (Auto-Cleanup Cronjob)
Cron sẽ tự động query và xóa bỏ toàn bộ Notification thỏa 2 điều kiện kẹp: Đã Đọc (`field_read` = TRUE) và cũ quá `90 ngày`:
1. Bạn vào phpMyAdmin hoặc dùng lệnh Drush sửa cột `created` của một record entity `lms_notification` bất kì đã có trong hệ thống hiện tại, cho lùi lại > 3 tháng (`created` trừ đi tầm 8 triệu giây).
2. Hãy đảm bảo check nó thành `field_read` = 1.
3. Chạy lệnh: `ddev drush cron`
4. Quai lại check database xem record đó có bị huỷ hoàn toàn chưa để khẳng định nền tảng database sẽ không bao giờ quá tải dung lượng tệp cặn.
