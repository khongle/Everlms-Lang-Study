# Hướng Dẫn Vận Hành & Quy Trình Hoạt Động (Certificate Module)

Tài liệu này mô tả chi tiết quy trình cấp phát, quản lý và xác thực chứng chỉ trong hệ thống EverLMS.

## 1. Quy Trình Tự Động Cấp Phát (Auto-Issuance)
Khi học viên hoàn thành khóa học hoặc vượt qua bài kiểm tra cuối khóa:
1. **Trigger:** Hệ thống ghi nhận trạng thái **Passed** cho một `QuizResult`.
2. **Event:** Bắn ra sự kiện `QuizPassedEvent`.
3. **Listen & Trace:** `CertificateEventListener` bắt lấy sự kiện này, truy vết ngược từ `Quiz -> Lesson -> Chapter -> Course`.
4. **Issue:** `CertificateManager` kiểm tra xem học viên đã có chứng chỉ của khóa này chưa. Nếu chưa:
   - Sinh ra một mã code độc nhất ngẫu nhiên (VD: `E25630C463`).
   - Gọi động cơ `mPDF` (`CertificateGenerator`) để render HTML Template (chứa Logo, Chữ ký, Background) thành file PDF vật lý.
   - Lưu file PDF vào `public://certificates/issued/`.
   - Tạo bản ghi `UserCertificate` lưu vào cơ sở dữ liệu Drupal để quản lý.

## 2. Quản Lý & URL Quan Trọng

Dưới đây là các đường dẫn (URL) để Admin và Học viên tương tác với module Certificate:

### Dành Cho Quản Trị Viên (Admin)
- **Quản lý Template Chứng chỉ (Certificate Templates):**
  - Quản lý giao diện, bố cục, logo, chữ ký, nội dung mẫu.
  - Thêm mới template: `/admin/lms/certificate_template/add`

- **Quản lý Chứng chỉ đã cấp (Issued Certificates):**
  - Xem danh sách toàn bộ học viên đã được cấp chứng chỉ, mã code, và file PDF.
  - URL Quản lý các Entities (Views): `/admin/lms/certificates` hoặc `/admin/content/...` tùy nơi đặt link collection của entity này.
  
- **Thu hồi Chứng chỉ (Revoke Certificate):**
  - Thao tác nằm trong danh sách Quản lý Chứng chỉ đã cấp (Entity Operation links). Admin bấm vào nút **Revoke** của một chứng chỉ bất kỳ.
  - URL Pattern: `/admin/lms/certificates/{id}/revoke`
  - *Hành động này sẽ popup form điền lý do và đánh dấu trạng thái bị thu hồi (`field_revoked` = 1), khiến chứng chỉ lộ diện là KHÔNG HỢP LỆ trên trang Verify.*

### Dành Cho Bất Kỳ Ai (Public Verification)
- **Cổng Xác Thực Chứng Chỉ (Verification Portal):**
  - Bất kỳ ai (nhà tuyển dụng, học viên, đối tác) đều có thể truy cập để kiểm tra tính hợp lệ của chứng chỉ bằng cách nhập mã Code được in trên file PDF.
  - **URL truy cập:** `/certificate/verify`
  - Nếu mã tồn tại và hợp lệ: Hiển thị cờ `VALID CERTIFICATE`, tên học viên, khóa học, ngày cấp.
  - Nếu mã bị thu hồi: Hiển thị cờ `REVOKED` kèm theo lý do thu hồi (nếu tổ chức công bố).
  - Nếu mã không tồn tại: Báo lỗi không tìm thấy chứng chỉ rác.

## 3. Cấu trúc Thư mục Lưu Trữ PDF
Tất cả các file chứng chỉ PDF sau khi sinh ra từ engine `mPDF` sẽ được lưu trữ vật lý tại thư mục:
- Dấu vết file: `public://certificates/issued/`
- Tên file định dạng gốc: `Certificate_{Code}.pdf` (VD: `Certificate_E25630C463.pdf`).

## 4. Xử lý Lỗi Cơ Bản (Troubleshooting)
- **Không sinh được file PDF:** Đảm bảo thư viện mPDF đã có (chạy lệnh `ddev composer require mpdf/mpdf`).
- **Gặp lỗi wrapper (private://):** Server yêu cầu cấu hình Private File System cho Drupal. Module này đang ghi đè default xuống `public://` để dùng trong môi trường Local.
