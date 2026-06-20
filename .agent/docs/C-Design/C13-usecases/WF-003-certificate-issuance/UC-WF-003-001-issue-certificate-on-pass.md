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
On course/quiz pass, automatically issue a PDF certificate.

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
Learner passed the course's final quiz; a `lms_certificate_template` exists.

## Trigger
`QuizPassedEvent` / course completion.

## Main success scenario
1. Subscriber checks eligibility + no existing cert (idempotent).
2. Render template to PDF (mPDF) with learner/course/date/code.
3. Create `lms_user_certificate` (`field_pdf_file`, `field_certificate_number`).
4. Notify learner; learner downloads (P023).

## Alternate and exception flows
- Already issued → no duplicate.
- Render failure → log + retry; no partial cert.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| My certificates | App | /my-certificates |
| Verify | Public | /certificate/verify |

## Acceptance and test contract
- Issuance-on-pass + idempotency tests (B05 F-007); verify code resolves.
