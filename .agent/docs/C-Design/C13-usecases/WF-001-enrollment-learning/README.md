# WF-001 — Enrollment & Learning — Use Case Folder

**Workflow:** WF-001 Enrollment & Learning  
**Source workflow doc:** [`../../C12-workflows/`](../../C12-workflows/)  
**Business workflow:** [`../../../../B-Requirements/B04-business-workflows/WF-001-enrollment-learning.md`](../../../B-Requirements/B04-business-workflows/WF-001-enrollment-learning.md)  
**Implementing modules:** `lms_order`, `lms_progress`, `lms_membership`, `lms_notification`  

---

## Use cases in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-001-01-paid-course-stripe.md](UC-001-01-paid-course-stripe.md) | UC-001-01 | happy | Student purchases a paid course via Stripe and is auto-enrolled |
| [UC-001-02-free-course-auto-enroll.md](UC-001-02-free-course-auto-enroll.md) | UC-001-02 | happy | Student visits a free course checkout and is immediately enrolled |
| [UC-001-03-anon-checkout-redirect.md](UC-001-03-anon-checkout-redirect.md) | UC-001-03 | permission | Anonymous visitor hits `/checkout` and is redirected to login |
| [UC-001-04-bank-transfer-pending.md](UC-001-04-bank-transfer-pending.md) | UC-001-04 | edge | Bank transfer checkout leaves order in `pending` — no auto-enrollment |

---

## Implementation status

| Step | Status |
|------|--------|
| Free course instant enroll | as-built |
| Stripe synchronous enroll | as-built |
| Bank transfer pending order | as-built |
| Anonymous redirect | as-built |
| Webhook async enroll (Stripe/PayPal/ZaloPay) | **not implemented** — no webhook routes exist |
| Membership plan checkout | as-built (free/Stripe path) |
