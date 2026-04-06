# Kế hoạch Tích hợp Ví điện tử MoMo & ZaloPay

## Mục tiêu
Mở rộng phương thức thanh toán cho EverLMS bằng việc hỗ trợ hai ví điện tử phổ biến nhất Việt Nam là MoMo và ZaloPay. Quá trình tích hợp sẽ sử dụng API Web Payment (Thanh toán qua cổng chuyển hướng) và tự động hóa hoàn toàn luồng duyệt đơn thông qua Webhook.

## Các Module Cần Phát triển
Để hệ thống gọn gàng và dễ mở rộng, chúng ta sẽ tạo 2 module Độc lập:
1. `lms_momo`: Chuyên xử lý API và Webhook của MoMo.
2. `lms_zalopay`: Chuyên xử lý API và Webhook của ZaloPay.

## Chi tiết Kế hoạch Triển khai

### 1. Cập nhật Giao diện Cấu hình (Payment Settings)
**File:** `PaymentSettingsForm.php`
- Bổ sung tab/fieldset cho **MoMo Settings**:
  - `momo_partner_code`: Mã đối tác (Partner Code)
  - `momo_access_key`: Key truy cập (Access Key)
  - `momo_secret_key`: Khóa bí mật tạo chữ ký (Secret Key)
- Bổ sung tab/fieldset cho **ZaloPay Settings**:
  - `zalopay_app_id`: Mã ứng dụng (App ID)
  - `zalopay_key1`: Key 1 (Dùng để tạo MAC lúc gửi request)
  - `zalopay_key2`: Key 2 (Dùng để verify MAC chống giả mạo lúc nhận Webhook)

### 2. Cập nhật Trang Checkout
**File:** `CheckoutForm.php` & `MembershipCheckoutForm.php`
- Thêm 2 phương thức thanh toán mới vào danh sách radio options (`momo`, `zalopay`).
- Xử lý logic Submit khi người dùng chọn Ví:
  1. Dùng mã Đơn hàng (Order ID) và số tiền (Amount) để hash MAC/Signature.
  2. Gọi POST Request sang API Create Order của MoMo / ZaloPay.
  3. Nhận về URL thanh toán (`payUrl` của MoMo hoặc `order_url` của ZaloPay).
  4. Redirect ngay lập tức trình duyệt của người dùng sang trang thanh toán của Ví.

### 3. Xây dựng Controller xử lý Webhook (Server-to-Server)
Xây dựng 2 API Endpoints đón dữ liệu trả về từ máy chủ của Ví điện tử (chạy ngầm).
**Files dự kiến:** `MomoController.php` & `ZaloPayController.php`
- Route 1: `/api/payment/momo-ipn`
- Route 2: `/api/payment/zalopay-callback`
- **Logic bên trong Webhook**:
  1. Nhận chuỗi JSON Payload.
  2. Băm chữ ký (Hash) bằng SecretKey / Key2 để xác thực request là thật, không phải hacker gửi data giả mạo.
  3. Trích xuất `order_id` lưu trong payload.
  4. Truy vấn database lấy đúng `lms_order`, đổi `field_status` thành `completed`.
  5. Save order -> Kích hoạt `hook_entity_update` -> Tự động Enroll khóa học, tự động gửi Email, gửi Web Notification.

### 4. Quy trình Trải nghiệm của Học viên (User Flow)
1. Thêm khóa học vào giỏ -> Ấn "Checkout".
2. Chọn "MoMo" -> Bấm "Place Order".
3. Màn hình tự nhảy sang trang của MoMo hiển thị mã QR.
4. Rút điện thoại quét mã QR trên web (hoặc nếu mở trên đt sẽ nhảy thẳng sang app MoMo). Bấm chuyển tiền.
5. MoMo báo thành công -> Trình duyệt tự động chuyển hướng quay về trang `Dashboard` của EverLMS.
6. Cùng lúc đó, Webhook báo về máy chủ xử lý đơn hàng cái "rụp". Khóa học đã mở sẵn chờ học viên!

## Điều kiện cần chuẩn bị trước khi Coding
1. **Tài khoản Sandbox API**: Truy cập portal dành cho Developer của MoMo (https://developers.momo.vn) và ZaloPay (https://docs.zalopay.vn) để lấy bộ Key Test.
2. **Expose Localhost**: Hệ thống EverLMS hiện đang chạy ở local DDEV. Để máy chủ MoMo/ZaloPay gọi ngược Webhook về được, cần dùng lệnh `ddev share` (Ngrok) để lấy HTTPS public URL ghép vào làm URL Callback.
