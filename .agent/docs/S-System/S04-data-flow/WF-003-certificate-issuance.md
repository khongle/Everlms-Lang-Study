# S04 Data Flow - WF-003 Certificate issuance

```text
QuizPassedEvent (from WF-002)
   ▼
lms_certificate subscriber: eligibility + idempotency check ──▶ db (lms_user_certificate?)
   │ select lms_certificate_template
   ▼
mPDF render (server) ──▶ PDF file (file storage)
   │ create lms_user_certificate (field_pdf_file, field_certificate_number)
   ▼
db-mariadb; notify learner (lms_notification)
   │ download (P023) / public verify (P015)
```
Source: [C12 WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md). External: none (mPDF is in-process); email link via INT-008 (partial).
