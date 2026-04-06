# Certificate Module - Data Model

## 1. Entity: `lms_certificate` (Cấu hình / Template)
Lưu trữ các mẫu chứng chỉ do Admin cấu hình.
- **name / title**: Tên mẫu chứng chỉ.
- **field_background**: File ảnh nền (PNG/JPG).
- **field_logo**: Ảnh Logo tổ chức.
- **field_signature**: Ảnh chữ ký.
- **field_orientation**: Hướng in (Portrait / Landscape).
- **field_layout**: JSON data lưu cấu hình tọa độ layout và placeholder.
- **field_color_scheme**: Mã màu (Hex) dùng làm accent.
- **status**: Trạng thái (Active/Inactive).

## 2. Entity: `lms_user_certificate` (Chứng chỉ đã cấp)
Lưu thông tin chứng chỉ được cấp cho học viên.
- **field_certificate**: Nguồn Template (`lms_certificate`).
- **field_holder**: Reference tới User.
- **field_course**: Reference tới Course.
- **field_quiz_result**: Reference tới `lms_quiz_result` (nếu có thi).
- **field_certificate_id**: Mã định danh (VD: `ELMS-{PREFIX}-{RANDOM}-{INITIALS}`).
- **field_pdf_link**: Link tới file PDF sinh ra do DomPDF/mPDF.
- **field_issued_date**: Ngày cấp.
- **field_expiry_date**: Ngày hết hạn (null = lifetime).
- **field_revoked**: (Boolean) Bị thu hồi.
- **field_revoke_reason**: (String) Lý do thu hồi.

## 3. Taxonomy: `certificate_categories` (Tùy chọn)
Dùng để phân loại loại chứng chỉ (VD: Completion, Excellence, Participation).
