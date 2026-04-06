# Kế hoạch Tích hợp Chuyển khoản VietQR

## Mô tả mục tiêu
Nâng cấp phương thức thanh toán "Chuyển khoản Ngân hàng" (Bank Transfer) bằng cách tự động tạo mã VietQR cho người dùng quét. Mã QR sẽ được điền sẵn thông tin tài khoản ngân hàng của Admin, số tiền chính xác của đơn hàng và Ghi chú chuyển khoản là Mã Đơn hàng (Order Number).

## Các Thay đổi Dự kiến

### Giao diện Cấu hình (Configuration UI)
#### [MODIFY] `web/modules/custom/lms_payment/src/Form/PaymentSettingsForm.php`
- Thêm các trường cấu hình mới vào phần "Bank Transfer Settings":
  - `bank_bin`: Tên viết tắt hoặc Mã BIN của Ngân hàng (VD: VCB, MB, 970436).
  - `bank_account_no`: Số tài khoản ngân hàng của Admin.
  - `bank_account_name`: Tên chủ tài khoản ngân hàng (VD: NGUYEN VAN A).

### Định tuyến & Điều khiển (Routing & Controllers)
#### [MODIFY] `web/modules/custom/lms_order/lms_order.routing.yml`
- Thêm route mới `lms_order.bank_transfer` trỏ tới `/checkout/bank-transfer/{lms_order}` để hiển thị hướng dẫn thanh toán và mã VietQR sau khi đơn hàng chờ (pending order) được tạo.

#### [MODIFY] `web/modules/custom/lms_order/src/Controller/CheckoutController.php`
- Thêm phương thức `bankTransferInstruction(Order $lms_order)` để trả về một mảng render (render array) hiển thị mã QR dựa trên Quick Link API của `vietqr.io`.

### Giao diện & Template (Theme & Template)
#### [MODIFY] `web/modules/custom/lms_order/lms_order.module`
- Thêm `lms_bank_transfer` vào `hook_theme()`.

#### [NEW] `web/modules/custom/lms_order/templates/lms-bank-transfer.html.twig`
- Tạo một trang thông báo thanh toán thành công đẹp mắt, hiển thị nổi bật hình ảnh mã VietQR, số tiền cần chuyển, thông tin ngân hàng và Mã Đơn hàng.

### Xử lý Form Thanh toán (Form Submission Handlers)
#### [MODIFY] `web/modules/custom/lms_order/src/Form/CheckoutForm.php`
- Đổi hướng chuyển trang (redirect) của trạng thái `pending` sang route mới là `lms_order.bank_transfer`.

#### [MODIFY] `web/modules/custom/lms_order/src/Form/MembershipCheckoutForm.php`
- Đổi hướng chuyển trang (redirect) của trạng thái `pending` sang route mới là `lms_order.bank_transfer`.

### Quy trình Duyệt Đơn hàng chuyển khoản (Bank Transfer Approval Workflow)
Quy trình này đảm bảo tính chặt chẽ và an toàn khi xác thực thanh toán:
1. **Kiểm tra Danh sách:** Quản trị viên (Manager/Admin) truy cập **Workspace > Orders** (`/workspace/orders`). Tuyệt đối sử dụng trang này làm giao diện quản lý chính thay cho `/manage/orders`.
2. **Xác thực Biên lai:** Tìm các đơn hàng đang ở trạng thái `Pending`. Bấm nút **Edit** để mở form chi tiết.
3. **Đối chiếu Giao dịch:** Tại trường **Payment Receipt** (Biên lai thanh toán), mở hình ảnh user đã upload và đối chiếu 3 thông tin với biến động số dư ngân hàng:
   - **Số tiền:** Phải khớp với `Order Total`.
   - **Nội dung:** Phải chứa `Order Number` (Mã đơn hàng).
   - **Thời gian:** Trùng khớp với thời điểm giao dịch.
4. **Cập nhật Trạng thái:**
   - Nếu **HỢP LỆ**: Đổi trường `Status` từ `Pending` sang `Completed`.
   - Nếu **KHÔNG HỢP LỆ/GIẢ MẠO**: Chuyển sang `Cancelled` hoặc `Failed`.
5. **Cấp quyền tự động (Auto-Enrollment):** Ngay khi lưu trạng thái `Completed`, hệ thống (thông qua `hook_entity_update` trong `lms_order.module`) sẽ tự động cấp quyền truy cập khoá học cho User. User có thể vào lớp học ngay tức thì.
6. **Gửi Thông báo Tự động (Auto-Notification):** Cùng lúc với quá trình cấp quyền, hệ thống sẽ tự động phát đi 2 kênh thông báo:
   - **Email Notification:** Gửi email xác nhận thanh toán qua `hook_mail`, báo cho học viên biết tài khoản đã được kích hoạt.
   - **Web App Notification:** Tạo một bản ghi `lms_notification` để hiển thị trực tiếp vào quả chuông thông báo trên nền tảng web, giúp học viên nhận tin tức thời nếu đang online.
