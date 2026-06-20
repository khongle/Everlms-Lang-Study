# C13 Usecases - WF-007 User registration & onboarding

## Workflow
- C12: [WF-007](../../C12-workflows/WF-007-user-registration-and-onboarding.md) · B04: [WF-007](../../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md)
- Modules: user, lms_mobile_api, lms_notification
- Business flow: register → verify → active.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-007-001](UC-WF-007-001-register-and-verify.md) | Register and verify (web/mobile) | Visitor | User registration | P016 |

## Completion rule
Closed when a verified account (web `student` role) or mobile bearer token exists.
