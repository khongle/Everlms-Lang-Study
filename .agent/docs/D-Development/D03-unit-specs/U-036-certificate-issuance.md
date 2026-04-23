# D03 — Unit Spec: Certificate Issuance Service

**Unit ID:** U-036
**Group:** G-CERTIFICATE
**Title:** Certificate issuance service (auto trigger)
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Auto-issue a certificate when a user completes a course (progress = 100% + quiz passing grade if required). Service must be idempotent (no duplicate certificates), generate a unique verification code, and dispatch a notification.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/CertificateIssuanceService.php` | Create | lms_certificate |
| `src/EventSubscriber/CourseCompletionSubscriber.php` | Create | lms_certificate |
| `src/Event/CourseCompletedEvent.php` | Create | lms_progress |

---

## Interfaces / Services

- `CertificateIssuanceService::issue(int $userId, int $courseId): ?UserCertificate`
  - Returns null if already issued (idempotent)
  - Sets `verification_code` = UUID v4
  - Sets `issued_at` = now
- Listens to `CourseCompletedEvent` dispatched by progress cascade service (U-022)
- Dispatches `NotificationEvent` (U-042) after issuance

---

## Acceptance Criteria

- [ ] Certificate issued automatically when course progress hits 100%
- [ ] No duplicate certificate for same user+course pair
- [ ] `verification_code` is unique UUID v4, stored in `UserCertificate`
- [ ] `CertificateTemplate` for the course is resolved; fallback to global default template
- [ ] Notification sent to user on issuance (U-042 dependency — degrade gracefully if missing)
- [ ] Certificate visible in user's profile/dashboard immediately after issuance

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Idempotency test required |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-035 | CertificateTemplate + UserCertificate entities |
| U-022 | Progress cascade service — emits completion event |
| U-042 | Notification dispatch (soft dependency) |

---

**Feature Flag:** None

*End of U-036-certificate-issuance.md*
