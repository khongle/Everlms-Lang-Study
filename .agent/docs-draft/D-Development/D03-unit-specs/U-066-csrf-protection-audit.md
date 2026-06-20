# D03 — Unit Spec: CSRF Protection Audit

**Unit ID:** U-066
**Group:** G-HARDENING
**Title:** CSRF protection audit
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-001 (High)

---

## Scope

Audit all state-changing forms and AJAX endpoints for CSRF token validation. Ensure Drupal Form API CSRF is active for all forms; verify any custom AJAX endpoints include token check.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| All `*Form.php` extending FormBase | Audit | All lms_* modules |
| All custom AJAX routes | Audit + Modify | All lms_* modules |
| Webhook routes | Verify exemption is explicit | lms_stripe, lms_paypal |

---

## Interfaces / Services

- Forms using Drupal Form API: CSRF is automatic — verify not bypassed
- Custom AJAX routes: must use `\Drupal::csrfToken()->validate($token, 'session')` or route requirement `_csrf_token: TRUE`
- Webhook routes: explicitly exempt (signature-based auth), add inline comment explaining why
- Verify no form has `'#token' => FALSE` unless intentional (document if so)

---

## Acceptance Criteria

- [ ] All forms using Drupal Form API have CSRF protection active
- [ ] No form has `'#token' => FALSE` without documented justification
- [ ] All custom state-changing AJAX endpoints validate CSRF token
- [ ] Webhook routes explicitly documented as CSRF-exempt (comment in routing.yml)
- [ ] Audit report: list of all forms + CSRF status

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | CSRF attack simulation on AJAX endpoints |
| G4 Adversarial | PENDING | Cross-site form submission test |

---

## Dependencies

- U-057 (validation) — complementary
- U-058 (access control) — run together

---

**Feature Flag:** None

*End of U-066-csrf-protection-audit.md*
