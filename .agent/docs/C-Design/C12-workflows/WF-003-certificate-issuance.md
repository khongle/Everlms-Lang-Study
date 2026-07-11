# C12 Workflow - WF-003 Certificate issuance

**Object:** certificate

## Business source
[B04 WF-003](../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md)

## Modules
[lms_certificate](../C05-modules/lms_certificate.md), [lms_quiz](../C05-modules/lms_quiz.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md)

## State model
`lms_user_certificate`:
| State | Meaning |
| ----- | ------- |
| approved_or_completed (issued) | rendered + stored (idempotent) |
| rejected_or_returned (revoked) | `field_revoked` set with reason |
| archived (expired) | past `field_expiry_date` |

## Issuance invariant
- `QuizPassedEvent` phải resolve được course sở hữu trước khi issue certificate.
- Nguồn course chính là `lms_quiz.field_course`.
- Fallback legacy là `lms_lesson.field_quiz → lms_chapter.field_lessons → lms_course.field_chapters`.
- Việc issue là idempotent theo learner/course: một learner có thể có nhiều attempt passed nhưng chỉ có một `lms_user_certificate` đang active cho course đó.
- Public verification resolve theo `field_certificate_number` và phải hiển thị các certificate đã revoke là invalid.

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| approved_or_completed | rejected_or_returned | manager revoke kèm lý do |
| approved_or_completed | archived | certificate hết hạn qua field_expiry_date |

## Permission model
- Initiator: hệ thống (event subscriber trên `QuizPassedEvent`/completion).
- Admin: manager có thể revoke/regenerate.

## As-built issuance, verification & revoke (round-2)
- Việc issue được kích hoạt từ `QuizPassedEvent` (`CertificateEventListener`), resolve course từ `lms_quiz.field_course` (fallback legacy qua lesson→chapter→course), render PDF (mPDF) vào `field_pdf_file`, và lưu `field_certificate_number`. Đã verify: grading result 98 issue cert `FDB2B9BA40` kèm PDF, idempotent (chỉ một dòng cho mỗi learner/course).
- **Public verifier** `/certificate/verify` (`VerificationForm`, field `certificate_code`) resolve theo `field_certificate_number`, hiển thị họ tên đầy đủ của recipient, course, code, ngày issue, và đánh dấu các certificate `field_revoked` là **invalid**.
- **Revoke** `/workspace/certificates/{id}/revoke` (`RevokeCertificateForm`, `administer lms_course`) set `field_revoked` + lý do rồi quay về `/workspace/certificates`. **Regenerate** sẽ render lại PDF.
- Danh sách learner tại `/my-certificates` hiển thị từng certificate kèm số, ngày issue, và link tải PDF.
- Tên hiển thị ở mọi nơi đều dùng `field_first_name`/`field_last_name` qua `lms_base_user_format_name_alter()` (không dùng login username).

## Notifications
Notification khi issue + link download gửi learner (bảo vệ theo role). Idempotent: pass lại không tạo duplicate.

## Round-2 QA evidence
Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/003-certificate-issuance/`. Các issue đã fix: `WF-003-001..004` (tên hiển thị, quyền revoke, revoke lỗi 500, nhãn thống kê).
