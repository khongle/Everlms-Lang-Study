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
Một visitor tạo một account web hoặc account mobile và đạt tới state onboarding
`student` active.

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
- Public web registration đã bật (`user.settings:register = visitors`).
- Web email verification đã bật (`user.settings:verify_mail = true`).
- `lms_mobile_api` đã bật để route `/api/v1/user/*` tồn tại.

## Trigger
Submit web registration hoặc request mobile OTP.

## Main success scenario
1. Web visitor mở `/user/register`.
2. Web visitor điền email và username, rồi submit form.
3. Drupal tạo một user active và gán `student`; vì email
   verification đã bật, việc thiết lập password tiếp tục qua hướng dẫn trong email.
4. Web visitor thấy một alert thành công nổi bật trên trang chủ.
5. Mobile client gọi `POST /api/v1/user/register` kèm name, email, password,
   và password confirmation.
6. System tạo một user `student` ở trạng thái blocked và gửi/lưu một OTP.
7. Mobile client có thể gọi `POST /api/v1/user/register/resend-otp`.
8. Mobile client gọi `POST /api/v1/user/register/verify-otp` với
   token/code OTP đang active.
9. System activate user mobile và trả về một bearer token.
10. Mobile client gọi `GET /api/v1/user/me` với `X-Authorization: Bearer ...`
    và nhận về profile user kèm role `student`.

## Alternate and exception flows
- OTP hết hạn → resend (`register/resend-otp`).
- Email mobile trùng → `422 validation_error` kèm lỗi vi phạm email.
- OTP không hợp lệ/hết hạn → `invalid_otp`.

## State transitions (evidence)
- `submitted` → `active`: web registration active ngay với role student.
- `submitted` → `pending_verify`: mobile registration blocked, chờ OTP.
- `pending_verify` → `active`: OTP hợp lệ, bearer token được cấp.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login / Register | Public | /user/register |
| Mobile register | API | /api/v1/user/register |
| Mobile resend OTP | API | /api/v1/user/register/resend-otp |
| Mobile verify OTP | API | /api/v1/user/register/verify-otp |
| Mobile profile | API | /api/v1/user/me |

## Supporting pages
Web registration bắt đầu tại Login / Register (P016); sau khi active, user hoàn tất thông tin tại Profile (P028). Manager quản lý role/block tại Manage users (P035); administrator debug qua impersonation tại Switch user (P051).

## Acceptance and test contract
- Web registration ghi lại screenshot trước khi điền, sau khi điền, và xác nhận
  đã submit.
- Account web ở trạng thái active và có `student`.
- Mobile registration trả về `otp_sent` và tạo một user pending ở trạng thái blocked với
  `student`.
- Mobile resend trả về một token OTP mới.
- Verify OTP mobile activate account và trả về một bearer token.
- `/api/v1/user/me` trả về `student` khi gọi kèm bearer token đó.
- Email mobile trùng trả về `422 validation_error`.
- Verify DB ghi nhận cả user id web và mobile, active status, và role.
