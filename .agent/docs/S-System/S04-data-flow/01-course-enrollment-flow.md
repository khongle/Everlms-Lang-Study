# Flow: Course Enrollment

**Trigger:** User (anonymous or authenticated) initiates course enrollment.
**Outcome:** User is enrolled in the course; `lms_user_course` progress record created; access granted.
**Critical path?** Yes — tied to revenue and learning access.
**Owner:** hainn (modules: `lms_order`, `lms_payment`, `lms_stripe`, `lms_progress`)

---

## Happy Path Steps

| # | Actor | Action | Implemented? | On Failure |
|---|-------|--------|-------------|-----------|
| 1 | Browser | User clicks "Enroll" on course page | Yes | N/A |
| 2 | Browser | If anonymous → redirect to `/user/login`; after login → redirect back | Yes (Drupal core) | User stays on login page |
| 3 | Browser | If course is free → direct enrollment (skip payment) | Yes (assumed) | Log error, show message |
| 4 | Browser | If course is paid → redirect to `GET /checkout/{course_id}` | Yes (`lms_order`) | 404 if no checkout route |
| 5 | CheckoutController | Render checkout form (`CheckoutForm`) with payment options | Yes | 500 → user sees error page |
| 6 | User | Select payment method (Stripe / PayPal / Bank Transfer) | Yes | Stay on form |
| 7 | Browser | Submit checkout form → POST to checkout handler | Yes | Validation error shown |
| 8 | CheckoutController | Create `lms_order` entity (status: `pending`) | Yes (`lms_order`) | Rollback, show error |
| 9 | StripeGateway | Call Stripe PaymentIntent retrieve to confirm payment | Yes (`lms_stripe`) | Error returned to user |
| 10 | PaymentManager | On payment success → update order status to `completed` | Yes (`lms_payment`) | Order stays `pending` |
| 11 | lms_progress | Create `lms_user_course` entity (status: `enrolled`) | Assumed | Enrollment lost — manual fix needed |
| 12 | lms_notification | Dispatch `CourseEnrolledEvent` → send confirmation email | Yes (event) | Log + retry via cron |
| 13 | Browser | Redirect user to course page — now has access | Yes | Show error page |

---

## Flow for Free Courses

```
User clicks Enroll
     → [is authenticated?]
          No  → Login → return
          Yes → [is free?]
                    Yes → Create lms_user_course (enrolled) → Access granted
                    No  → Checkout flow (steps 4–13 above)
```

---

## Key Entities Created / Updated

| Entity | Action | Status |
|--------|--------|--------|
| `lms_order` | Created (pending → completed) | Yes |
| `lms_order_item` | Created (links order to course) | Yes |
| `lms_user_course` | Created (enrolled status, 0% progress) | Assumed |

---

## Failure Modes

| Failure | Impact | Recovery |
|---------|--------|---------|
| Stripe API unreachable | Payment fails, order stays `pending` | User retries; manual admin intervention |
| DB write fails on order create | User sees 500 | No orphaned payment — PaymentIntent not captured yet |
| Progress record creation fails | User paid but has no access | **Critical gap** — manual admin fix required |
| Email notification fails | User enrolled but gets no email | Logged; cron retries |

---

## Open Issues

- No atomic transaction wraps order creation + progress creation. Payment success + progress failure = silent data inconsistency.
- No idempotency guard on enrollment — double-submit could create duplicate orders.
