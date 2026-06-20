# D03 — Story Spec: lms_paypal

**Story ID:** lms_paypal
**Story type:** module
**Title:** PayPal Payment Gateway — Dead-Code Stub (Not Wired)
**Primary source:** `C-Design/C05-modules/lms_paypal.md`
**Owning module(s):** lms_paypal
**Service(s):** N/A
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-005 (Payment Integration)
**Design source(s):** `../C05-modules/lms_paypal.md`
**Round:** N/A
**Driver:** SA directive
**Status:** BLOCKED
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_paypal`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2 (full rebuild required)
**Splitting strategy:**
- S1: Fix interface + add @PaymentGateway annotation; implement createPayment() using PayPal REST SDK; wire into plugin system
- S2: Implement verifyPayment(), refund(); add webhook route + signature verification; integration tests

## Scope (from primary source)

Intended to provide a real PayPal REST API gateway plugin. Currently contains one PHP class (`PayPalGateway`) that is **dead code** — it implements the wrong interface (`Service\PaymentGatewayInterface` instead of `Plugin\PaymentGatewayInterface`), carries no `@PaymentGateway` annotation, and therefore is never discovered by `PaymentGatewayManager`. All three methods are TODO stubs returning empty/false values.

The PayPal gateway actually used at runtime is `lms_payment\Plugin\PaymentGateway\PayPalGateway` (also a stub, but wired into the plugin system). This module's class is never instantiated.

**BLOCKED status rationale:**
- Wrong interface (cannot be discovered by plugin system without annotation).
- All methods are TODO placeholders — no PayPal REST SDK calls.
- No `lms_paypal.info.yml` register of dependencies; no services.yml, routing.yml, or module file.
- Cannot be released to production in current state.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_paypal.md` | Owning module design |
| `lms_payment.md` | Plugin host — defines the correct interface to implement |
| `../../B-Requirements/B06-integration-requirements/` | PayPal integration contract |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| PayPal order creation via REST API | B03-features/04-order-membership | target-state / planned |
| Server-side PayPal order verification on submission | B03-features/04-order-membership | target-state / planned |
| PayPal IPN/webhook endpoint with signature verification | B03-features/04-order-membership | target-state / planned |
| PayPal refund via REST API | B03-features/04-order-membership | target-state / planned |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_paypal/lms_paypal.info.yml` | Create | Module declaration (exists) | — | — |
| `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` | Create | Dead-code stub (wrong interface, no annotation) | — | — |

**Files to be created in future sessions (target-state):**
- `lms_paypal.routing.yml` — webhook route `/api/lms/webhook/paypal`
- `lms_paypal.services.yml` — if PayPal client needs DI registration
- Modify `PayPalGateway.php` — fix interface to `Plugin\PaymentGatewayInterface`, add `@PaymentGateway` annotation, implement all methods using `paypal/paypal-server-sdk` or `srmklive/paypal`

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_payment | Intra-module | info.yml dependency |
| PayPal REST SDK (not installed) | External — target-state | PayPalGateway TODO comments |
| lms_payment PayPalGateway stub (conflict) | Intra-Epic | Two classes claim same gateway id; must be resolved |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| @PaymentGateway annotation | PayPalGateway class | MISSING — not annotated |
| Plugin\PaymentGatewayInterface | PayPalGateway class | WRONG — uses Service\PaymentGatewayInterface |

## Editorial Usability / Entity-First Evidence

Not applicable — module has no pages or content surfaces.

**Hardcoded content exceptions:** None.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-PPL-001 | Unit | PayPalGateway is NOT discovered by PaymentGatewayManager (regression guard — current as-built) | Dead-code verification |
| T-PPL-002 | Integration | (target-state) PayPalGateway::createPayment() calls PayPal REST API and returns redirect_url | PayPal create AC |
| T-PPL-003 | Integration | (target-state) verifyPayment() calls PayPal capture endpoint and returns status=success/failed | PayPal verify AC |
| T-PPL-004 | Integration | (target-state) PayPal webhook route receives IPN, verifies signature, triggers order completion | Webhook AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs (no-op) | — | — |
| G2 Lint | PENDING | Not run against dead-code class | 0/3 | — |
| G3 Tests | PENDING | No implementation to test | 0/3 | — |
| G4 Adversarial | PENDING | Cannot pass with dead-code | 0/3 | — |

## Notes

- **Dead code (stub / dead-code):** `lms_paypal\PayPalGateway` is never instantiated. The runtime PayPal handler is `lms_payment\Plugin\PaymentGateway\PayPalGateway`, which is also a stub but IS discovered. This dual-class situation must be resolved: either remove the lms_paypal class or fix it to replace the stub in lms_payment.
- **Interface conflict (architecture issue):** `Service\PaymentGatewayInterface` defines a different method signature (`createPayment`, `verifyPayment`, `refund`) vs `Plugin\PaymentGatewayInterface` (`processPayment`, `handleWebhook`, `getLabel`). The plugin system requires the Plugin interface. The Service interface and this module's PayPalGateway class should be revised or removed. See [lms_payment Story](lms_payment.md) notes.
- **PayPal credentials:** `paypal_client_id` and `paypal_secret` in committed config — `«secret in config — see security risk»`.
- **Blocked by:** PayPal REST SDK installation + architectural resolution of dual-interface conflict + removal of conflicting stub in lms_payment.
