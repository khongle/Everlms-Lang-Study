# D02 — Group Spec: G-HARDENING

**Group ID:** G-HARDENING
**Group Name:** Quality & Hardening
**Order:** 10
**Units in Scope:** U-057 – U-070
**Dependencies:** ALL above groups (G-FOUNDATION through G-ADDITIONAL)
**Estimated Sprints:** 2-3
**Status:** NOT_STARTED
**Priority:** P0 — Remediation required before production

---

## Purpose

Remediation backlog P0: vá lỗ hổng bảo mật, race condition, thiếu validation, static service calls, và thiết lập cache/performance strategy. Không add feature mới — chỉ hardening code đã có.

---

## Units

| Unit ID | Name | Status | Risk Ref | Priority |
|---------|------|--------|----------|----------|
| U-057 | API input validation — all write endpoints | SPEC | RISK-001 | P0 |
| U-058 | Access control hardening — all routes | SPEC | RISK-001 | P0 |
| U-059 | Progress race condition fix (DB lock/transaction) | SPEC | RISK-002 | P0 |
| U-060 | Webhook signature verification (Stripe) | SPEC | RISK-004 | P0 |
| U-061 | Webhook signature verification (PayPal) | SPEC | RISK-004 | P0 |
| U-062 | Idempotency keys for payment callbacks | SPEC | RISK-004 | P0 |
| U-063 | AI rollback + compensation logic | SPEC | RISK-003 | P0 |
| U-064 | Static service call cleanup (\Drupal:: → DI) | SPEC | — | P0 |
| U-065 | Entity access handler audit | SPEC | RISK-001 | P0 |
| U-066 | CSRF protection audit | SPEC | RISK-001 | P0 |
| U-067 | Error handling standardization | SPEC | — | P0 |
| U-068 | Logging for critical operations | SPEC | — | P0 |
| U-069 | Cache strategy implementation (Redis) | SPEC | — | P0 |
| U-070 | Performance optimization (N+1 queries, lazy load) | SPEC | — | P0 |

---

## Build Order Within Group

**Wave 1 — Security (parallel):**
- U-057 API validation
- U-058 Access control
- U-065 Entity access audit
- U-066 CSRF audit

**Wave 2 — Data integrity (parallel after Wave 1):**
- U-059 Race condition fix
- U-060, U-061, U-062 Webhook/payment idempotency

**Wave 3 — Code quality (parallel):**
- U-063 AI rollback
- U-064 DI cleanup
- U-067 Error handling
- U-068 Logging

**Wave 4 — Performance (after Wave 3):**
- U-069 Redis cache
- U-070 N+1/lazy load

---

## Exit Criteria

- [ ] All endpoints validate input at controller layer (U-057)
- [ ] All routes have explicit access checks (U-058)
- [ ] Progress updates use DB transactions + locking (U-059)
- [ ] Stripe + PayPal webhook signatures verified before processing (U-060, U-061)
- [ ] Payment callbacks are idempotent (U-062)
- [ ] AI operations have rollback path (U-063)
- [ ] No `\Drupal::service()` in service classes (U-064)
- [ ] Entity access handlers audited and complete (U-065)
- [ ] All state-changing forms have CSRF token (U-066)
- [ ] Standardized error responses across all APIs (U-067)
- [ ] Critical operations logged to watchdog (U-068)
- [ ] Redis cache configured for hot data (U-069)
- [ ] No N+1 queries on list pages (U-070)

---

## Key Risks

- Large surface area: 14 units across all modules. Requires full code audit.
- DEC-001 (open): validation strategy must be resolved before U-057 starts.
- DEC-003 (open): lock vs queue decision must be resolved before U-059.
- G-HARDENING is gate for G-UI-POLISH — do not skip.

---

*End of 10-G-HARDENING.md*
