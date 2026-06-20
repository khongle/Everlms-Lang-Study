# ISSUE-0008 - No rate limiting on /api/v1

**Severity:** P2 · **Status:** open · **Risk:** R-007

## Description
The mobile API (`/api/v1`, ~22 routes incl. OTP) has no documented rate limiting / throttling.

## Impact
OTP/auth endpoints vulnerable to brute force/abuse; resource exhaustion risk.

## Fix
Add per-IP/per-user rate limits (esp. OTP request/verify, login); document quotas in [I06](../../../I-External/I06-rate-limits-quotas/_index.md).
