# C13 Usecases - WF-007 User registration & onboarding

## Workflow
- C12: [WF-007](../../C12-workflows/WF-007-user-registration-and-onboarding.md) · B04: [WF-007](../../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md)
- Modules: user, lms_mobile_api, lms_notification
- Business flow: web register -> hướng dẫn qua email -> student active; mobile
  register -> OTP/resend -> verify -> bearer token.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-007-001](UC-WF-007-001-register-and-verify.md) | Register and verify (web/mobile) | Visitor | User registration | P016 |

## Completion rule
Đóng khi form registration web tạo được một account `student` active và
mobile API tạo được một account blocked đang pending, resend OTP, verify OTP,
activate account, trả về bearer token, và `/api/v1/user/me` trả về đúng
role `student`. Bằng chứng (evidence) phải bao gồm screenshot cho web trước/sau/submit,
trạng thái request/response của API, validation email trùng, và verify
user-role trong DB.
