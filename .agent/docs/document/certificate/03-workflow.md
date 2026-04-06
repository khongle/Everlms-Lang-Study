# Certificate Module - Workflow

## Cấp phát chứng chỉ tự động (Generation Workflow)

1. **Trigger**: Học viên hoàn thành tất cả Lesson trong Course và thi đậu Quiz (đạt Threshold).
2. **Listener**: Hệ thống phát event `CourseCompletedEvent`.
3. **Validation**: `CertificateManager` kiểm tra xem Khóa học này có cấu hình cấp chứng chỉ không & User đã có chứng chỉ này chưa.
4. **Rendering**:
   - Load `lms_certificate_template` được gán cho khóa học (hoặc lấy template mặc định).
   - Tạo mã `verification_code` duy nhất & Sinh mã QR code.
   - Thay thế các placeholder `{{ user_name }}`, `{{ course_name }}` thành dữ liệu thực tế.
   - Chạy HTML qua Engine (`mPDF`) sinh nguyên bản PDF.
5. **Storage**:
   - Lưu PDF vào thư mục Private của Drupal `private://certificates/`.
   - Tạo record Entity `lms_user_certificate` trỏ tới file PDF vừa tạo.
6. **Notification**: Gửi email chúc mừng và đính kèm file PDF / link tải cho User.

## Tra cứu chứng chỉ (Verification Workflow)

1. Khách truy cập vào đường dẫn `/verify-certificate`.
2. Form tra cứu yêu cầu nhập `Verification Code` (VD: `CERT-ABC-XYZ`).
3. Hệ thống query entity `lms_user_certificate`.
4. Trả về kết quả: (Hợp lệ, Cấp cho [Tên Học viên], Tên Khóa học, Ngày cấp, Thời hạn). Nếu nhập sai thì báo không tồn tại.
