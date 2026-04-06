# Kế hoạch Nâng cấp Giao diện Trình dựng Chứng chỉ (Certificate Builder UI)

Mục tiêu là cải tiến UI của form chỉnh sửa `CertificateTemplate` để hỗ trợ nhập trực tiếp mã Twig/HTML thô và có tính năng Xem trước trực tiếp (Live Preview) giống như bản vẽ thiết kế, đồng thời tuân thủ chặt chẽ Data Model ban đầu. (Mã nguồn và comments bên trong code sẽ được viết hoàn toàn bằng tiếng Anh).

## Các thay đổi đề xuất

### lms_certificate
#### [MODIFY] lms_certificate/src/Entity/CertificateTemplate.php
- **TUYỆT ĐỐI KHÔNG XÓA** các trường định nghĩa kiến trúc ban đầu: `field_background`, `field_logo`, `field_signature`, `field_orientation`, `field_color_scheme`.
- Tái sử dụng trường `field_layout` (hiện là `text_long`) để lưu mã Raw HTML/Twig thay vì cấu hình JSON, và gán nhãn lại thành "Twig Template" trên form.
- Chỉnh sửa form class tham chiếu về class tuỳ chỉnh: `\Drupal\lms_certificate\Form\CertificateTemplateForm`.

#### [NEW] lms_certificate/src/Form/CertificateTemplateForm.php
- Tạo class form quản trị mới `CertificateTemplateForm` kế thừa từ `ContentEntityForm`.
- Điều chỉnh giao diện Form:
  - Tích hợp #ajax vào trường nhập liệu Twig Template (`field_layout`) để bắt sự kiện thay đổi nội dung.
  - Gắn một khối "Xem trước" (Preview Container dạng `#markup` hoặc `inline_template`) ở phần dưới cùng của form để tự động hiển thị trực quan đoạn mã HTML người dùng nhập vào theo thời gian thực.
  
#### [MODIFY] lms_certificate/templates/certificate-pdf.html.twig
- Loại bỏ các thẻ `<div>` bao bọc và code HTML bị đóng cứng (hardcode) trong file gốc.
- Đổi thành chỉ render thuần túy mã được lưu trữ trong database (`{{ template.field_layout.value|replace(placeholders)|raw }}`) để nhường lại 100% quyền tuỳ biến cho Admin.

## Kế hoạch Kiểm thử (Verification Plan)

### Kiểm thử Thủ công (Manual Verification)
1. Truy cập vào đường dẫn: `/manage/certificates/add`.
2. Quan sát giao diện Admin sẽ thấy ô nhập "Twig Template" phía trên và khung "Template Default" (Preview) ngay bên dưới.
3. Chèn một đoạn mã HTML chứng chỉ mẫu vào ô nhập liệu.
4. Xác nhận rằng khối Preview ở dưới tự động nhận diện và thay đổi giao diện trực quan theo đúng cấu trúc mã vừa nhập.
