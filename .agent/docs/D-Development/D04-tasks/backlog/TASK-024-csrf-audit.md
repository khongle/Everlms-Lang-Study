# Task: CSRF Protection Audit

**ID:** TASK-024
**Unit:** U-066
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG (0% — CRITICAL)

## Audit Notes
- **Zero CSRF token usage** across entire codebase (csrf_token, X-CSRF-Token = 0 matches)
- **8 POST API endpoints without CSRF protection**:
  1. `/api/lms/progress/mark-complete` — marks lessons complete
  2. `/api/lms/websdk/signature` — generates Zoom JWT
  3. `/api/lms/wishlist/toggle` — toggles wishlist
  4. `/api/lms/note/save` — saves notes
  5. `/api/lms/note/delete` — deletes notes
  6. `/workspace/content/bulk` — bulk content operations
  7. `/workspace/users/bulk` — bulk user operations
  8. Quiz submit route
- All rely solely on `_user_is_logged_in: TRUE` — vulnerable to CSRF
- **Severity: CRITICAL**

## Description
Audit CSRF protection trên tất cả state-changing operations (POST/PUT/DELETE).

## Acceptance Criteria
- [ ] Tất cả forms dùng Drupal Form API (tự có CSRF token)
- [ ] Custom AJAX endpoints có CSRF token verification
- [ ] API endpoints authenticated properly
- [ ] No state-changing GET requests

## Technical Notes
- Drupal Form API tự inject CSRF token
- Check: custom routes không dùng Form API

## Dependencies
- None

## Definition of Done
- [ ] Audit complete
- [ ] G4 Security category pass
