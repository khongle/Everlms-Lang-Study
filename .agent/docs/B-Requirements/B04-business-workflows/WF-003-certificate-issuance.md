# WF-003 - Certificate issuance

## Business flow
Khi learner hoàn thành một course / pass quiz cuối, một subscriber lắng nghe `QuizPassedEvent` (hoặc course-completion) sẽ kiểm tra điều kiện đủ điều kiện, chọn `lms_certificate_template` của course, render ra PDF qua mPDF, và tạo một bản ghi `lms_user_certificate` với mã xác thực (verification code) duy nhất. Việc cấp certificate có tính **idempotent** — learner pass lại quiz sẽ không nhận certificate trùng lặp. Learner được thông báo (qua WF liên quan F-017) và có thể tải PDF; editor có thể render lại qua `lms:mock-certs` / `scripts/regenerate_all_certs.php`.

## Participating modules
[lms_certificate](../../C-Design/C05-modules/lms_certificate.md), [lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Related features (B03)
[F-007](../B03-features/F-007-certificate-issuance.md)

## Trigger
`QuizPassedEvent` / course hoàn thành.

## Outcome
Một file PDF `lms_user_certificate` duy nhất được cấp cho mỗi learner-per-course; learner được thông báo.

## Design link
[C12 WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md)
