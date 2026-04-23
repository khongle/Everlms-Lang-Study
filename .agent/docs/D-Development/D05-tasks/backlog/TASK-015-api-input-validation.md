# Task: API Input Validation — All Write Endpoints

**ID:** TASK-015
**Unit:** U-057
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG (40% — basic only)

## Audit Notes
- 18 Form classes found, all extending FormBase
- `#required` usage minimal. `#maxlength` NOT used in any custom form
- `setRequired(TRUE)` on ~20 entity base field definitions
- **Zero custom entity constraints** (`addConstraint`) on any entity
- **Zero `validateForm()` overrides** in checkout or payment forms
- CheckoutForm and BankTransferForm process payments with no server-side validation beyond `#required`
- **Risk: HIGH** — OWASP compliance gaps

## Description
Audit và thêm input validation cho tất cả API/form write endpoints. Plug validation gaps hiện tại.

## Acceptance Criteria
- [ ] Audit tất cả routes có write operations
- [ ] Form API validation cho mọi user-facing forms
- [ ] Entity constraint validation cho mọi entity save operations
- [ ] Sanitize input (XSS prevention) trên tất cả text fields
- [ ] Validate entity references (ensure referenced entity exists + access)
- [ ] File upload validation (type, size, extension)
- [ ] Numeric range validation (scores, prices, quantities)

## Technical Notes
- Impact: tất cả custom modules
- Approach: Entity constraints + Form API `#required`, `#maxlength`, custom validators
- Security: OWASP Top 10 compliance

## Dependencies
- None (can start immediately)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed (đặc biệt G4 Security category)
- [ ] Zero input validation gaps trên write endpoints
