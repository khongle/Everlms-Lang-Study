# Tóm tắt Logic Quy trình Thanh toán (Payment Process)

Dựa trên tài liệu `docs` và mã nguồn (`lms_payment`, `lms_order`, `lms_stripe`, `lms_paypal`), dưới đây là luồng xử lý thanh toán của hệ thống EverLMS:

## 1. Cấu trúc Module
*   **`lms_payment`**: Module lõi cung cấp `PaymentGatewayInterface` và `PaymentManager`. Đây là nơi đăng ký và quản lý các cổng thanh toán (Stripe, PayPal, v.v.).
*   **`lms_order`**: Quản lý giỏ hàng/thanh toán thông qua `CheckoutController` và form `CheckoutForm`. Đồng thời định nghĩa các thực thể `Order` (Đơn hàng) và `OrderItem` (Chi tiết đơn).
*   **Các Plugin Cổng Thanh toán**:
    *   `lms_stripe`: Implement cổng thanh toán Stripe.
    *   `lms_paypal`: Đang trong quá trình phát triển (TODO REST SDK).
    *   Ngoài ra dự phòng hỗ trợ ZaloPay/MoMo và Bank Transfer (Chuyển khoản thủ công).

## 2. Luồng Thanh toán Khóa học (Checkout Flow)
1.  **Khởi tạo Checkout**: User truy cập trang checkout của một khóa học (`/checkout/{course_id}`). `CheckoutController` sẽ tải thông tin khóa.
2.  **Khóa học Miễn phí ($0)**: Nếu giá khóa học = 0, hệ thống tự động gọi `createAndFulfillFreeOrder`, tạo Order trạng thái `completed`, ghi danh học viên ngay lập tức và chuyển hướng vào học.
3.  **Khóa học Trả phí**:
    *   Hệ thống build `CheckoutForm`.
    *   Nếu Gateway là Stripe, mã nguồn sẽ gọi API Stripe tạo `PaymentIntent` ngầm và lấy `client_secret` truyền xuống Frontend (để render giao diện nhập thẻ bảo mật).
4.  **Submit Form**: User chọn phương thức thanh toán và bấm xác nhận:
    *   Hệ thống khởi tạo thực thể `Order` và `OrderItem`, set trạng thái ban đầu là `pending`.
    *   Dựa trên phương thức (`stripe`, `paypal`, `zalopay`, `bank_transfer`), hệ thống dùng `PaymentManager` gọi `processPayment` tương ứng.
    
## 3. Xử lý Kết quả Thanh toán (Payment Result)
*   `processPayment` trả về mảng kết quả gồm `status`, `transaction_id`, và `message`:
    *   **`success`** (VD: Stripe thanh toán thẻ thành công): Cập nhật Order thành `completed`, lưu `payment_id`, thông báo thành công và chuyển hướng user vào khóa học.
    *   **`pending`** (VD: Bank Transfer): Giữ Order ở trạng thái `pending`, hiện thông báo hướng dẫn chuyển khoản và mã đơn hàng (Order Number).
    *   **`redirect`** (VD: E-wallet như ZaloPay/PayPal): Đơn hàng ở trạng thái `pending`, hệ thống chuyển hướng (redirect) user sang trang thanh toán của đối tác.
    *   **`error`**: Báo lỗi trên form để user thử lại.

## 4. Webhook / Cập nhật tự động (Background)
*   Các gateway đều có khai báo hàm `handleWebhook` trong `PaymentGatewayInterface` để nhận update trạng thái thanh toán từ các cổng (VD: PayPal IPN, Stripe Webhook). Tính năng này sẽ tự nhận diện khi thanh toán chuyển khoản/e-wallet thành công và tự động update Order từ `pending` sang `completed`.

---
**Nhận xét về kiến trúc hệ thống**:
Kiến trúc thanh toán hiện tại khá chuẩn, tuân thủ Plugin architecture của Drupal. Việc tách biệt `lms_payment` (cổng giao tiếp thanh toán) và `lms_order` (quản trị đơn hàng) giúp nền tảng dễ dàng mở rộng thêm các cổng thanh toán nội địa và quốc tế trong tương lai bằng cách tạo thêm plugin class implements `PaymentGatewayInterface`.
