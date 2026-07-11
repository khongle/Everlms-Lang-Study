# ISSUE-0008 - No rate limiting on /api/v1

**State:** open
**Severity:** P2 · **Module:** [lms_mobile_api](../../../C-Design/C05-modules/lms_mobile_api.md) · **Story:** [E11S01-lms_mobile_api](../../../D-Development/D03-stories/E11S01-lms_mobile_api.md) · **Risk:** R-007

## Summary
The mobile API (`/api/v1`, ~22 routes incl. OTP request/verify) has no documented rate limiting / throttling, leaving auth endpoints open to brute-force and resource-exhaustion abuse.

## Steps to reproduce
1. `for i in {1..50}; do curl -s -X POST https://<host>/api/v1/auth/otp/verify -d '{"phone":"<test>","otp":"000000"}'; done`.
2. Observe every request is accepted for processing (no 429 Too Many Requests, no lockout after N failures).
3. Check `lms_mobile_api.routing.yml` and any middleware/event subscriber → no throttle logic present.

## Root cause
The mobile API routes were built for functional coverage (auth, catalog, learning parity with web) without a companion rate-limiting layer; Drupal core has no built-in per-route throttle, and none was added during `lms_mobile_api` development.

## Fix
Add per-IP/per-user rate limits, especially on OTP request/verify and login; document the quotas in [I06](../../../I-External/I06-rate-limits-quotas/_index.md) so mobile-client and gateway/proxy configuration can align on the same limits.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `api-auth-parity` Functional test extended with a rate-limit assertion: the (N+1)th OTP-verify attempt within the configured window returns HTTP 429.
