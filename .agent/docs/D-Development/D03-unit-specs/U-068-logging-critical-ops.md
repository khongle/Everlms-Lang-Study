# D03 — Unit Spec: Logging for Critical Operations

**Unit ID:** U-068
**Group:** G-HARDENING
**Title:** Logging for critical operations
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned

---

## Scope

Add structured watchdog logging to all critical operations: payment processing, order status changes, membership activation/expiry, certificate issuance, user role changes, AI generation events, and security events (failed auth, access denied, signature verification failure).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/LoggingService.php` | Create | lms_base |
| All service classes handling critical ops | Modify | All lms_* modules |

---

## Interfaces / Services

- `LoggingService::critical(string $event, array $context): void` → `\Drupal::logger()->critical()`
- `LoggingService::security(string $event, array $context): void` → `\Drupal::logger()->warning()`
- `LoggingService::audit(string $event, array $context): void` → `\Drupal::logger()->info()`

**Critical events that MUST be logged:**
| Event | Level | Context fields |
|-------|-------|---------------|
| Payment gateway call | info | order_id, gateway, amount |
| Order status change | info | order_id, old_status, new_status, user_id |
| Membership activate/expire | info | membership_id, user_id, plan_id |
| Certificate issued | info | cert_id, user_id, course_id |
| Webhook received | info | gateway, event_type, event_id |
| Webhook sig verification fail | warning | gateway, remote_ip |
| AI generation started/completed | info | session_id, provider |
| AI generation failed | error | session_id, exception_message |
| Access denied (403) | warning | route, user_id, remote_ip |

---

## Acceptance Criteria

- [ ] All critical events in the table above produce watchdog log entries
- [ ] Log entries include structured context (no bare string concatenation)
- [ ] Security events logged at `warning` level minimum
- [ ] Log entries do not include raw API keys, passwords, or PII beyond user_id
- [ ] Logs visible in Drupal admin `/admin/reports/dblog`

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Verify log entries created for each event type |
| G4 Adversarial | PENDING | Verify no sensitive data in log context |

---

## Dependencies

- U-067 — exception subscriber also logs to watchdog
- Best run alongside U-067

---

**Feature Flag:** None

*End of U-068-logging-critical-ops.md*
