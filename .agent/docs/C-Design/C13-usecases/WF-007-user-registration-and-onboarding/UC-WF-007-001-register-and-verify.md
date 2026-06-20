---
usecase_id: UC-WF-007-001
workflow: WF-007-user-registration-and-onboarding
status: as-built
primary_actor: Visitor
modules: [user, lms_mobile_api, lms_notification]
processes: [user-registration-and-onboarding]
---

# UC-WF-007-001 - Register and verify (web/mobile)

## Intent
A visitor creates and verifies an account.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-007 |
| Business flow | register → verify → active |
| Modules | user, lms_mobile_api, lms_notification |
| Related features | F-021, F-019 |
| B04 source | WF-007 |

## Actors
Visitor (primary); system.

## Preconditions
None (public).

## Trigger
Submit web registration or request mobile OTP.

## Main success scenario
1. Web: submit `/user/register` → account + `student` role; verify email.
2. Mobile: `POST /api/v1/user/register` → OTP → `verify-otp` → bearer token.
3. Welcome notification sent.
4. Land on dashboard/catalog.

## Alternate and exception flows
- OTP expired → resend (`register/resend-otp`).
- Duplicate email → error.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login / Register | Public | /user/register |

## Acceptance and test contract
- Web account gets `student`; mobile OTP → token (B05 F-019/F-021).
