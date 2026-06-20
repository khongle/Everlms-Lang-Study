---
id: TEST-004
type: Kernel + Functional
priority: P0 — Must
ac: AC-039, AC-040
feature: F-004 (Order & Membership)
module: lms_order, lms_payment
service: plugin.manager.payment_gateway (PaymentGatewayManager)
status: NOT_YET_AUTOMATED
---

# TEST-004 — Payment Checkout — Bank Transfer

> Verifies the bank transfer payment path: order creation, `BankTransferGateway::processPayment()` returning `status=pending`, receipt image upload, admin approval transition to `completed`, and auto-enrollment trigger. Stripe is NOT tested here (requires live API key or complex mock); use TC-004-D for Stripe-specific notes.

**Story:** [D03 lms_order](../../D-Development/D03-stories/lms_order.md)
**AC:** [AC-039 — Bank transfer creates pending order (B05 AC-04)](../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) · [AC-040 — Admin approval triggers enrollment (B05 AC-04)](../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md)
**C12 Workflow:** [WF-004 Order Checkout](../../C-Design/C12-workflows/WF-004-order-checkout.md)
**C05 Module:** [lms_order](../../C-Design/C05-modules/lms_order.md) · [lms_payment](../../C-Design/C05-modules/lms_payment.md)

---

## TC-004-A: `CheckoutForm` creates order and dispatches to `BankTransferGateway`

**Layer:** Kernel
**Given:**
- A paid `lms_course` with `field_price = 200000`, `field_price_type = paid`
- `lms_payment.settings` config has `enable_bank_transfer = TRUE`
- An authenticated `student` user

**When:** `CheckoutForm::submitForm()` is called with `payment_method = bank_transfer`

**Then:**
- A `lms_order` entity is created with `field_order_number` matching the pattern, `field_total = 200000`, `field_status = pending`, `field_payment_method = bank_transfer`
- A `lms_order_item` entity is created referencing the course with `field_type = course`, `field_price = 200000`
- `BankTransferGateway::processPayment()` returns `['status' => 'pending', 'transaction_id' => 'MANUAL_<order_id>']`
- `field_payment_id` on the order is set to `MANUAL_<order_id>`

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid paid course + bank_transfer method | Order created with `field_status=pending` |
| Free course | Controller calls `createAndFulfillFreeOrder()` directly; order `field_status=completed` |
| Anonymous user | Redirect to login |

---

## TC-004-B: Bank transfer instruction page renders

**Layer:** Functional
**Given:**
- A pending `lms_order` entity exists for the student

**When:** Student GETs `/checkout/bank-transfer/{lms_order}`

**Then:**
- HTTP 200; `CheckoutController::bankTransferInstruction()` verifies order is pending and returns `BankTransferForm`
- The `lms_bank_transfer` theme is rendered with VietQR parameters from `lms_payment.settings` config
- The receipt upload field (managed_file, png/jpg/jpeg, 5MB) is present in the form

### Test Data

| Input | Expected Output |
|-------|----------------|
| Pending order | HTTP 200, bank transfer form rendered |
| Completed order | HTTP 403 or redirect (order not pending) |
| Wrong user's order | HTTP 403 (OrderAccessControlHandler: view only own) |

---

## TC-004-C: Receipt upload and admin approval → auto-enrollment

**Layer:** Kernel
**Given:**
- `lms_order` with `field_status = pending` for student + course
- Admin submits `ApproveOrderForm` and changes `field_status` to `completed`

**When:** `hook_entity_update()` in `lms_order.module` fires on the order save

**Then:**
- `_lms_order_process_completed_order()` creates a `lms_user_course` enrollment for the student
- A `lms_notification` entity (type=`payment`) is created in-app
- `plugin.manager.mail` is called with key `order_completed`
- The order's `field_payment_receipt` image field is populated if a file was uploaded

### Test Data

| Input | Expected Output |
|-------|----------------|
| `pending` → `completed` transition | `lms_user_course` created; notification created |
| `processing` → `completed` | Same result |
| `completed` → `completed` (no change) | `hook_entity_update` fires; `_lms_order_process_completed_order` called again — idempotency risk (not guarded in code) |

---

## TC-004-D: Stripe PaymentIntent flow (scope note — NOT_YET_AUTOMATED)

**Layer:** Functional (requires Stripe mock or env-gated test)

This test case is documented but intentionally deferred:

- `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` on every page load (known bug — creates orphaned intents on refresh)
- Testing requires either a Stripe test mode API key (`«secret in config — see security risk»`) or a Stripe PHP SDK mock
- Once automated: verify that `StripeGateway::processPayment()` calls `\Stripe\PaymentIntent::retrieve($payment_intent_id)` and returns `status=success` when intent status is `succeeded`

**Known risk:** Stripe secret key is stored in plaintext in committed config YAML — `«secret in config — see security risk»`. The test should NOT hard-code or log this value. Use environment variable override.

---

## Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_order`, `lms_payment`, `lms_progress`, `lms_notification`; install entity schemas; configure `lms_payment.settings` with `enable_bank_transfer=TRUE`; create paid course, student user, admin user
- Teardown: Delete test entities; reset `lms_payment.settings`

## Notes

- `BankTransferGateway` source: `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/BankTransferGateway.php` — the only real payment gateway for synchronous testing (no external API)
- `lms_paypal` module (`web/modules/custom/lms_paypal/`) is **dead code** — `PayPalGateway` in that module implements the wrong interface and has no `@PaymentGateway` annotation; do NOT test it as a plugin
- ZaloPay gateway: hardcoded sandbox URL — do not test against real ZaloPay API
- No webhook routes exist in any module as of 2026-06-20 — webhook signature verification is a TODO; do not write webhook tests yet
- `createAndFulfillFreeMembershipOrder()` has a known minor bug: `$order->set('field_items', ...)` called twice (second call overwrites first); write a regression test to confirm only one order item is persisted
- Expected file: `web/modules/custom/lms_order/tests/src/Kernel/CheckoutBankTransferTest.php` (TC-004-A/C) + `web/modules/custom/lms_order/tests/src/Functional/BankTransferPageTest.php` (TC-004-B)
