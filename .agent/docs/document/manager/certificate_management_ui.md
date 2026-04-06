# Kế hoạch: Tích hợp Certificate Management vào Manager Dashboard

## 1. Bối cảnh (Context)
Hiện tại tài khoản `Manager` đã có các công cụ quản trị (Dashboard, Courses, Users, Quizzes, Reports). Tuy nhiên, Certificate module đã được xây dựng logic Auto-Issuance nhưng chưa có giao diện Quản lý UI đồng bộ trên Dashboard dark theme này.

## 2. Mục tiêu (Objective)
Tạo một trang **Certificate Management** đồng bộ 100% với phong cách UI/UX hiện tại của Manager Dashboard (Giao diện Dark Theme, Sidebar chung, Layout dạng thẻ Card).

## 3. Các bước thực hiện (Implementation Plan)

### Bước 1: Cập nhật Sidebar Navigation
- Sửa file `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig`.
- Thêm menu item **"Certificates"** với thẻ icon `<i class="fas fa-award"></i>` hoặc `<i class="fas fa-certificate"></i>` bên dưới tab Quizzes.
- Đảm bảo cơ chế active state hoạt động (`active == 'certificates'`).

### Bước 2: Tạo Controller & Route Admin UI
- **Route:** Tạo route mới hoặc bổ sung vào `lms_certificate.routing.yml` (VD: `lms_certificate.admin_certificates`).
- **Controller:** Tạo/Sửa UI Controller (`CertificateAdminController.php`) thực hiện:
  - Query lấy tổng số chứng chỉ đã cấp (`total_issued`).
  - Query lấy phân loại trạng thái (hợp lệ / thu hồi).
  - Load danh sách `UserCertificate` mới nhất (có phân trang).
  - Truyền dữ liệu vào biến `#theme` mới.

### Bước 3: Build Custom Twig Template
- Tạo file `web/modules/custom/lms_certificate/templates/lms-admin-certificates.html.twig` kế thừa layout gốc hoặc bao gồm sidebar.
- **Quick Stats Bar:**
  - Thẻ 1: Tổng số cấp phát (Total Issued).
  - Thẻ 2: Bị thu hồi (Revoked).
  - Thẻ 3: Mới nhất tuần này (Issued This Week).
- **Filter Area:** 
  - Ô tìm kiếm (Search by Code or User Name).
  - Dropdown lọc (Status: Valid / Revoked).
- **Data Table:** 
  - Cột: Thuộc tính chứng chỉ (Người nhận, Khóa học, Ngày cấp, Trạng thái).
  - Cột Actions: Nút **View PDF** (mở file PDF trong tab mới popup), Nút **Revoke** (thu hồi chứng chỉ vi phạm nhanh chóng thông qua AJAX hoặc dialog).

### Bước 4: Tích hợp và Fix Styling
- Tích hợp JS/CSS cho các hành động (nếu có sử dụng modal dialog thu hồi tại chỗ).
- Tận dụng các base class CSS đã có (`lms-admin-card`, `lms-table-responsive`, `lms-status`).
