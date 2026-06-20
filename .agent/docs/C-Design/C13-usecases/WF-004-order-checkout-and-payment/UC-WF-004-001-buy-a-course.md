---
usecase_id: UC-WF-004-001
workflow: WF-004-order-checkout-and-payment
status: as-built
primary_actor: Student
modules: [lms_order, lms_payment, lms_stripe]
processes: [order-checkout-and-payment]
---

# UC-WF-004-001 - Buy a course

## Intent
A buyer purchases a course and gains access.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-004 |
| Business flow | cart → coupon → pay → entitlement |
| Modules | lms_order, lms_payment, lms_stripe |
| Related features | F-010, F-011 |
| B04 source | WF-004 |

## Actors
Buyer (primary); gateway; manager (bank confirm).

## Preconditions
Authenticated; course is paid; gateway enabled.

## Trigger
Buyer confirms checkout.

## Main success scenario
1. Open checkout (P029); review item.
2. Optionally apply coupon (WF-009).
3. Select gateway (stripe/bank_transfer/…); pay.
4. On success → order paid/completed; enrollment granted (WF-001).

## Alternate and exception flows
- Declined → order pending; retry/switch gateway.
- Bank transfer → pending until manager confirms (P036).
- ⚠️ No webhook (R-002) → reliance on synchronous return.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Checkout | App | /checkout/{course} |
| Manage orders | Manage | /workspace/orders |

## Acceptance and test contract
- Paid order → entitlement; declined → no access (B05 F-010/F-011).
