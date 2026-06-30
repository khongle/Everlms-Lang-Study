# C13 Usecases - WF-007 User registration & onboarding

## Workflow
- C12: [WF-007](../../C12-workflows/WF-007-user-registration-and-onboarding.md) · B04: [WF-007](../../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md)
- Modules: user, lms_mobile_api, lms_notification
- Business flow: web register -> email instructions -> active student; mobile
  register -> OTP/resend -> verify -> bearer token.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-007-001](UC-WF-007-001-register-and-verify.md) | Register and verify (web/mobile) | Visitor | User registration | P016 |

## Completion rule
Closed when the web registration form creates an active `student` account and
the mobile API creates a blocked pending account, resends OTP, verifies OTP,
activates the account, returns a bearer token, and `/api/v1/user/me` returns the
`student` role. Evidence must include screenshots for web before/after/submit,
API request/response states, duplicate-email validation, and DB user-role
verification.
