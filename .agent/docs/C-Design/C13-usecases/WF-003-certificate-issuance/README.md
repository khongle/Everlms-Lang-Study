# C13 Usecases - WF-003 Certificate issuance

## Workflow
- C12: [WF-003](../../C12-workflows/WF-003-certificate-issuance.md) · B04: [WF-003](../../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md)
- Modules: lms_certificate, lms_quiz
- Business flow: pass → render PDF → store (idempotent) → notify.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-003-001](UC-WF-003-001-issue-certificate-on-pass.md) | Issue certificate on pass | System | Certificate issuance | P023 |

## Completion rule
Đóng khi việc pass một final quiz gắn với course tạo ra đúng một certificate có thể tải về và verify công khai.
