# Kế hoạch Triển khai Chi tiết: Tính năng Membership (Gói Hội viên)

*Kế hoạch này được chia nhỏ thành 6 phần việc cụ thể (phases) để đảm bảo không sót logic của bất kỳ trang (page) hay tiến trình (process) nào.*

---

## Giai đoạn 1: Chuẩn hoá Entity & Cấu trúc Dữ liệu (Backend)
**Mục tiêu**: Đảm bảo các Entity lưu trữ đúng và đủ thông tin cần thiết.
1. **Entity `lms_membership_plan` (Định nghĩa Gói Cước)**:
   - Đồng bộ lại các trường cho chuẩn với giao diện Frontend Pricing.
   - Các trường cần xác nhận/cập nhật: `name`, `field_description`, `field_price` (decimal), `field_billing_cycle` (monthly/yearly/lifetime), `field_trial_days` (số ngày dùng thử), `field_features` (danh sách tính năng), `field_course_access` (all/selected), `status`.
2. **Entity `lms_user_membership` (Gói của người dùng cụ thể)**:
   - Khởi tạo và kiểm tra các trường: `field_user` (uid - người dùng), `field_plan` (tham chiếu đến gói `lms_membership_plan`), `field_start_date` (timestamp), `field_end_date` (timestamp), `field_status` (active/expired/cancelled/trial), `field_auto_renew` (boolean).

---

## Giai đoạn 2: Quản trị viên (Manager Backend UI)
**Mục tiêu**: Cung cấp giao diện để Manager định nghĩa Gói và quản lý Học viên có Gói.
1. **Quản lý Membership Plans (`/manage/memberships`)**:
   - Dùng Listbuilder chuẩn của Drupal (hoặc Views) liệt kê tất cả các Gói đang có.
   - Tuỳ chỉnh Form Add/Edit Plan: Trình bày trực quan giá cả, vòng lặp thanh toán, các tính năng đi kèm gói.
2. **Quản lý User Memberships (`/manage/user-memberships`)**:
   - Xây dựng **Views (Trang danh sách)**: Liệt kê chi tiết Học viên đang có gói. Cột hiển thị: Tên Học Viên, Tên Gói đang dùng, Ngày bắt đầu, Ngày hết hạn, Trạng thái (Active/Expired).
   - Thêm Bộ lọc (Exposed Filters): Lọc theo Trạng thái (vd: chỉ xem gói đang Active), tìm kiếm tên học viên, lọc theo Tên Gói.
   - **Gán Gói Thủ Công (Manual Assign)**: Xây dựng route/form `/manage/user-memberships/add` cho phép Manager tự cấp gói cho học viên (không qua thanh toán). Khi gán, hệ thống hỏi: *Chọn Học viên, Chọn Gói, Số tháng tặng*. Logic sẽ tự tính ngày hết hạn và tạo bản ghi.

---

## Giai đoạn 3: Học viên (Student Frontend UI)
**Mục tiêu**: Giao diện hiển thị bán Gói và cho học viên quản lý tình trạng gói của mình.
1. **Trang Pricing / Mua Gói (`/pricing`)**:
   - Cập nhật Controller `PageController::pricing()`.
   - Render động (Dynamic render) các Gói từ Entity `lms_membership_plan` (lấy đúng giá tiền, cấu hình chu kỳ tháng/năm, và các gạch đầu dòng tính năng).
   - Phím Call-To-Action (Nút "Đăng ký"): Dẫn sang trang Checkout của hệ thống LMS.
2. **Trang Quản lý Gói của Tôi (`/my-membership` hoặc tích hợp vào User Dashboard)**:
   - Xây dựng Controller trả về giao diện twig.
   - Hiển thị thông tin tổng quan: Gói đang kích hoạt (Tên gói, biểu tượng), Thời gian còn lại (Còn bao nhiêu ngày), Trạng thái Auto-renew (Có bật tự động trừ tiền hay không).
   - Nút "Huỷ tự gán" (Cancel Auto-Renewal): Chỉ thay đổi trạng thái `field_auto_renew = FALSE`. (Lưu ý: Gói của người dùng vẫn sẽ dùng bình thường cho tới ngày hết hạn thực tế, chỉ là đến hạn không gia hạn nữa).

---

## Giai đoạn 4: Luồng Thanh toán (Checkout & Order Integration)
**Mục tiêu**: Kết nối chức năng Gói với hệ thống Order/Cart hiện hành.
1. **Giỏ hàng (Cart) & Order Item**: Cho phép add một `lms_membership_plan` vào giỏ hàng (`lms_order_item`) giống như mua một khóa học. 
2. **Kích hoạt tự động (Order Subscriber)**: 
   - Viết Event Subscriber (`MembershipOrderSubscriber`) lắng nghe event khi một `lms_order` chuyển sang trạng thái `Paid` (Đã thanh toán / Hoàn tất).
   - Hệ thống quét các item trong Order đó, nếu có item là Membership Plan:
     - Lấy thông tin User thanh toán + Plan đã mua.
     - Tính biểu thức tính giờ: `thời gian hiện tại` + `chu kỳ kỳ hạn của gói` (ví dụ + 1 tháng, hoặc + 1 năm). Tạm lưu làm `end_date`.
     - Sinh ra bản ghi `lms_user_membership` trạng thái `Active`.

---

## Giai đoạn 5: Logic Kiểm soát Quyền học (Course Access Control)
**Mục tiêu**: Bộ não kiểm tra – Chặn những thanh niên không mua Gói vào học.
1. **Service `MembershipManager` (`lms_membership.manager`)**: 
   - Hàm tiện ích `hasActiveMembership(User $user)`: Trả về TRUE nếu user có 1 bản ghi `lms_user_membership` với trạng thái Active và `end_date > today`.
2. **Access Hook (`hook_entity_access` tại `lms_course` / `lms_lesson`)**:
   - Khi có người truy cập URL chi tiết Khoá Học hoặc Bài Giảng, hệ thống đánh giá:
   - Khoá học này có `field_price_type` == `membership` không?
   - Nếu tham số là True: Gọi hàm `hasActiveMembership()`.
   - Nếu User CÓ gói: Cho học (Return `Allowed`).
   - Nếu User KHÔNG CÓ gói: Chặn (Return `Forbidden`/`Neutral`) và hiển thị màn hình "Bạn cần mua Gói Hội Viên để xem nội dung này", kèm link tới `/pricing`.

---

## Giai đoạn 6: Vòng đời & Tự động Gia hạn (Cron & Lifecycle)
**Mục tiêu**: Chạy ngầm tự động mỗi đêm để quét các gói hết hạn (Giống hệ tuần hoàn).
1. **Cron Job (`MembershipRenewal` Service)**: 
   - Hook vào rãnh thời gian cron mỗi đêm của Drupal.
   - Quét Database: Tìm tất cả `lms_user_membership` đang `Active` có `field_end_date` <= ngày giờ hiện tại.
2. **Kịch bản thanh lọc**:
   - Đối với gói **KHÔNG tự động gia hạn (`auto_renew = false`)**: Chuyển trạng thái sang `Expired` (Hết hạn). Gửi email (qua Event Dispatcher) báo người dùng tài khoản đã hết hạn. Khoá học sẽ tự động gỡ mạc do Giai đoạn 5 đánh rớt.
   - Đối với gói **CÓ tự động gia hạn**: (Dành cho Payment Gateway Stripe Subscriptions) – Check với gateway để xem có khoản thu tiền mới không. Nếu Stripe webhook báo thanh toán thành công qua luồng webhook -> Mở rộng update `end_date`. Nếu webhook báo trừ tiền thất bại -> Gửi email báo lỗi thẻ, duy trì trạng thái ân hạn (3-day Grace Period) trước khi đổi sang `Expired`.
