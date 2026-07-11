# F-007 - Certificate issuance

**Status:** built
**Primary module:** [lms_certificate](../../C-Design/C05-modules/lms_certificate.md)
**Epic:** [E05 Certification](../../D-Development/D02-epics/E05-certification.md)
**Related workflows:** [WF-003 Certificate issuance](../B04-business-workflows/WF-003-certificate-issuance.md)
**Acceptance:** [B05 F-007](../B05-acceptance-criteria/F-007-certificate-issuance.md)
**Source:** `web/modules/custom/lms_certificate/`

## Requirement
Khi course completion / quiz pass, hệ thống tự động issue một certificate: một event subscriber lắng nghe `QuizPassedEvent` (hoặc course completion) render một `lms_certificate_template` ra PDF (mPDF) và lưu một record `lms_user_certificate` kèm mã unique. Learner tải certificate của mình về; editor quản lý template mà không cần code.

## Business value
Certificate là kết quả hữu hình mà learner bỏ tiền ra mua — bằng chứng hoàn thành cho bối cảnh compliance/an toàn lao động; việc chỉnh sửa template giữ quyền kiểm soát branding trong tay editor.

## Scope notes
- `lms_certificate_template` (content entity kiểu config) chứa layout có thể chỉnh sửa; `lms_user_certificate` là instance đã được issue.
- Việc issuance phải **idempotent** (không tạo cert trùng khi pass lại) — đây là một NFR về reliability.
- `lms:mock-certs` và `scripts/regenerate_all_certs.php` hỗ trợ (re)generation hàng loạt.

## Cross references
- Design: [C04 lms_certificate_template](../../C-Design/C04-entities/lms_certificate_template.md), [C04 lms_user_certificate](../../C-Design/C04-entities/lms_user_certificate.md), [C12 WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md)
