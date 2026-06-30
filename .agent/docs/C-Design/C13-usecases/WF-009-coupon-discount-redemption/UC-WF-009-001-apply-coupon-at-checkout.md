---
usecase_id: UC-WF-009-001
workflow: WF-009-coupon-discount-redemption
status: as-built
primary_actor: Student
modules: [lms_order, lms_payment]
processes: [coupon-discount-redemption]
---

# UC-WF-009-001 - Apply a coupon at checkout

## Intent
A buyer reduces their total with a coupon.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-009 |
| Business flow | enter → validate → apply → persist |
| Modules | lms_order, lms_payment |
| Related features | F-010 |
| B04 source | WF-009 |

## Actors
Buyer (primary); administrator/config maintainer for coupon definitions.

## Preconditions
At checkout with an order in progress.

## Trigger
Buyer enters a coupon code.

## Main success scenario
1. Enter code at checkout (P029).
2. Validate (validity/eligibility/limit).
3. Redirect back to checkout with the applied coupon in the query state.
4. Show inline coupon confirmation, subtotal, discount, and adjusted total.
5. On checkout submit, persist `field_coupon_code`, `field_discount`, and adjusted `field_total`.

## Alternate and exception flows
- Invalid/expired/over-limit → rejected with message.
- External payment methods must use the adjusted payable total after coupon application.
- Current as-built coupon definitions are config-driven (`lms_order.settings`); a manager coupon UI is not yet present.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Checkout | App | /checkout/{course} |

## Acceptance and test contract
- Valid → total reduced; invalid → rejected; discount recorded (B05 F-010).
- Verified test coupon: `SILVERCARE10` applies 10% discount to a paid SilverCare course.
