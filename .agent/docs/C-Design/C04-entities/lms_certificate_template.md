# C04 Entity - lms_certificate_template

**Display name:** Certificate Template
**Owning module(s):** [lms_certificate](../C05-modules/lms_certificate.md)
**Mode:** as-built (Content Entity — editor-managed layout)

## Purpose
Layout/design có thể chỉnh sửa, được render thành PDF khi certificate được issue. Branding (logo, chữ ký, con dấu, màu sắc, hướng trang) do editor quản lý.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | template name |
| field_body | formatted certificate body/copy |
| field_design, field_layout, field_orientation, field_color_scheme | layout/design controls |
| field_background, field_logo, field_signature, field_seal | image assets (target_id + alt/title/dimensions) |
| status | active |

## Relationships
- Được tham chiếu bởi `lms_user_certificate.field_template`.

## Validation rules
Nguồn: `web/modules/custom/lms_certificate/src/Entity/CertificateTemplate.php`.
- `name`: bắt buộc, tối đa 255 ký tự.
- `field_design`: `list_string`, giá trị hợp lệ giới hạn trong `classic|modern|elegant|minimal`, mặc định `classic`.
- `field_orientation`: `list_string`, giá trị hợp lệ `landscape|portrait`, mặc định `landscape`.
- `field_background`, `field_signature`, `field_logo`, `field_seal`: image field, không giới hạn `file_extensions` trong code (khác với `lms_article.thumbnail` có giới hạn) — gap nhỏ, phụ thuộc cấu hình field UI ở config.
- Không field nào khác được đánh dấu `setRequired(TRUE)` ngoài `name`.

## Data invariants
- `CertificateManager::issue()` (`lms_certificate/src/Service/CertificateManager.php`) chọn template theo `status = 1` đầu tiên tìm thấy khi course không chỉ định `field_certificate_template` cụ thể — ngụ ý nghiệp vụ "nên có đúng 1 template active mặc định", nhưng **không có ràng buộc unique/exclusive nào enforce** việc chỉ 1 template có `status = TRUE` — nếu nhiều template active, hệ thống lấy template đầu tiên theo thứ tự query (không xác định rõ ràng).
- `field_body` dùng token `[student_name]`, `[course_name]`, `[issue_date]` — đây là hợp đồng ngầm giữa nội dung field và generator, không được validate cú pháp token khi lưu.

## Calculations & derived values
- Không có trường tính toán trên chính entity này; toàn bộ là layout/config tĩnh do editor nhập.
- Khi issue certificate, generator (`CertificateManager`) đọc các field này để render PDF — quá trình render diễn ra ở `lms_user_certificate`, không ghi giá trị dẫn xuất ngược lại `lms_certificate_template`.

## Editorial/data management
Content editor/manager chỉnh sửa template qua template entity form (management UI) — đây là điểm neo về editorial-usability cho certificate. Toàn bộ nội dung/branding nằm trong field; mPDF thực hiện render. Thay đổi một template sẽ ảnh hưởng đến các certificate mới issue (re-render qua `regenerate_all_certs.php`). Xem [F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md).
