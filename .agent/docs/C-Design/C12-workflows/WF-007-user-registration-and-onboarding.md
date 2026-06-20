# C12 Workflow - WF-007 User registration & onboarding

## Business source
[B04 WF-007](../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md)

## Modules
user (core), [lms_mobile_api](../C05-modules/lms_mobile_api.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md), [F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md)

## State model
account lifecycle:
| State | Meaning |
| ----- | ------- |
| submitted (registered) | account created / OTP requested |
| in_review (pending verify) | email/OTP verification pending |
| approved_or_completed (active) | verified; `student` role assigned |
| archived (blocked) | account blocked |

## Permission model
- Initiator: visitor (register).
- Admin: manager creates/blocks users; assigns roles.

## Notifications
Welcome/verification (web email; mobile OTP). Mobile bearer token issued on OTP verify.
