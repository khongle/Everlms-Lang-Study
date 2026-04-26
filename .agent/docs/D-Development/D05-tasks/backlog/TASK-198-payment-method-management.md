# Task: Payment Method Management — Cards on File

**ID:** TASK-198
**Unit:** — (lms_payment extension)
**Group:** G-COMMERCE
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow users to manage payment methods (credit/debit cards) stored in Stripe. Users can add new cards via Stripe Setup Intents, set a default card, and remove saved cards. The UI displays card brand and last 4 digits. The default card is used for recurring subscription charges and one-off purchases.

## Acceptance Criteria

- [ ] **List view** (`/my/payment-methods`):
  - [ ] Displays all saved cards: brand icon (Visa, MC, Amex, etc.), last4, expiry month/year, "Default" badge
  - [ ] "Add new card" button
  - [ ] "Set as default" link per non-default card
  - [ ] "Remove" button per card (disabled for default card if subscription active)
- [ ] **Add card flow:**
  - [ ] Stripe Setup Intent created server-side; client mounts Stripe.js `PaymentElement`
  - [ ] On confirmation: `PaymentMethod` attached to Stripe Customer; stored locally in `lms_payment` card table
  - [ ] First card added automatically set as default
  - [ ] 3DS challenge handled by Stripe.js redirect
- [ ] **Set default:**
  - [ ] Updates `customer.invoice_settings.default_payment_method` on Stripe Customer
  - [ ] Local default flag updated
- [ ] **Remove card:**
  - [ ] Cannot remove default card while an active subscription exists — show warning
  - [ ] Stripe `PaymentMethod.detach()` called; local record deleted
- [ ] Webhook `payment_method.attached` / `.detached` syncs state if changed outside UI
- [ ] PHPUnit kernel test: add → set default → remove cycle; guard against removing active default

## Technical Notes

- Module: `lms_payment` — new `PaymentMethodController`, new `lms_payment_methods` DB table (columns: pmid, uid, stripe_pm_id, brand, last4, exp_month, exp_year, is_default, created)
- Stripe SDK: `\Stripe\SetupIntent::create(['customer' => ..., 'usage' => 'off_session'])`, `\Stripe\PaymentMethod::retrieve()`, `->detach()`
- Stripe.js: load from `https://js.stripe.com/v3/`; mount in custom Drupal attached library
- Card brand icons: use Stripe's official SVG set or CSS sprites — store in `lms_payment/assets/`
- AJAX endpoint: `POST /api/lms/payment-methods` (add), `PATCH /api/lms/payment-methods/{id}/default`, `DELETE /api/lms/payment-methods/{id}`
- No raw card data ever touches the server (PCI DSS scope-1 avoided)

## Dependencies

- TASK-029 (Stripe gateway — StripeClientFactory, Customer management)
- TASK-005 (commerce checkout — uses default PM for recurring)

## Definition of Done

- [ ] Code complete — controller, DB schema, Stripe.js integration
- [ ] G1-G4 gates passed
- [ ] Stripe test cards (4242, 4000002500003155 for 3DS) tested manually
- [ ] No raw card numbers logged or stored
- [ ] Tests written + passing
- [ ] No new debt introduced
