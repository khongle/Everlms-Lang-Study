# WF-007 - User registration & onboarding

## Business flow
Visitor đăng ký (qua web form hoặc mobile OTP qua `lms_mobile_api`). Trên web, cơ chế đăng ký của Drupal core tạo account với role `student`; trên mobile, `MobileAuthController` + `OtpService` xác thực mã dùng một lần (one-time code) rồi cấp bearer token. User mới nhận thông báo chào mừng/xác thực (F-017), hoàn tất thông tin profile cơ bản, và đến catalog/dashboard sẵn sàng để enroll (WF-001).

## Participating modules
user (Drupal core), [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Related features (B03)
[F-021](../B03-features/F-021-users-roles-and-impersonation.md), [F-019](../B03-features/F-019-mobile-app-api.md)

## Trigger
Visitor gửi đăng ký (web) hoặc yêu cầu/xác thực OTP (mobile).

## Outcome
Một account đã xác thực với role `student` (web) hoặc một bearer token hợp lệ (mobile); thông báo onboarding được gửi.

## Design link
[C12 WF-007](../../C-Design/C12-workflows/WF-007-user-registration-and-onboarding.md)
