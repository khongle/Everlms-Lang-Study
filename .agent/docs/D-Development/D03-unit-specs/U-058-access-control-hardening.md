# D03 — Unit Spec: Access Control Hardening — All Routes

**Unit ID:** U-058
**Group:** G-HARDENING
**Title:** Access control hardening — all routes
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-001 (High — API endpoint validation gaps)

---

## Scope

Audit every route in all lms_* modules for correct access requirements. Ensure no route is accidentally public, ownership checks are enforced (user can only access own data), and role-based access is explicit.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| All `*.routing.yml` files | Audit + Modify | All lms_* modules |
| All `*Controller.php` | Audit + Modify | All lms_* modules |
| All `*AccessCheck.php` | Audit/Create | lms_base + affected modules |

---

## Interfaces / Services

- Every route must have `requirements: _permission: ...` or `_custom_access: ...` in routing.yml
- Ownership check: user can only access own UserCourse, Order, Certificate, etc.
- Admin routes prefix `/manage/` must require `administrator` or `lms_manager` role
- No route with write access should allow `_access: TRUE` (anonymous)

---

## Acceptance Criteria

- [ ] All routes have explicit access requirements (no implicit open access)
- [ ] Ownership checked for all user-data endpoints (certificate download, order detail, etc.)
- [ ] `/manage/*` routes require admin or manager role
- [ ] Anonymous users cannot access any write endpoint
- [ ] Access checks bypass is not possible via HTTP method spoofing
- [ ] Audit report produced: list of all routes + access rule (document in D08 session log)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Role matrix test for all key routes |
| G4 Adversarial | PENDING | Privilege escalation test (student → instructor routes) |

---

## Dependencies

- All routes must exist before audit: complete after all SPEC units are DONE
- U-065 (Entity access handler audit) — complementary, run together

---

**Feature Flag:** None

*End of U-058-access-control-hardening.md*
