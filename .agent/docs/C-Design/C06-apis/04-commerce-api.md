# C06-04 — Commerce API

> Modules: `lms_order`, `lms_membership`, `lms_payment`, `lms_stripe`, `lms_paypal`.
> `lms_stripe` và `lms_paypal` là plugin-only — không có routing.yml, không có webhook endpoint. Đây là **RISK-004**.

---

## Route Table

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_order.checkout` | `/checkout/{course_id}` | GET | `CheckoutController::checkout` | `_access:TRUE`* | HTML |
| `lms_order.checkout_membership` | `/checkout/membership/{plan_id}` | GET | `CheckoutController::checkoutMembership` | `_access:TRUE`* | HTML |
| `lms_order.bank_transfer` | `/checkout/bank-transfer/{lms_order}` | GET | `CheckoutController::bankTransferInstruction` | anon | HTML |
| `lms_membership.my_membership` | `/my-membership` | GET | `MembershipController::myMembership` | login | HTML |
| `lms_membership.cancel_membership` | `/my-membership/{lms_user_membership}/cancel` | GET | `MembershipController::cancelMembership` | login | HTML/redirect |
| `lms_membership.admin_pricing` | `/workspace/pricing` | GET | `MembershipAdminController::pricingList` | perm:administer lms_course | HTML |
| `lms_membership.admin_pricing.add` | `/workspace/pricing/add` | GET | `MembershipAdminController::pricingAddForm` | perm:administer lms_course | HTML/Form |
| `lms_membership.admin_pricing.edit` | `/workspace/pricing/{lms_membership_plan}/edit` | GET | `MembershipAdminController::pricingEditForm` | perm:administer lms_course | HTML/Form |
| `lms_membership.admin_pricing.toggle` | `/workspace/pricing/{lms_membership_plan}/toggle` | GET | `MembershipAdminController::toggleStatus` | perm:administer lms_course | HTML/redirect |
| `lms_membership.admin_user_memberships` | `/workspace/user-memberships` | GET | `MembershipAdminController::userMembershipsList` | perm:administer lms_course | HTML |
| `lms_membership.admin_user_memberships.add` | `/workspace/user-memberships/add` | GET | `MembershipAdminController::userMembershipAddForm` | perm:administer lms_course | HTML/Form |
| `lms_membership.admin_user_memberships.edit` | `/workspace/user-memberships/{lms_user_membership}/edit` | GET | `MembershipAdminController::userMembershipEditForm` | perm:administer lms_course | HTML/Form |
| `lms_payment.settings` | `/admin/config/everlms/payment` | GET/POST | `PaymentSettingsForm` (Form API) | perm:administer site configuration | HTML/Form |

> *`_access:TRUE` nghĩa là routing bypass — không có authentication check ở route layer; controller phải tự kiểm tra.

---

## Route Chi Tiết

### GET `/checkout/{course_id}`

**Auth:** `_access: TRUE` (route layer không chặn — **RISK-001**)
**Path param:** `course_id` — raw integer (không upcast entity)
**Constraint:** `course_id: '\d+'`

**Business logic (controller):**
1. Load course, tính giá (áp coupon nếu có).
2. Kiểm tra course chưa mua / chưa enrolled.
3. Hiển thị payment method form (Stripe card, PayPal, bank transfer).
4. Khi submit → tạo `lms_order` entity, redirect đến payment gateway.

**Gap:** Không có POST route riêng cho submit checkout — form action gắn vào controller cùng path hoặc dùng Drupal Form API.

---

### GET `/checkout/membership/{plan_id}`

**Auth:** `_access: TRUE` (route layer — **RISK-001**)
**Path param:** `plan_id` — raw integer
**Constraint:** `plan_id: '\d+'`

**Business logic:** Tương tự checkout course nhưng tạo `lms_user_membership` thay vì order item.

---

### GET `/checkout/bank-transfer/{lms_order}`

**Auth:** `access content` (public)
**Path param:** `lms_order` — entity upcast
**Mô tả:** Hiển thị thông tin tài khoản ngân hàng và hướng dẫn chuyển tiền sau khi tạo order.

---

### GET `/my-membership/{lms_user_membership}/cancel`

**Auth:** `_user_is_logged_in: TRUE`
**Path param:** `lms_user_membership` — không có entity upcast khai báo trong routing.yml
**Gap:** Thiếu ownership check ở route layer — controller phải tự verify membership thuộc user.

---

## Payment Plugin Architecture

`lms_stripe` và `lms_paypal` là Drupal Plugin (`PaymentGatewayInterface`), không phải controller routes.

| Plugin | Class | Gap |
|--------|-------|-----|
| `StripeGateway` | `Drupal\lms_stripe\Plugin\PaymentGateway\StripeGateway` | Không có webhook endpoint |
| `PayPalGateway` | `Drupal\lms_paypal\Plugin\PaymentGateway\PayPalGateway` | Không có webhook endpoint |

**RISK-004 (Critical):** Stripe và PayPal gửi webhook events (payment.intent.succeeded, IPN) để xác nhận thanh toán. Hiện tại hệ thống không có route nhận webhook → không thể xác nhận payment server-side → phải dùng client-side redirect callback (không an toàn, có thể bị bypass).

### Webhook Endpoints Cần Thêm

| Endpoint | Module | Mô tả |
|----------|--------|-------|
| `POST /api/lms/payment/stripe/webhook` | `lms_stripe` | Nhận Stripe webhook events |
| `POST /api/lms/payment/paypal/ipn` | `lms_paypal` | Nhận PayPal IPN |

**Security requirement cho webhook:** Validate `Stripe-Signature` header (HMAC-SHA256) trước khi xử lý payload. Hiện chưa implement.

---

## Order Lifecycle

```
PENDING → PROCESSING → COMPLETED
                     ↘ FAILED
COMPLETED → REFUNDED
```

| Status | Trigger |
|--------|---------|
| PENDING | Order tạo mới |
| PROCESSING | Payment gateway nhận request |
| COMPLETED | Webhook confirm (hiện thiếu) / manual admin |
| FAILED | Payment failed |
| REFUNDED | Admin refund action |

---

## Gap Summary

| Thiếu | Mô tả | Priority |
|-------|-------|----------|
| Stripe webhook endpoint | Không có route nhận Stripe events | P0 (RISK-004) |
| PayPal IPN endpoint | Không có route nhận PayPal IPN | P0 (RISK-004) |
| Coupon apply API | Không có endpoint áp dụng coupon | P1 |
| Order history page | `/orders` route không có trong lms_order.routing.yml — cần kiểm tra | Gap |
| POST /checkout submit | Không rõ form submit endpoint — cần kiểm tra controller | Gap |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-004 | Payment gateway là stub — Stripe/PayPal plugin chưa có webhook receiver |
| RISK-001 | `/checkout/*` dùng `_access:TRUE` — không có auth guard ở route layer |
| — | `cancel_membership` thiếu ownership access check ở route layer |
| DEC-001 | Checkout form input validation chưa có schema chuẩn |

---

*Module paths: `web/modules/custom/lms_order/`, `lms_membership/`, `lms_payment/`, `lms_stripe/`, `lms_paypal/`*
