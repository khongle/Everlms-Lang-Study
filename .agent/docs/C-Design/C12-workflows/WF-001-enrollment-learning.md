---
workflow: WF-001
slug: enrollment-learning
mode: as-built
b04_source: B04/WF-001-enrollment-learning.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-001 Enrollment & Learning

**Trigger:** Student accesses `/checkout/{course_id}` (paid) or clicks Enroll on a free course, or navigates directly to `/learn/{course}/{lesson}` (free deep-link auto-enroll).
**Outcome:** `lms_user_course` record exists for the student/course pair; student can navigate lessons and track progress through to `field_status=completed`.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-001-enrollment-learning.md`](../../B-Requirements/B04-business-workflows/WF-001-enrollment-learning.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Student (authenticated) | Steps 1–7 | `_user_is_logged_in: TRUE`; `access content` |
| System | Steps 2a, 3, 6 | — (runs in context of hook/controller) |
| Admin | Step 5 (bank transfer approval) | `administer lms_order` |
| Payment Gateway (Stripe / Bank Transfer) | Step 2b | — (external or manual) |

## Entry Points

| URL | Page (C11) | Method | Form | Feature Flag |
|---|---|---|---|---|
| `/checkout/{course_id}` | checkout | GET | `CheckoutForm` | — |
| `/checkout/bank-transfer/{lms_order}` | bank-transfer | GET+POST | `BankTransferForm` | — |
| `/learn/{lms_course}/{lms_lesson}` | lesson-view | GET | — | — |
| `/api/lms/progress/mark-complete` | — | POST (JSON) | — | — |
| `/dashboard` | dashboard | GET | — | — |
| `/my-courses` | my-courses | GET | — | — |

## Entities Touched

| Entity | Operation | Fields Written | State Transition |
|---|---|---|---|
| `lms_order` | CREATE | `field_order_number`, `field_items`, `field_total`, `field_status`, `field_payment_method`, `field_payment_id` | — → `pending` → `completed` |
| `lms_order_item` | CREATE | `field_course`, `field_price`, `field_type=course` | — |
| `lms_user_course` | CREATE / UPDATE | `uid`, `field_course`, `field_status`, `field_progress`, `field_enrolled_at`, `field_completed_at` | `enrolled` → `in_progress` → `completed` |
| `lms_user_lesson` | UPSERT | `uid`, `field_lesson`, `field_status`, `field_completed_at` | `not_started` → `completed` |
| `lms_notification` | CREATE | `uid`, `field_type=enrollment`, `field_channel=[in_app]`, `field_title`, `field_message` | — |

## State Transitions

```
lms_user_course.field_status:

  [not enrolled]
       │
       ▼  createAndFulfillFreeOrder() OR StripeGateway success OR admin approves bank transfer
   enrolled
       │
       ▼  first lessonView() / markComplete() POST
   in_progress
       │
       ▼  markComplete(): (completed_lessons / total_lessons) * 100 = 100%
   completed
```

State guards:

| Transition | Trigger | Guard | Side Effects |
|---|---|---|---|
| `[none] → enrolled` | `_lms_order_process_completed_order()` or `UserPortalController::lessonView()` auto-enroll | Order `field_status=completed` OR free course direct access | Creates `lms_notification` (in_app); sends `hook_mail` key `order_completed` |
| `enrolled → in_progress` | `ProgressApiController::markComplete()` when first lesson completed | `_user_is_logged_in`; `field_progress < 100` | Updates `lms_user_course.field_progress` |
| `in_progress → completed` | `ProgressApiController::markComplete()` when progress hits 100% | `completed_lessons = total_lessons` | Sets `field_completed_at = now()` |

## Modules Involved

| Module | Responsibility in This Workflow |
|---|---|
| `lms_order` | Checkout controller, order/order-item creation, `hook_entity_insert/update` enrollment trigger, email |
| `lms_payment` | Plugin host (`PaymentGatewayManager`); `BankTransferGateway` returns `pending` |
| `lms_stripe` | `StripeGateway::processPayment()` retrieves PaymentIntent, checks `status=succeeded` |
| `lms_progress` | `UserPortalController::lessonView()` (auto-enroll, render); `ProgressApiController::markComplete()` (progress update); `UserPortalController::dashboard()` / `myCourses()` |
| `lms_notification` | Creates `lms_notification` entity on enrollment (in_app only; email and push are stubs) |

## Permissions

| Step | Public (anon) | Student (auth) | Manager | Admin |
|---|---|---|---|---|
| 1. View course page | view (public route) | view | view | view |
| 2a. Free enroll | redirect to login | enroll via `createAndFulfillFreeOrder()` | same | same |
| 2b. Paid checkout | redirect to login | fill `CheckoutForm` | same | same |
| 3. Order fulfillment | — | automated on save | — | — |
| 4–6. Mark lesson complete | — | POST `/api/lms/progress/mark-complete` | same | same |
| 5 (bank). Approve order | — | — | — | `administer lms_order` |
| 7. View dashboard | — | own records only | own records only | all records |

## Notifications

| Trigger | Recipient | Channel | Template / Key |
|---|---|---|---|
| Order `completed` | Student (buyer) | email (hook_mail) | `order_completed` |
| Order `completed` | Student | in-app (lms_notification entity) | type=`enrollment` |
| Membership expires (see WF-005) | Student | email | `membership_expired` |
| Email/push for enrollment | Student | **email stub** — logs only; **push stub** — logs only | `lms_notification.NotificationManager` |

## Cache Invalidation

| Step | Cache Tag(s) | Search Index | List Views |
|---|---|---|---|
| Order completed → enrollment | — (no explicit cache tag invalidation in `_lms_order_process_completed_order`) | — | — |
| `markComplete()` | — (JSON API response; no Drupal cache tag) | — | — |
| `lessonView()` — note save | `lms_note:{lesson_id}:{uid}` (NoteController::save) | — | — |
| `myWishlist()` | `lms_wishlist:{uid}` | — | — |

> **Gap**: `lms_user_course` updates do not explicitly invalidate any cache tag. Dashboard renders are uncached (controller returns render array with no `#cache` definition seen in facts). Acceptable for now; add `lms_user_course_list:{uid}` tag if dashboard caching is introduced.

## Operations Queues

| Page | Filters | Owner Role |
|---|---|---|
| `/manage/orders` (OrderListBuilder) | status, student, date | `administer lms_order` |
| `/workspace/pricing` | status, search | `administer lms_course` |

## Editorial / Management Surfaces

| Entity / Content | Create/Edit UI | Role / Permission | State | Translation | Visible C11 Surfaces |
|---|---|---|---|---|---|
| `lms_order` | `/manage/orders/{id}` (edit-form, auto-generated) | `administer lms_order` | `field_status` list | — | `/manage/orders` |
| `lms_user_course` | No admin UI (managed programmatically) | `administer lms_course` (entity admin) | `field_status` list | — | — |

## Failure Modes

| Failure | Detection | Handling |
|---|---|---|
| Anonymous checkout | `CheckoutController` checks `$account->isAnonymous()` | Redirect to Drupal login page |
| Stripe `PaymentIntent::create()` fails on form build | PHP exception on `buildForm()` | Form render fails; student sees error page; each reload creates orphaned PaymentIntent (known bug) |
| Card declined (client-side) | Stripe.js inline error | No form POST submitted; student selects different card |
| `StripeGateway` returns `status=error` | `submitForm()` checks gateway response | Order not created; error message displayed |
| Bank transfer: receipt never uploaded | Order stays `pending` indefinitely | Admin contacts student; manual approval or cancellation |
| `hook_mail` failure | PHP mail error logged | Enrollment succeeds; email is best-effort |
| `markComplete` with invalid IDs | `ProgressApiController` returns `{success: false}` | Client retries |
| Membership-gated lesson, membership expired | `MembershipAccessSubscriber` catches `AccessDeniedHttpException` on `/learn/*` | Redirects to `lms_course.pricing` with warning |
| No webhook for Stripe/PayPal/ZaloPay | Webhook POST to non-existent route → 404 | Not recoverable; async payment events are lost |

## Acceptance (B05)

- [B05 acceptance criteria](../../B-Requirements/B05-acceptance-criteria/) — `lms_user_course` created with `field_status=enrolled`; all lessons completed → `field_status=completed`, `field_progress=100`; dashboard resolves next-lesson URL

## Known Bugs (as-built)

| Bug | Location | Notes |
|---|---|---|
| `PaymentIntent::create()` on every `buildForm()` | `lms_order/src/Form/CheckoutForm.php`, `MembershipCheckoutForm.php` | Creates orphaned Stripe PaymentIntents on partial loads; move to `submitForm()` |
| `createAndFulfillFreeMembershipOrder()` double `set('field_items')` | `lms_order/src/Controller/CheckoutController.php` | Second call overwrites first; functional but wasteful |
| `lms_progress_comment_form_submit_redirect()` undefined `$response` | `lms_progress.module` | PHP warning on non-AJAX path when no lesson found |
| `hook_form_alter` for comments mutates `authenticated` Role entity | `lms_progress.module` | Grants all text formats to authenticated at form-alter time; security/performance concern |
