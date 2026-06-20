---
usecase_id: UC-001-02
workflow: WF-001-enrollment-learning
type: happy
---

# UC-001-02 — Free Course Checkout → Immediate Enrollment

**One-line purpose:** An authenticated student visits a free course checkout URL; the system instantly creates a completed order and enrolls the student without any payment step.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user |
| `CheckoutController` | Detects free course and short-circuits to immediate fulfillment |
| `lms_order.module` | `hook_entity_insert` on the completed order triggers enrollment |
| `NotificationManager` (via `lms_notification`) | Creates in-app notification |

---

## Preconditions

- Student is authenticated.
- `lms_course` entity exists with `field_price_type = free` (or `field_price = 0`).
- Student has no existing enrollment for this course (the flow does not guard against re-enrollment; a duplicate `lms_user_course` may be created).

---

## Trigger

Student visits `/checkout/{course_id}` (route `lms_order.checkout`); course resolves to a free course.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** student navigates to `/checkout/{course_id}` |
| 2 | **When** `CheckoutController::checkout()` loads the course and checks `field_price_type` |
| 3 | **Then** the controller detects free course and calls `createAndFulfillFreeOrder($course_id, $uid)` directly — no payment form is shown |
| 4 | **When** `createAndFulfillFreeOrder()` runs |
| 5 | **Then** a `lms_order_item` entity is created: `field_course={course_id}`, `field_type=course`, `field_price=0` |
| 6 | **Then** a `lms_order` entity is created: `uid={student_uid}`, `field_order_number={generated}`, `field_items=[item]`, `field_subtotal=0`, `field_total=0`, `field_currency=USD`, `field_status=completed`, `field_payment_method=free` |
| 7 | **When** `lms_order.module::hook_entity_insert()` fires on the new order (status already = `completed`) |
| 8 | **Then** `_lms_order_process_completed_order()` iterates `field_items` |
| 9 | **Then** for the course item: a `lms_user_course` entity is created with `uid={student_uid}`, `field_course={course_id}`, `field_status=enrolled`, `field_progress=0.00`, `field_enrolled_at={now}` |
| 10 | **Then** `plugin.manager.mail` sends `order_completed` email to student |
| 11 | **Then** an in-app `lms_notification` entity is created: `field_type=enrollment`, `field_channel=[in_app]` |
| 12 | **Then** student is redirected to the course page or dashboard |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_order` | `field_status` | `completed` (set at creation) |
| `lms_order` | `field_total` | `0.00` |
| `lms_order` | `field_payment_method` | `free` |
| `lms_order_item` | `field_course`, `field_price`, `field_type` | created with course reference, 0, course |
| `lms_user_course` | all fields | created: enrolled, progress=0, enrolled_at=now |
| `lms_notification` | all fields | created: type=enrollment, in_app channel |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Order created + completed | `hook_entity_insert` | Enrollment triggered synchronously in same request |
| Enrollment email | `hook_mail` `order_completed` | Sent immediately via `plugin.manager.mail` |
| In-app notification | `lms_notification` entity | Reflected in notification bell |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-036](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-036-free-course-checkout-immediately-enrolls-the-student) — Free course checkout enrolls immediately | TEST-036 |

---

## Notes

- The hook fires on `hook_entity_insert` (order is inserted already complete), not `hook_entity_update`. This distinguishes the free path from the Stripe path.
- No guard against duplicate enrollment: if a student who is already enrolled revisits `/checkout/{course_id}`, `createAndFulfillFreeOrder()` creates another `lms_order` and another `lms_user_course` entity. Deduplication is not implemented.
