# WF-007 - User registration & onboarding

## Business flow
A visitor registers (web form or mobile OTP via `lms_mobile_api`). On the web, Drupal core registration creates the account with the `student` role; on mobile, `MobileAuthController` + `OtpService` verify a one-time code and issue a bearer token. The new user receives a welcome/verification notification (F-017), completes profile basics, and lands on the catalog/dashboard ready to enroll (WF-001).

## Participating modules
user (Drupal core), [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Related features (B03)
[F-021](../B03-features/F-021-users-roles-and-impersonation.md), [F-019](../B03-features/F-019-mobile-app-api.md)

## Trigger
Visitor submits registration (web) or requests/verifies OTP (mobile).

## Outcome
An authenticated account with the `student` role (web) or a valid bearer token (mobile); onboarding notification sent.

## Design link
[C12 WF-007](../../C-Design/C12-workflows/WF-007-user-registration-and-onboarding.md)
