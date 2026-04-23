# D03 — Unit Spec: Membership Lifecycle

**Unit ID:** U-033
**Group:** G-COMMERCE
**Title:** Membership lifecycle (activate/expire/renew)
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Implement the full lifecycle of a UserMembership: activation on payment success, expiry checking (cron), renewal flow, and access control based on active membership. Covers both manual and auto-renewal scenarios.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/MembershipService.php` | Create | lms_membership |
| `src/EventSubscriber/MembershipSubscriber.php` | Create | lms_membership |
| `src/Plugin/QueueWorker/MembershipExpiryWorker.php` | Create | lms_membership |
| `lms_membership.cron.php` (or hook_cron) | Create | lms_membership |
| `templates/membership-status.html.twig` | Create | lms_membership |

---

## Interfaces / Services

- `MembershipService::activate(UserMembership $membership): void` — called after payment success
- `MembershipService::expire(UserMembership $membership): void` — called by cron or manual admin action
- `MembershipService::renew(UserMembership $membership, Order $order): void`
- `MembershipService::isActive(int $userId): bool` — for access checks
- Subscribes to: `OrderCompletedEvent` → activate; `hook_cron` → expire check

---

## Acceptance Criteria

- [ ] Membership activates automatically when order status → `completed`
- [ ] Membership `end_date` is calculated from plan duration (e.g. 30/90/365 days)
- [ ] Expired memberships are detected by daily cron and status → `expired`
- [ ] User loses membership-gated course access when membership expires
- [ ] Renewal creates new order and extends `end_date` from today (not from old end_date)
- [ ] Admin can manually activate/expire/renew a membership

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
| U-026 | UserMembership entity |
| U-030 | Checkout triggers activation via order completion |
| U-031 | Webhook triggers order completion event |

---

**Feature Flag:** None

*End of U-033-membership-lifecycle.md*
