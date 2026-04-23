# D02 — Group Spec: G-COMMERCE

**Group ID:** G-COMMERCE
**Group Name:** Commerce & Payment
**Order:** 5
**Units in Scope:** U-025 – U-034
**Dependencies:** G-FOUNDATION
**Estimated Sprints:** 2
**Status:** PARTIAL (5 DONE, 5 SPEC)

---

## Purpose

Xây dựng toàn bộ commerce layer: Order/OrderItem entities, MembershipPlan/UserMembership, PaymentGatewayInterface + PaymentManager, Stripe/PayPal gateway implementations, checkout workflow, webhook handling, coupon system, membership lifecycle, và admin order management.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-025 | Order + OrderItem entities | DONE | G4 pass | P0 |
| U-026 | MembershipPlan + UserMembership entities | DONE | G4 pass | P0 |
| U-027 | PaymentGatewayInterface + PaymentManager | DONE | G4 pass | P0 |
| U-028 | StripeGateway (stub) | DONE | G4 pass | P0 |
| U-029 | PayPalGateway (stub) | DONE | G4 pass | P0 |
| U-030 | Checkout workflow | SPEC | — | P1 |
| U-031 | Webhook handler + signature verification | SPEC | — | P1 |
| U-032 | Coupon system | SPEC | — | P1 |
| U-033 | Membership lifecycle (activate/expire/renew) | SPEC | — | P1 |
| U-034 | Order management (admin) | SPEC | — | P1 |

---

## Build Order Within Group

1. U-025, U-026, U-027 — Entities + interface (parallel)
2. U-028, U-029 — Gateway stubs (parallel, depends on U-027)
3. U-030 — Checkout workflow (depends on U-025, U-026, U-027, U-028, U-029)
4. U-031 — Webhook handler (depends on U-028, U-029) — **must implement sig verification per RISK-004**
5. U-032 — Coupon system (depends on U-025, U-030)
6. U-033 — Membership lifecycle (depends on U-026, U-030, U-031)
7. U-034 — Order management admin (depends on U-025, U-026)

---

## Key Modules

- `lms_order` — U-025, U-034
- `lms_membership` — U-026, U-033
- `lms_payment` — U-027, U-030, U-031, U-032
- `lms_stripe` — U-028, U-031 (Stripe webhook)
- `lms_paypal` — U-029, U-031 (PayPal webhook)

---

## Exit Criteria

- [x] Order + Membership entities
- [x] PaymentGatewayInterface + gateway stubs
- [ ] Checkout workflow end-to-end (U-030)
- [ ] Webhook handler with signature verification (U-031) — RISK-004
- [ ] Coupon application + validation (U-032)
- [ ] Membership activate/expire/renew cycle (U-033)
- [ ] Admin order management page (U-034)

---

## Key Risks

- **RISK-004** (High): Gateway stubs U-028, U-029 không phải production-ready. U-030/U-031 phải implement thực.
- Webhook signature verification (U-031) → hardening overlap với U-060, U-061 in G-HARDENING.
- Idempotency for payment callbacks → U-062 in G-HARDENING.

---

*End of 05-G-COMMERCE.md*
