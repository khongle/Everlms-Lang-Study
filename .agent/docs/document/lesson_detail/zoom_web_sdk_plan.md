# Kế hoạch Tích hợp Zoom Web SDK

Kế hoạch này trình bày chi tiết các bước cần thiết để nhúng màn hình học Zoom trực tiếp vào trang học tập của EverLMS, giúp học viên có thể tham gia lớp học trực tiếp mà không cần rời khỏi trình duyệt hoặc mở ứng dụng Zoom trên máy tính.

## Yêu cầu anh xác nhận trước khi làm

> [!IMPORTANT]
> Để thực hiện được việc này, chúng ta cần tạo chữ ký điện tử mã hóa (JWT) bằng PHP để bảo mật phòng học. Phổ biến nhất là dùng thư viện Composer `firebase/php-jwt`. **Anh cần xác nhận xem em có được quyền chạy lệnh `composer require firebase/php-jwt` trên thư mục gốc của EverLMS không nhé.**
>
> Anh cũng sẽ cần lên trang Zoom Developer Marketplace tạo thêm một ứng dụng tên là **Meeting SDK** để lấy cặp mã `SDK Key` và `SDK Secret` (Hai mã này hoàn toàn khác với mã Server-to-Server OAuth anh lấy đợt trước).

## Các thay đổi dự kiến

### Khu vực Cấu hình `lms_zoom`

#### [MODIFY] [ZoomSettingsForm.php](file:///wsl$/Ubuntu/home/phatle/projects/everlms/web/modules/custom/lms_zoom/src/Form/ZoomSettingsForm.php)
- Bổ sung thêm 2 trường dữ liệu mới: `sdk_key` và `sdk_secret` để lưu thông tin cấu hình Zoom Meeting SDK, song song với cấu hình Server-to-Server OAuth hiện tại.

### Routing & Xử lý Backend của `lms_zoom`

#### [MODIFY] [lms_zoom.routing.yml](file:///wsl$/Ubuntu/home/phatle/projects/everlms/web/modules/custom/lms_zoom/lms_zoom.routing.yml)
- Chuyển đổi đường dẫn `lms_zoom.join_meeting` (`/zoom/join/{meeting_id}`) từ dạng *chuyển hướng (redirect)* thành đường dẫn trang bình thường để in ra giao diện Zoom Web.
- Thêm một đường dẫn API (JSON) mới để tự động cấp phát chữ ký bảo mật SDK (Signature) từ phía server.

#### [MODIFY] [ZoomController.php](file:///wsl$/Ubuntu/home/phatle/projects/everlms/web/modules/custom/lms_zoom/src/Controller/ZoomController.php)
- Viết lại hàm `joinMeeting()`: Thay vì dùng `TrustedRedirectResponse` (đẩy văng qua Zoom), nó sẽ lấy passcode của phòng học, chuẩn bị thông số và trả về giao diện cho file Twig hiển thị Web SDK.
- Tạo hàm `generateSignature()`: Đây là API bảo mật sử dụng `SDK Key`, `SDK Secret`, `Meeting ID`, và `Role` (Quyền người tham gia) để mã hóa thành hàm băm JWT trả về cho Web.

### Tích hợp Frontend (Javascript & CSS)

#### [MODIFY] [lms_zoom.libraries.yml](file:///wsl$/Ubuntu/home/phatle/projects/everlms/web/modules/custom/lms_zoom/lms_zoom.libraries.yml)
- Đăng ký bộ thư viện `websdk` mới nhằm kéo các file bắt buộc từ máy chủ CDN chính thức của Zoom (`@zoomus/websdk`).
- Đính kèm một file Javascript tự viết để làm nhiệm vụ "lắp ráp" màn hình Zoom.

#### [NEW] `lms_zoom/js/zoom-websdk-runner.js`
- Viết kịch bản khởi tạo Zoom Client: `ZoomMtg.preLoadWasm()`, `ZoomMtg.prepareWebSDK()`, và `ZoomMtg.init()`.
- Lập trình logic để Javascript tự gọi lên API backend xin chữ ký, sau khi có chữ ký thì tự động chạy lệnh `ZoomMtg.join()` đẩy học viên vào lớp.

#### [NEW] `lms_zoom/templates/lms-zoom-web-player.html.twig`
- Một file giao diện màn hình đen toàn trang (hoặc đóng khung) chứa phần tử `<div id="zmmtg-root"></div>`. Đây sẽ là nơi Zoom tự động "bơm" cái màn hình video call, micro và camera của nó vào.

## Kế hoạch Kiểm tra (Verification)

### Kiểm tra thủ công
1. Đảm bảo thư viện Composer được cài đặt và API cấp chữ ký ở backend chạy mượt mà không văng ra trang lỗi 500.
2. Admin nhập xong cả 2 bộ thông số cấu hình.
3. Học viên bấm nút "Join Class Now" khi hết thời gian đếm ngược.
4. Học viên được đưa tới màn hình học ngay trên web thay vì bị bắn link ra ngoài.
5. Giao diện Zoom hiển thị chính xác yêu cầu cấp quyền Micro/Camera, và video hiện lên lọt thỏm trong giao diện của Drupal.
