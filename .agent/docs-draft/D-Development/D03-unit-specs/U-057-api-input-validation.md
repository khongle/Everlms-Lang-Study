# D03 — Unit Spec: API Input Validation — All Write Endpoints

**Unit ID:** U-057
**Group:** G-HARDENING
**Title:** API input validation — all write endpoints
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-001 (High — API endpoint validation gaps)

---

## Scope

Add server-side input validation to every write endpoint (POST/PUT/PATCH/DELETE) across all lms_* modules. Resolve DEC-001: use per-controller validation via Symfony Validator constraints (not global middleware, to allow per-route customization).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Validator/` directory | Create | Each affected module |
| All `*Controller.php` with write actions | Modify | All lms_* modules |
| `src/Service/ValidationService.php` | Create | lms_base |

---

## Interfaces / Services

- `ValidationService::validate(array $data, array $constraints): array` — returns errors array
- Use Symfony `Assert\*` constraints (Length, NotBlank, Range, Regex, etc.)
- Pattern: validate before entity create/update; return 422 + error array on fail
- Key endpoints to audit: course CRUD, quiz submit, order create, user profile update, forum post, checkout

---

## Acceptance Criteria

- [ ] All POST/PUT/PATCH endpoints validate required fields (NotBlank, type checks)
- [ ] String fields: max length enforced (prevent DB truncation)
- [ ] Numeric fields: range validated (e.g., price >= 0, stars 1-5)
- [ ] Invalid input returns HTTP 422 with structured error response: `{"errors": [{"field": "price", "message": "..."}]}`
- [ ] XSS: HTML input stripped or escaped on all text fields
- [ ] SQL injection: all queries use entity API or parameterized queries (no raw string concat)
- [ ] DEC-001 resolved: validation strategy documented

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Fuzzing tests for each endpoint |
| G4 Adversarial | PENDING | SQL injection + XSS test suite |

---

## Dependencies

- Depends on: All write endpoints must exist (G-COMMERCE U-030, G-CERTIFICATE, G-COMMUNICATION SPEC units)
- Resolves: DEC-001 (Open Decision)

---

**Feature Flag:** None

*End of U-057-api-input-validation.md*
