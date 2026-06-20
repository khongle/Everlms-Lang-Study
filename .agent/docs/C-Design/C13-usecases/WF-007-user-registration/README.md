---
workflow: WF-007
title: User Registration & Login — Use-Case Catalog
status: as-built
---

# C13 — WF-007: User Registration & Login — Use-Case Catalog

Use cases covering both registration paths: (1) web registration via Drupal user form altered by `lms_base`, and (2) mobile OTP registration via `lms_mobile_api`. Social/OAuth login is **not implemented** — no OAuth module or social login integration exists in the codebase.

## Files in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-WF007-001-web-registration.md](UC-WF007-001-web-registration.md) | UC-WF007-001 | happy | Visitor registers via `/user/register`; student role auto-assigned |
| [UC-WF007-002-mobile-otp-registration.md](UC-WF007-002-mobile-otp-registration.md) | UC-WF007-002 | happy | Flutter app registers via OTP flow; bearer token issued on verify |
| [UC-WF007-003-otp-expired-registration.md](UC-WF007-003-otp-expired-registration.md) | UC-WF007-003 | error | Mobile OTP session expires before verification; client must restart |

## Cross-links

- Workflow: [../../../B-Requirements/B04-business-workflows/WF-007-user-registration.md](../../../B-Requirements/B04-business-workflows/WF-007-user-registration.md)
- Acceptance criteria: [../../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md](../../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md)
- Primary modules: `lms_base`, `lms_mobile_api`
- Entities mutated: `user` (Drupal core), keyvalue store `lms_mobile_api.tokens`, `lms_mobile_api.otp`

## Implementation status

| Scenario | Status |
|----------|--------|
| Web registration + student role auto-assign | as-built (`lms_base` `hook_user_insert`) |
| Email-based login (web) | as-built (`lms_base_user_login_validate`) |
| Mobile OTP register + verify | as-built (`MobileAuthController`) |
| Mobile bearer token (keyvalue.expirable) | as-built |
| Mobile forgot-password / reset flow | as-built |
| OTP resend | as-built |
| Social / OAuth login | not implemented — config keys are unused artifacts |
| No refresh token for mobile bearer | missing — clients must re-authenticate after 7-day TTL |
