# D03 — Unit Spec: Error Handling Standardization

**Unit ID:** U-067
**Group:** G-HARDENING
**Title:** Error handling standardization
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned

---

## Scope

Standardize error responses across all lms_* API endpoints and service classes. Define a consistent error response format, ensure no raw PHP exceptions leak to end users, and establish error category taxonomy.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Exception/LmsException.php` | Create | lms_base |
| `src/Exception/ValidationException.php` | Create | lms_base |
| `src/Exception/AccessDeniedException.php` | Create | lms_base |
| `src/Exception/NotFoundException.php` | Create | lms_base |
| `src/EventSubscriber/ExceptionSubscriber.php` | Create | lms_base |
| All controllers with try/catch | Audit + Modify | All lms_* modules |

---

## Interfaces / Services

**Standard JSON error format:**
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Human-readable message",
    "fields": {"field_name": "error detail"}
  }
}
```

- HTTP codes: 400 (bad request), 403 (access denied), 404 (not found), 422 (validation), 500 (server error)
- `ExceptionSubscriber` catches `LmsException` subclasses → formats JSON response
- Raw `\Exception` → log to watchdog, return generic 500 (no stack trace to client)
- Drupal `AccessDeniedException` → 403 JSON response

---

## Acceptance Criteria

- [ ] No PHP exception stack trace exposed in any API response
- [ ] All 4xx responses use standard JSON format
- [ ] 500 responses log full exception to watchdog but return generic message to client
- [ ] Validation errors (U-057) use 422 with `fields` object
- [ ] Error codes documented (enum of all `code` values)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Each exception type produces correct HTTP response |
| G4 Adversarial | PENDING | Verify no stack trace leakage |

---

## Dependencies

- U-057 — validation errors use this format
- Should be implemented early in G-HARDENING wave

---

**Feature Flag:** None

*End of U-067-error-handling-standardization.md*
