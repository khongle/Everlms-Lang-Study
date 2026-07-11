---
usecase_id: UC-WF-003-001
workflow: WF-003-certificate-issuance
status: as-built
primary_actor: System
modules: [lms_certificate, lms_quiz]
processes: [certificate-issuance]
---

# UC-WF-003-001 - Issue certificate on pass

## Intent
Khi course/quiz pass, tự động issue một certificate dạng PDF.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-003 |
| Business flow | pass → render → store → notify |
| Modules | lms_certificate, lms_quiz |
| Related features | F-007 |
| B04 source | WF-003 |

## Actors
System (event subscriber); learner (download); manager (revoke).

## Preconditions
Learner đã pass final quiz của course; quiz có `field_course` hoặc một đường lesson hierarchy dẫn tới course của nó; đã tồn tại một `lms_certificate_template`.

## Trigger
`QuizPassedEvent` / course completion.

## Main success scenario
1. Subscriber kiểm tra điều kiện đủ điều kiện + chưa có cert nào tồn tại (idempotent).
2. Resolve course từ `lms_quiz.field_course`; fallback qua hệ thống phân cấp lesson/chapter/course với quiz nhúng (embedded).
3. Render template thành PDF (mPDF) kèm learner/course/date/code.
4. Tạo `lms_user_certificate` (`field_pdf_file`, `field_certificate_number`, `field_quiz_result`).
5. Notify learner; learner tải về (P023).
6. Public verifier xác nhận tính hợp lệ theo certificate code.

## Alternate and exception flows
- Đã issue rồi → không tạo duplicate.
- Render lỗi → log + retry; không tạo cert dở dang.
- Quiz pass nhưng không link tới course → không issue và phải coi đây là lỗi dữ liệu/cấu hình.

## State transitions (evidence)
- `approved_or_completed` → `rejected_or_returned`: manager revoke kèm lý do.
- `approved_or_completed` → `archived`: certificate hết hạn qua field_expiry_date.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| My certificates | App | /my-certificates |
| Verify | Public | /certificate/verify |

## Supporting pages
Public verifier ở Certificate verify (P015) resolve theo certificate number. Content editor duy trì layout tại Certificate templates (P040); manager theo dõi toàn bộ certificate đã issue, kể cả revoke/regenerate, tại Issued certificates (P041).

## Acceptance and test contract
- Test issuance-on-pass + idempotency (B05 F-007); verify code resolve đúng; nhiều attempt passed cho cùng learner/course vẫn chỉ tạo ra một certificate.
