# C04 Entity - lms_user_certificate

**Display name:** User Certificate (issued)
**Owning module(s):** [lms_certificate](../C05-modules/lms_certificate.md)
**Mode:** as-built (Content Entity)

## Purpose
Một instance certificate đã issue cho một cặp learner-course, gồm PDF đã render, một số hiệu duy nhất, thời hạn hiệu lực, và trạng thái revocation.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | recipient |
| field_template | ref → lms_certificate_template |
| field_course | ref → lms_course |
| field_quiz_result | ref → lms_quiz_result (the passing attempt) |
| field_certificate_number | unique verification code |
| field_pdf_file | rendered PDF (file) |
| field_issued_at, field_expiry_date | validity window |
| field_revoked, field_revoke_reason | revocation |

## Relationships
- N→1 `user`, `lms_course`, `lms_certificate_template`, `lms_quiz_result`.

## Validation rules
Nguồn: `web/modules/custom/lms_certificate/src/Entity/UserCertificate.php`.
- `field_template`: bắt buộc, entity_reference tới `lms_certificate_template`.
- `field_certificate_number`: string, tối đa 32 ký tự, mô tả "unique verification code" (Baseline fields) nhưng **`setRequired()`/unique constraint không được khai báo ở field-definition** — tính duy nhất chỉ đến từ thuật toán sinh mã (xem Calculations), không có unique index DB tường minh trong entity definition.
- `field_pdf_file`: file field, giới hạn `file_extensions` = `pdf`.

## Data invariants
- `field_revoked = TRUE` về nghiệp vụ nên đi kèm `field_revoke_reason` có nội dung, nhưng ràng buộc chéo này **không được enforce** trong `baseFieldDefinitions()` — có thể revoke mà không ghi lý do (gap as-built; `RevokeCertificateForm` có thể tự chặn ở tầng form, chưa được xác nhận trong phạm vi rà soát này).
- Idempotency "một certificate cho mỗi cặp learner-course" (ghi ở Editorial/data management) được đảm bảo bởi logic của `QuizPassedEvent` subscriber ở tầng service, KHÔNG phải bởi ràng buộc unique (uid, field_course) ở entity/DB level.

## Calculations & derived values
- Xác nhận trong `web/modules/custom/lms_certificate/src/Service/CertificateManager.php`: `field_certificate_number = strtoupper(substr(md5($user->id() . '-' . $course->id() . '-' . time()), 0, 10))` — mã 10 ký tự hex viết hoa, phụ thuộc uid + course id + timestamp giây; về lý thuyết có xác suất (rất nhỏ) trùng mã nếu cùng user+course được cấp lại đúng trong cùng 1 giây — không có retry/kiểm tra trùng trước khi lưu.
- `field_issued_at`: field type `created`, tự nhận request time khi entity save.
- `field_pdf_file` được `CertificateManager::generator->generate($template, $user, $course, $code)` render từ `lms_certificate_template.field_body`/`field_layout` (Twig) và các token `[student_name]`, `[course_name]`, `[issue_date]` — file kết quả gán vào field, không tính lại nếu template gốc thay đổi sau đó (trừ khi chạy `regenerate_all_certs.php`, đã ghi ở `lms_certificate_template.md`).

## Editorial/data management
Được tạo bởi `QuizPassedEvent` subscriber (idempotent — một certificate cho mỗi cặp learner-course). ~24 certificate thực tế. Manager có thể revoke. Xem [WF-003](../C12-workflows/WF-003-certificate-issuance.md).
