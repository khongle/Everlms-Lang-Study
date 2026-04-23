# Task: Error Handling Standardization

**ID:** TASK-025
**Unit:** U-067
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG (20% — poor)

## Audit Notes
- **Zero custom Exception classes** found (find */Exception/*.php = empty)
- **No global error handler** or event subscriber for exceptions
- try/catch in ~47 locations but ALL catch generic `\Exception`
- ZoomController::joinMeeting() throws raw `\Exception` → stack trace exposed to users
- Payment forms catch exceptions but use static `\Drupal::logger()` and generic messenger

## Description
Standardize error handling: custom exception classes, global error handler, user-friendly error pages.

## Acceptance Criteria
- [ ] Custom exception hierarchy (LmsException, PaymentException, CertificateException, etc.)
- [ ] Global exception handler cho unhandled errors
- [ ] User-friendly 403/404/500 error pages
- [ ] API errors return structured JSON responses
- [ ] No stack traces exposed to users
- [ ] All exceptions logged với context

## Technical Notes
- Exception classes trong `lms_base/src/Exception/`
- Error pages: custom Twig templates
- API: JsonResponse with error structure

## Dependencies
- None

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] No information leakage in error responses
