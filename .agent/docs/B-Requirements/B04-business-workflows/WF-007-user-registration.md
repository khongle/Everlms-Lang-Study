# WF-007 — User Registration

**ID:** WF-007
**Trigger:** Visitor click Register hoặc chọn Social Login
**Actors:** Visitor, System, Email Service, OAuth Provider (optional)
**Preconditions:** Email chưa tồn tại trong hệ thống (đối với email registration).
**Related Features:** F-007 (User Management)
**Related Entities:** User, UserProfile

---

## Happy Path — Email Registration

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Visitor | Click "Register" | Hiển thị registration form |
| 2 | Visitor | Điền form (name, email, password) | Client-side validation real-time |
| 3 | Visitor | Submit form | Server validation: unique email, password strength |
| 4 | System | Tạo account (status=pending) | Gửi verification email |
| 5 | Visitor | Click link trong email | Activate account (status=active) |
| 6 | System | Assign role Student | Redirect đến dashboard |
| 7 | System | (Optional) Hiển thị welcome tour | Onboarding flow |

## Happy Path — Social Login (OAuth)

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Visitor | Click "Continue with Google/Facebook" | OAuth redirect đến provider |
| 2 | Provider | User authenticates | Return profile data (name, email) |
| 3 | System | Check email đã tồn tại chưa | Nếu có: link social account; Nếu không: tạo mới |
| 4 | System | Activate account (không cần email verify) | Assign role Student |
| 5 | System | Redirect dashboard | Logged in |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 2a | Email đã tồn tại | Báo lỗi inline | User dùng "Forgot Password" |
| 2b | Password yếu | Báo lỗi inline với requirements | User chỉnh sửa |
| 4a | Email server lỗi | Retry gửi email, cho user resend | Resend verification link |
| 5a | Verification link hết hạn | Hiển thị "Link expired" | Offer resend |

## Registration Form Fields

| Field | Required | Validation |
|-------|----------|-----------|
| Full Name | Yes | Min 2 chars |
| Email | Yes | Valid format, unique |
| Password | Yes | Min 8 chars, 1 uppercase, 1 number |
| Confirm Password | Yes | Must match |
| Terms & Conditions | Yes | Must accept |

## Business Rules

- Email verification bắt buộc cho email registration.
- Social login không cần email verification (provider đã verify).
- Default role sau registration: **Student**.
- Instructor role: chỉ admin/manager cấp sau khi review.
- Duplicate email: nếu social email trùng email account, tự động link (không tạo duplicate).

## Postconditions

- User account với status=active.
- Role Student assigned.
- Profile record tạo.
- User có thể đăng nhập và browse courses.

## Related Docs

- B03 Feature: [F-007 User Management](../B03-features/07-user-management.md)
- INT: [SMTP Mail Service](../B06-integration-requirements/INT-007-smtp.md)
