# Progress + Commerce Layer

---

## `lms_progress` — Learning Progress

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Track user progress at course/chapter/lesson levels, cascade calculation |
| **Entities** | `lms_user_course`, `lms_user_chapter`, `lms_user_lesson` |
| **Dependencies** | `lms_base`, `lms_course`, `lms_chapter`, `lms_lesson`, `lms_quiz_result` |
| **Services** | `lms_progress.course_manager`, `lms_progress.chapter_manager`, `lms_progress.lesson_manager` |

### Key Services
- `lms_progress.lesson_manager` — Mark lesson complete, track time/video progress
- `lms_progress.chapter_manager` — Recalculate chapter progress from lessons
- `lms_progress.course_manager` — Recalculate course progress from chapters, trigger completion events

---

## `lms_order` — Order Management

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Order CRUD, checkout flow, invoicing, refund processing |
| **Entities** | `lms_order`, `lms_order_item`, `lms_coupon` |
| **Dependencies** | `lms_base`, `lms_payment` |
| **Services** | `lms_order.manager`, `lms_order.checkout`, `lms_order.invoice` |

### Key Services
- `lms_order.checkout` — Cart → Coupon validation → Payment → Enrollment
- `lms_order.invoice` — PDF invoice generation

---

## `lms_membership` — Membership & Subscription

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Membership plans, user subscriptions, auto-renewal, expiry |
| **Entities** | `lms_membership_plan`, `lms_user_membership` |
| **Dependencies** | `lms_base`, `lms_payment` |
| **Services** | `lms_membership.manager`, `lms_membership.renewal` |

### Key Services
- `lms_membership.renewal` — Cron-based auto-renewal, grace period, expiry notifications

---

## `lms_payment` — Payment Gateway Core

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Payment processing interface, transaction logging |
| **Entities** | `lms_payment_transaction` |
| **Dependencies** | `lms_base` |
| **Services** | `lms_payment.gateway_manager` (Plugin-based) |

### Plugin Architecture
```
PaymentGatewayManager (Plugin Manager)
├── StripeGateway (lms_stripe)
├── PayPalGateway (lms_paypal)
└── [Future gateways as plugins]
```

---

## `lms_stripe` — Stripe Integration

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Stripe Checkout Sessions, subscription billing, webhook handling |
| **Dependencies** | `lms_payment` |
| **Services** | `lms_stripe.gateway` |

---

## `lms_paypal` — PayPal Integration

| Attribute | Value |
|-----------|-------|
| **Responsibility** | PayPal Smart Buttons, subscription billing, webhook handling |
| **Dependencies** | `lms_payment` |
| **Services** | `lms_paypal.gateway` |
