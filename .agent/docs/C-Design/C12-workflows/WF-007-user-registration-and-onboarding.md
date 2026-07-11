# C12 Workflow - WF-007 User registration & onboarding

**Object:** account

## Business source
[B04 WF-007](../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md)

## Modules
user (core), [lms_mobile_api](../C05-modules/lms_mobile_api.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md), [F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md)

## State model
Vòng đời tài khoản:

| State | Meaning |
| ----- | ------- |
| submitted (registered) | Web account created, or mobile registration request accepted. |
| pending verify | Web email instruction sent, or mobile account blocked while OTP is pending. |
| active | Account is active with the `student` role. Mobile verification also issues a bearer token. |
| blocked/archived | Account blocked by admin or invalid/stale pending mobile registration. |

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | active | web registration active ngay với role student |
| submitted | pending_verify | mobile registration blocked, chờ OTP |
| pending_verify | active | OTP hợp lệ, bearer token được cấp |

## Permission model
- Initiator: visitor (register).
- Admin: manager tạo/block user; gán role.

## Concrete flow
### Web registration
1. Visitor mở `/user/register`.
2. Vì `user.settings:verify_mail` là true, form web chỉ thu thập email và
   username. Việc tạo password diễn ra qua hướng dẫn gửi trong email.
3. Visitor submit form.
4. Drupal tạo một account active và `lms_base_user_insert()` gán
   role `student`.
5. Trang chủ hiển thị alert thành công xác nhận rằng hướng dẫn qua email
   đã được gửi.

### Mobile registration
1. Mobile client gọi `POST /api/v1/user/register` kèm name, email, password,
   và password confirmation.
2. `lms_mobile_api` tạo một user ở trạng thái blocked với role `student` và lưu
   một session OTP 6 chữ số trong expirable key-value store.
3. Mobile client có thể gọi `POST /api/v1/user/register/resend-otp`; token OTP
   cũ bị vô hiệu và một token mới được cấp.
4. Mobile client gọi `POST /api/v1/user/register/verify-otp`.
5. Khi OTP hợp lệ, user được kích hoạt (activate) và một bearer token được trả về.
6. Mobile client gọi `GET /api/v1/user/me` dùng `X-Authorization: Bearer ...`
   và nhận về profile đã authenticated kèm role.

## Implementation requirements
- `lms_mobile_api` phải được enable trong active config và config sync; nếu không
  các route `/api/v1/user/*` đã tài liệu hóa sẽ trả về 404.
- Đăng ký mobile với email trùng trả về `422 validation_error` **khi đã có account active dùng email đó**. Đăng ký lại với một email còn đang pending (blocked/chưa verify) vẫn được cho phép — user blocked cũ bị xóa và một session OTP mới được cấp (trải nghiệm retry). *(Đã verify ở round-2: email active trùng → 422; đăng ký lại khi đang pending → OTP mới.)*
- Screenshot của API nên redact OTP/access token trong khi vẫn chứng minh được status code và
  cấu trúc response.
- Screenshot web phải ghi lại trạng thái trước khi điền, sau khi điền, và
  xác nhận đã submit.

## Notifications
Chào mừng/xác minh (email cho web; OTP cho mobile). Bearer token của mobile được cấp khi verify OTP.

## Round-2 QA evidence
Đã verify hoạt động tốt (không cần sửa code). Web: `/user/register` → account active + role `student` (uid 106) + alert thành công trên trang chủ. Mobile API end-to-end: register → verify-otp (uid 109, Bearer, `expires_in` 604800) → `/api/v1/user/me` (role `student`) → email trùng đã active `422`. Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/007-user-registration-and-onboarding/` (bao gồm `09-mobile-api-flow.txt`, OTP/token đã redact).
