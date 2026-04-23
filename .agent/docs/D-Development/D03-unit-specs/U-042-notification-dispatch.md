# D03 — Unit Spec: Notification Dispatch Service

**Unit ID:** U-042
**Group:** G-COMMUNICATION
**Title:** Notification dispatch service
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Service that creates `Notification` entities for the right users based on system events (course enrollment, quiz result, certificate issued, new forum reply, membership expiry warning). Routes notifications to in-app center and optionally to email (U-044).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/NotificationDispatchService.php` | Create | lms_notification |
| `src/EventSubscriber/NotificationEventSubscriber.php` | Create | lms_notification |
| `src/Event/NotificationEvent.php` | Create | lms_notification |

---

## Interfaces / Services

- `NotificationDispatchService::dispatch(string $type, int $recipientId, array $context): Notification`
  - `$type`: one of 6 notification types (enrollment, quiz_result, certificate, forum_reply, membership_expiry, system)
  - Creates `Notification` entity with `is_read = FALSE`
  - Optionally queues email via U-044
- Listens to: `CourseCompletedEvent`, `QuizResultSavedEvent`, `CertificateIssuedEvent`, `ForumReplyCreatedEvent`, `MembershipExpiryWarningEvent`

---

## Acceptance Criteria

- [ ] Notification entity created for each supported event type
- [ ] Notification appears in user's notification center (U-081) immediately
- [ ] Batch dispatch: one system event can create notifications for multiple users (e.g., new course announcement)
- [ ] Notification count badge updated in UI (session cache invalidated)
- [ ] Dispatch is async-safe: can be called from event subscriber without breaking the originating request

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-039 | Notification entity — created by this service |
| U-044 | Email integration — called after entity creation (soft dep) |
| U-036 | Emits CertificateIssuedEvent |
| U-033 | Emits MembershipExpiryWarningEvent |

---

**Feature Flag:** None

*End of U-042-notification-dispatch.md*
