# Task: Logging for Critical Operations

**ID:** TASK-020
**Unit:** U-068
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG (55% — partial coverage)

## Audit Notes
- 28 files contain logging across codebase
- Payment gateways: all log at `notice` level. Stripe logs errors on failure ✅
- Certificate generation: logs mPDF errors ✅
- Zoom: comprehensive logging via injected LoggerInterface ✅
- **Gaps**: No logging in ProgressApiController. No logging in quiz submission. No logging in order status transitions beyond payment. No structured logging format. No security event log levels.

## Description
Thêm structured logging cho các thao tác critical: payment, grading, certificate, AI generation, access control failures.

## Acceptance Criteria
- [ ] Payment operations logged (create order, webhook, status change, refund)
- [ ] Grading operations logged (quiz submit, score calculation, manual grade)
- [ ] Certificate operations logged (issue, revoke, verify)
- [ ] AI operations logged (request, response, error, rollback)
- [ ] Access control failures logged (403 events)
- [ ] Log format consistent: timestamp, user, action, entity, result

## Technical Notes
- Drupal logger: `\Drupal::logger('lms_payment')->info(...)`
- Structured context array per log entry
- Admin log review via Drupal dblog hoặc custom log viewer

## Dependencies
- None

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Log coverage cho tất cả critical ops
