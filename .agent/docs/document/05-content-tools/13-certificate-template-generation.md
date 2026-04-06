# Kế hoạch Triển khai: Certificate Template Generation & Course Mapping (Cách A)

## Bối cảnh & Vấn đề Cốt lõi
Hệ thống cấp chứng chỉ của EverLMS sử dụng `mPDF` để in từ HTML ra PDF thực. Quá trình in tuân theo flow sau:
1. `mPDF` nhận dữ liệu HTML từ file `certificate-pdf.html.twig`.
2. File Twig này **không chứa** giao diện tĩnh, mà chỉ làm nhiệm vụ in biến `template.field_layout.value` dưới dạng chuỗi `raw`.
3. Biến `field_layout` mặc định kỳ vọng Admin cung cấp toàn bộ chuỗi HTML (có chèn sẵn token `[student_name]`, `[course_name]`, v.v.) qua giao diện cấu hình backend.

**Điểm nghẽn ở hệ thống Tự động hóa (AI Seeder):**
Content Generator sinh file JSON `certificate-templates.json` có chứa dữ liệu `body_text`, `issuer`, `signature_label`. Lệnh Drush `everlms:import` đang bỏ trống field `field_layout` khiến mọi chứng chỉ render ra đều là **Trang trắng 100%**. Quá trình import cũng chưa gán (map) Mẫu Chứng Chỉ vào từng khóa học (`lms_course`).

---

## Cách Tiếp cận Mới (Approach A - HTML Blueprint Injection)

Thay vì thiết kế lại toàn bộ Entity `lms_certificate_template` (`Cách B`), chúng ta sẽ ứng dụng kỹ thuật **HTML Blueprint Injection** vào bước Drush Import:

1. **Bảo toàn Entity hiện tại:** Field `field_layout` tiếp tục đóng vai trò là nơi lưu trữ giao diện WYSIWYG tự do cho Admin chỉnh sửa sau này.
2. **Dùng Khung HTML Mẫu mPDF (Blueprint):** Viết sẵn một số file cấu trúc HTML thô (Blueprint) bên trong module `lms_content_tools` (Code thuần bằng `<table width="100%">` phù hợp 100% chuẩn khắc nghiệt của mPDF). Xin lưu ý mPDF KHÔNG hiểu `flexbox` hoặc `grid`. 
3. **Bơm dữ liệu JSON vào Blueprint:** Khi chạy lệnh `importCertificates()`, chúng ta đọc file Blueprint này bằng PHP, nạp (Inject) các dữ liệu JSON AI đã sinh (`body_text`, `issuer`) vào đúng những ô vị trí còn khuyết trong file Blueprint.
4. **Lưu Blueprint thành Chuỗi:** Cả khối string HTML khổng lồ đã được nạp dữ liệu này sẽ được save gọn gàng vào text-area của field `field_layout`.

=> **Lợi ích:** Module mPDF sau đó render bình thường mà không hề biết HTML này là từ Admin nhập hay từ AI Seeder tự động Inject. Admin sau này lên Backend thấy giao diện WYSIWYG Edit rất tự do.

---

## Chi tiết Triển khai Step-by-Step

### 1. Xây dựng "HTML Blueprint" mPDF Compatible
- Tạo thư mục lưu trữ mẫu tĩnh: `web/modules/custom/lms_content_tools/src/Blueprints/`
- Tạo file `modern_certificate.html` (chỉ chứa thẻ HTML gốc và Table layout).
- Đặt sẵn các móc nối (Hook tags) để PHP thực hiện Replace như: `%body_text%`, `%issuer%`, `%signature_label%`.

### 2. Cập nhật `ContentToolsCommands::importCertificates()`
Sửa code lệnh Drush để nó nối dữ liệu:
```php
$blueprint_html = file_get_contents(__DIR__ . '/../Blueprints/modern_certificate.html');
$injected_html = str_replace(
  ['%body_text%', '%issuer%', '%signature_label%'],
  [$templateData['body_text'], $templateData['issuer'], $templateData['signature_label']],
  $blueprint_html
);

// Save vào Entity
$template->set('field_layout', $injected_html);
```

### 3. Nâng cấp Config Entity `lms_course` & Course Mapping
Do hệ thống hiện tại chưa liên kết Course và Template gốc.
- Ghi đè file config của `lms_course` để bổ sung Reference Field (Entity Refernce) tên là `field_certificate_template` (target_type: `lms_certificate_template`).
- Trong hàm `ContentToolsCommands::importCourse()`, kiểm tra thuộc tính json `$courseData['has_certificate']`. Nếu Course có dùng chứng chỉ, truy tìm xem Template nào phù hợp nhất (dựa trên keyword/tên) và set `target_id` đó vào `field_certificate`.

### 4. Sửa logic cấp chứng chỉ `CertificateManager::generate()`
Thay thế đoạn fallback cứng (ngẫu nhiên lấy Active Template đầu tiên) bằng logic lấy chính xác Template đã cấu hình trên khóa học:
```php
$template_id = $course->get('field_certificate_template')->target_id;
if ($template_id) {
  $template = $this->entityTypeManager->getStorage('lms_certificate_template')->load($template_id);
} else {
  // Lấy Global Array theo fallback...
}
```

### 5. Script Khởi tạo Dữ liệu Giả lập (Mock Generator)
Tạo script `fix_certificates.php` bên trong thư mục `scripts`. Script này có nhiệm vụ:
- Duyệt qua toàn bộ Học viên hiện có.
- Gửi lệnh Mock "Học viên A hoàn thành Khóa học X" (Trigger event hoặc call thẳng Service mPDF) để test việc Render.
- Sinh ra hàng loạt chứng chỉ ảo với ảnh PDF vật lý ở `public://certificates/issued` và ghi Log xuống database Entity `lms_user_certificate`.
- **Mục tiêu:** Nhờ data ảo này, chúng ta mới có thể lập trình được bảng **Data Table (View, Revoke)** khi bắt tay vào xây dựng giao diện **Manager Dashboard > Certificates UI**.
