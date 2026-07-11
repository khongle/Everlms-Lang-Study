# F-007 Acceptance Criteria - Certificate issuance

## Core criteria
1. **Given** learner pass final quiz của course, **when** `QuizPassedEvent` được kích hoạt, **then** một `lms_user_certificate` được tạo và một PDF được render từ `lms_certificate_template` của course.
2. **Given** một certificate đã được phát hành, **when** learner pass lại, **then** **không tạo certificate trùng lặp** (idempotent).
3. **Given** một certificate đã phát hành, **when** learner mở mục certificate của mình, **then** họ có thể tải PDF hiển thị tên, course, ngày, và mã unique.
4. **Given** editor thay đổi certificate template, **when** certificate mới được phát hành, **then** chúng dùng layout đã cập nhật (không cần đổi code).

## Evidence expected
- Module: [lms_certificate](../../C-Design/C05-modules/lms_certificate.md); render bằng mPDF.
- Test: issuance-on-pass kernel test; idempotency test; template-edit smoke; `lms:mock-certs` cho fixture (E03).
