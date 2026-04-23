# S04 — Data Flow

> Mô tả các critical-path flows end-to-end trong EverLMS runtime.
> Mỗi flow là một file riêng, tracking từ trigger đến outcome.

---

## Critical Flows

| File | Flow | Trigger | Outcome | Critical? |
|------|------|---------|---------|-----------|
| `01-course-enrollment-flow.md` | Course Enrollment | User clicks "Enroll" / completes checkout | User enrolled, progress record created | Yes (revenue + access) |
| `02-quiz-submission-flow.md` | Quiz Submission | User submits quiz | Quiz graded, progress updated, certificate triggered if passed | Yes (learning outcome) |
| `03-payment-webhook-flow.md` | Payment Webhook | Stripe POST to `/payment/webhook/stripe` | Order fulfilled, enrollment activated | Yes (revenue) |

---

## Flow Notation

- **Actor:** Who or what initiates the step (User, Controller, Service, External).
- **Success path:** Happy path step sequence.
- **Failure mode:** What happens when a step fails.
- **Implemented?** Whether the step exists in current code or is proposed.

---

## Common Failure Assumptions

- Database unavailable → all flows fail; no queue fallback.
- External service unavailable → payment and AI flows degrade; core LMS (content viewing) continues.
- Cron not running → AI queue jobs stall; notifications delayed; no user-visible degradation for core flows.
