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
A visitor creates a web account or mobile account and reaches an active
`student` onboarding state.

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
- Public web registration is enabled (`user.settings:register = visitors`).
- Web email verification is enabled (`user.settings:verify_mail = true`).
- `lms_mobile_api` is enabled so `/api/v1/user/*` routes exist.

## Trigger
Submit web registration or request mobile OTP.

## Main success scenario
1. Web visitor opens `/user/register`.
2. Web visitor fills email and username, then submits the form.
3. Drupal creates an active user and assigns `student`; because email
   verification is enabled, password setup continues through email instructions.
4. Web visitor sees a prominent success alert on the homepage.
5. Mobile client calls `POST /api/v1/user/register` with name, email, password,
   and password confirmation.
6. System creates a blocked `student` user and sends/stores an OTP.
7. Mobile client optionally calls `POST /api/v1/user/register/resend-otp`.
8. Mobile client calls `POST /api/v1/user/register/verify-otp` with the active
   OTP token/code.
9. System activates the mobile user and returns a bearer token.
10. Mobile client calls `GET /api/v1/user/me` with `X-Authorization: Bearer ...`
    and receives the user profile with `student` role.

## Alternate and exception flows
- OTP expired → resend (`register/resend-otp`).
- Duplicate mobile email → `422 validation_error` with an email violation.
- Invalid/expired OTP → `invalid_otp`.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login / Register | Public | /user/register |
| Mobile register | API | /api/v1/user/register |
| Mobile resend OTP | API | /api/v1/user/register/resend-otp |
| Mobile verify OTP | API | /api/v1/user/register/verify-otp |
| Mobile profile | API | /api/v1/user/me |

## Acceptance and test contract
- Web registration captures before-fill, after-fill, and submitted confirmation
  screenshots.
- Web account is active and has `student`.
- Mobile registration returns `otp_sent` and creates a blocked pending user with
  `student`.
- Mobile resend returns a new OTP token.
- Mobile OTP verification activates the account and returns a bearer token.
- `/api/v1/user/me` returns `student` when called with that bearer token.
- Duplicate mobile email returns `422 validation_error`.
- DB verification records both web and mobile user ids, active status, and roles.
