# Bank Transfer Receipt Upload Plan

## Mục Tiêu
Cho phép người dùng tải lên hình ảnh biên lai thành toán (Payment Receipt) làm bằng chứng chuyển khoản. Admin sẽ dễ dàng kiểm tra và phê duyệt các đơn hàng đang chờ trực tiếp từ Workspace.

## Các Thay Đổi Dự Kiến

### 1. Cập nhật Order Entity
#### [MODIFY] `web/modules/custom/lms_order/src/Entity/Order.php`
- Thêm trường `field_payment_receipt` định dạng `image` để lưu hình ảnh biên lai tải lên.
- Chạy mã PHP cài đặt trường vào database.

### 2. Tạo File Upload Form
#### [NEW] `web/modules/custom/lms_order/src/Form/BankTransferForm.php`
- Tạo Form chứa mã QR VietQR và hỗ trợ field `managed_file` để tải biên lai lên.
- Khi gửi form, file được gắn vào Order Entity và hiển thị thông báo thành công.

### 3. Cập nhật Controller
#### [MODIFY] `web/modules/custom/lms_order/src/Controller/CheckoutController.php`
- Thay đổi hàm `bankTransferInstruction` để trả về `BankTransferForm` thay vì template trống thuần.

### 4. Cập nhật Template
#### [MODIFY] `web/modules/custom/lms_order/templates/lms-bank-transfer.html.twig`
- Cập nhật template để in ra biến `form` phía dưới mã QR.

### 5. Tối ưu Navigation Admin
#### [MODIFY] `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig`
- Bổ sung nút `Orders` trỏ tới `/manage/orders` bên thanh sidebar của Workspace để Admin bấm vào nhanh chóng.
