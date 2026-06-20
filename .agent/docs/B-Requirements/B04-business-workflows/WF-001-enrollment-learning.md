---
id: WF-001
name: Enrollment & Learning
status: APPROVED
features: [F-001, F-003, F-004]
---

# WF-001 — Enrollment & Learning

End-to-end flow from a student accessing a course through lesson completion and progress tracking. Covers both free and paid enrollment paths.

## Trigger

Student accesses a course detail page and initiates enrollment (free) or purchase (paid).

## Actors

| Actor | Role |
|-------|------|
| Student (authenticated) | Primary learner |
| System | Auto-enrollment, progress, notification dispatch |
| Payment Gateway | Stripe / bank-transfer (for paid courses) |

## Preconditions

- Student is authenticated (`_user_is_logged_in: TRUE`).
- Course entity exists and is published (`status=1`).
- For paid courses: at least one active payment gateway is enabled in `lms_payment.settings`.

## Happy Path

| Step | Actor | Action | System Response | Modules / Code | Feature |
|------|-------|--------|----------------|----------------|---------|
| 1 | Student | Navigates to course detail page | Course entity rendered with curriculum (chapters + lessons) | `lms_course` | F-001 |
| 2a | Student | Clicks "Enroll" on a **free** course | `CheckoutController::createAndFulfillFreeOrder()` creates `lms_order` (status=`completed`) + `lms_order_item` immediately; `hook_entity_insert` fires `_lms_order_process_completed_order()` | `lms_order` | F-004 |
| 2b | Student | Clicks "Buy" on a **paid** course | `CheckoutController::checkout()` builds `CheckoutForm`; Stripe: `PaymentIntent::create()` called server-side, `client_secret` pushed to `drupalSettings` | `lms_order` | F-004 |
| 3 | System | `_lms_order_process_completed_order()` runs | Creates `lms_user_course` (status=`enrolled`, `field_progress=0`); sends confirmation email (`hook_mail` key `order_completed`); creates in-app `lms_notification` entity | `lms_order.module` | F-003, F-004 |
| 4 | Student | Navigates to `/learn/{course}/{lesson}` | `UserPortalController::lessonView()` checks for existing `lms_user_course`; if missing, auto-enrolls (free path). Resolves lesson type and video embed URL. | `lms_progress` | F-001, F-003 |
| 5 | Student | Completes a lesson (watches video / reads content) | POST to `/api/lms/progress/mark-complete` with `{course_id, lesson_id}` | `lms_progress` | F-003 |
| 6 | System | `ProgressApiController::markComplete()` runs | Upserts `lms_user_lesson` (status=`completed`); recalculates `lms_user_course.field_progress` as `(completed_lessons / total_lessons) * 100`; sets `field_status=completed` when progress=100% | `lms_progress` | F-003 |
| 7 | Student | Views dashboard at `/dashboard` | `UserPortalController::dashboard()` loads all `lms_user_course` for user; computes "continue URL" by finding next incomplete lesson; renders `lms_dashboard` theme | `lms_progress` | F-003 |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 2b-E1 | Student is anonymous | `CheckoutController::checkout()` redirects to Drupal login | Student logs in, returns to checkout |
| 2b-E2 | Stripe `PaymentIntent::create()` fails on form build | Form renders error; no order is created | Student retries page load |
| 2b-E3 | Card payment declined (client-side) | Stripe.js returns error; form stays; no submit sent | Student uses a different card or method |
| 2b-E4 | Bank transfer: student submits without uploading receipt | `BankTransferForm` order stays `pending`; no enrollment | Student uploads receipt later; admin approves manually |
| 3-E1 | Enrollment email fails | PHP mail error is logged; enrollment still succeeds (email is best-effort) | Admin checks mail logs |
| 4-E1 | Membership expired; lesson is membership-only | `MembershipAccessSubscriber` catches `AccessDeniedHttpException` on `/learn/*`; redirects to `lms_course.pricing` with warning | Student renews membership |
| 6-E1 | `markComplete` POST without valid `course_id`/`lesson_id` | `ProgressApiController` returns `{success: false}` | Client retries |

## Success Criteria

- `lms_user_course` entity exists for the student/course pair with `field_status=enrolled` (or higher).
- After all lessons completed: `field_status=completed`, `field_progress=100`.
- Dashboard "continue URL" resolves to the next incomplete lesson.
- Confirmation email and in-app notification are created on enrollment.

## Business Rules

- **Free course**: `createAndFulfillFreeOrder()` directly sets order to `completed`; no payment form shown.
- **Auto-enroll on lesson access**: `UserPortalController::lessonView()` creates a `lms_user_course` record if one does not exist (supports direct deep-links for free courses).
- **Progress calculation**: `ProgressApiController` counts all `lms_user_lesson` records with `field_status=completed` for the user+course pair, divided by total lessons in the course.
- **`field_start_time` accuracy**: `QuizResult.field_start_time` is hardcoded to `request_time - 60` (not real elapsed time) — **bug, as-built**.
- **Notification channels**: In-app notification is created. Email is sent via `hook_mail`. Push channel is a log-only stub (`lms_notification` `NotificationManager`).
- Lesson types handled in `lessonView()`: video (YouTube/Vimeo/direct), pdf, pptx, h5p, zoom, scorm.

## Related Docs

- [WF-004 — Order & Checkout](./WF-004-order-management.md)
- [WF-003 — Certificate Issuance](./WF-003-certificate-issuance.md)
- [WF-005 — Membership Subscription](./WF-005-membership-subscription.md)
- Source: `web/modules/custom/lms_progress/src/Controller/UserPortalController.php`
- Source: `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php`
- Source: `web/modules/custom/lms_order/lms_order.module`
