# D03 — Story Spec: lms_payment

**Story ID:** lms_payment
**Story type:** module
**Title:** Payment Gateway Plugin Host — Plugin Manager, Settings, BankTransfer, and Stub Gateways
**Primary source:** `C-Design/C05-modules/lms_payment.md`
**Owning module(s):** lms_payment
**Service(s):** plugin.manager.payment_gateway (PaymentGatewayManager)
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-005 (Payment Integration)
**Design source(s):** `../C05-modules/lms_payment.md`
**Round:** N/A
**Driver:** SA directive
**Status:** IN_PROGRESS
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_payment`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2
**Splitting strategy:**
- S1: Plugin system scaffold — PaymentGatewayManager, @PaymentGateway annotation, PaymentGatewayInterface (plugin), PaymentGatewayBase, PaymentSettingsForm, services.yml
- S2: BankTransferGateway (real), PayPalGateway stub, ZaloPayGateway stub — all in `Plugin/PaymentGateway/`; resolve PaymentManager DI gap; resolve dual-interface conflict

## Scope (from primary source)

Acts as the payment plugin host for EverLMS. Defines the `@PaymentGateway` annotation-driven plugin system (`PaymentGatewayManager`) that discovers gateway implementations across modules. Provides `PaymentSettingsForm` at `/admin/config/everlms/payment` for enabling gateways and storing credentials. Includes three built-in plugins: `BankTransferGateway` (real), `PayPalGateway` (stub — no server-side verification), and `ZaloPayGateway` (stub — hardcoded sandbox URL).

**IN_PROGRESS status rationale:**
- Plugin system architecture is complete and working at runtime.
- `BankTransferGateway` is real and used in production.
- `PayPalGateway` (in this module) is a stub: accepts any `paypal_order_id` without API verification.
- `ZaloPayGateway` is a stub: hardcoded sandbox URL, no HMAC signature.
- **No webhook endpoints exist** — `handleWebhook()` on all gateways returns a no-op or TODO response.
- `PaymentManager` service class exists but is **not registered in any services.yml** and is never used at runtime.
- Two incompatible `PaymentGatewayInterface` definitions exist (Plugin vs Service namespace) — architectural conflict. See Notes.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_payment.md` | Owning module design |
| `../../B-Requirements/B03-features/04-order-membership.md` | Payment feature requirements |
| `../../B-Requirements/B06-integration-requirements/` | Gateway integration contracts |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Plugin-discoverable payment gateway system | B03-features/04-order-membership | Acceptance |
| BankTransfer gateway — manual transfer + receipt upload | B03-features/04-order-membership | Acceptance — as-built |
| PayPal gateway — server-side order verification | B03-features/04-order-membership | target-state / planned (stub only) |
| ZaloPay gateway — HMAC-signed request + callback | B03-features/04-order-membership | target-state / planned (stub only) |
| Webhook signature verification for Stripe, PayPal, ZaloPay | B03-features/04-order-membership | target-state / planned (no routes exist) |
| Admin settings form for gateway credentials | B03-features/04-order-membership | Acceptance — as-built |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_payment/lms_payment.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_payment/lms_payment.services.yml` | Create | PaymentGatewayManager registration | — | S1 |
| `web/modules/custom/lms_payment/lms_payment.routing.yml` | Create | /admin/config/everlms/payment route | — | S1 |
| `web/modules/custom/lms_payment/lms_payment.links.menu.yml` | Create | Admin menu link | — | S1 |
| `web/modules/custom/lms_payment/src/Annotation/PaymentGateway.php` | Create | @PaymentGateway annotation class | — | S1 |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayInterface.php` | Create | Plugin interface (processPayment, handleWebhook, getLabel) | — | S1 |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayBase.php` | Create | Abstract base — stubs for processPayment/handleWebhook | — | S1 |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayManager.php` | Create | DefaultPluginManager scanning Plugin/PaymentGateway/ | — | S1 |
| `web/modules/custom/lms_payment/src/Service/PaymentGatewayInterface.php` | Create | Service-layer interface (getId, createPayment, verifyPayment, refund) — NOT used by plugin system | — | S1 |
| `web/modules/custom/lms_payment/src/Service/PaymentManager.php` | Create | Registry class — NOT wired into DI container | — | S1 |
| `web/modules/custom/lms_payment/src/Form/PaymentSettingsForm.php` | Create | Config form for gateway credentials | — | S1 |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/BankTransferGateway.php` | Create | Real manual-transfer gateway (status=pending) | — | S2 |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` | Create | Stub — accepts any paypal_order_id | — | S2 |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/ZaloPayGateway.php` | Create | Stub — hardcoded sandbox redirect | — | S2 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_stripe (StripeGateway plugin) | Intra-Epic | Discovered by PaymentGatewayManager |
| PayPal REST SDK (not installed) | External — target-state | PayPalGateway stub comment |
| ZaloPay SDK (not installed) | External — target-state | ZaloPayGateway stub comment |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| DefaultPluginManager + annotation | PaymentGatewayManager | As-built |
| Plugin namespace: Plugin/PaymentGateway | BankTransferGateway, PayPalGateway, ZaloPayGateway | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/admin/config/everlms/payment` | Gateway credentials + enable flags | lms_payment.settings config | same path | administer site configuration | None | None | Config cache clear | As-built |

**Hardcoded content exceptions:** ZaloPayGateway uses hardcoded sandbox URL `https://sandbox.zalopay.vn/redirect/demo?order={id}`. Must be replaced before production use.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-PAY-001 | Unit | PaymentGatewayManager discovers BankTransferGateway, stripe, paypal, zalopay plugins | Plugin discovery AC |
| T-PAY-002 | Integration | BankTransferGateway::processPayment() returns status=pending with MANUAL_{order_id} | BankTransfer AC |
| T-PAY-003 | Unit | PaymentGatewayBase::processPayment() returns status=failed (base stub) | Stub AC |
| T-PAY-004 | Unit | PaymentSettingsForm saves and reads all gateway config keys | Settings AC |
| T-PAY-005 | Integration | PayPalGateway (stub) accepts arbitrary paypal_order_id — regression guard | Stub behaviour |
| T-PAY-006 | Unit | handleWebhook() on all gateways returns non-500 response (even stub) | Webhook stub AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; plugin manager discovers gateways | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | BankTransfer flow works; PayPal/ZaloPay stubs pass smoke | — | — |
| G4 Adversarial | PENDING | Webhook gap, PayPal stub security, dual-interface conflict not resolved | 0/3 | — |

## Notes

- **Dual PaymentGatewayInterface (architecture risk):** Two unrelated interfaces share the name `PaymentGatewayInterface` — `Plugin\PaymentGatewayInterface` (used by the real plugin system) and `Service\PaymentGatewayInterface` (only implemented by `lms_paypal\PayPalGateway`, which is dead code). The Service interface should be renamed or removed to prevent future confusion. Logged as FIND in G02 risk register.
- **PaymentManager not wired:** `src/Service/PaymentManager.php` exists as a gateway registry but is never registered in any `services.yml`. It is dead code. Should be removed or wired before use.
- **Webhook gap (critical risk):** No webhook routes exist in any payment module. No payment provider can asynchronously confirm payment completion. `handleWebhook()` on all gateways is a stub. Real money movement for Stripe, PayPal, and ZaloPay cannot be verified server-side via push. This is the highest-priority open work item in this Story.
- **Secrets:** All gateway keys in `lms_payment.settings` config YAML — `«secret in config — see security risk»`. See G02 risk register (planned, layer not yet materialized).
