---
page: Login / Register
drupal_concept: core user forms
mode: as-built
owner_module: user
route_group: public
route_name: user.login
prototype_type: auth-util
adequacy: PASS
---

# C11 Page - Login / Register

## Purpose
Truy cập tài khoản tại `/user/login` và `/user/register` (auth-util). Tài khoản web mới được gán role `student`; mobile dùng OTP (F-019/WF-007).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/user/login`, `/user/register`, `/user/password` |
| Route name | `user.login` (core) |
| Primitive | core user forms (themed) |
| Access | anonymous |
| Cache | uncacheable (forms) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Auth forms | core user module | — | — | register → student account |

## Page States
Normal: form · Empty: n/a · Access denied: authed → redirect · Validation error: bad creds → error · Loading: submit.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA đăng nhập | "Log in" | core user module string (Login form submit button) |
| CTA đăng ký | "Create account" | core user module string (Register form submit button) |
| Error — sai thông tin đăng nhập | "Unrecognized username or password. Forgot your password?" | core user module string (`UserLoginForm::validateAuthentication`) | <!-- credential-sweep:allow: UI error string, không phải secret -->
| Error — email không hợp lệ | "The email address is not valid." | core user module string (mail field validation) |
| Error — mật khẩu quá ngắn (dưới 8 ký tự) | "Password must be at least 8 characters." | password policy module config (password policy.settings` — ràng buộc độ dài mật khẩu) |
| Error — email đã được đăng ký | "The email address is already taken." | core user module string (`UserMailUnique` constraint) |
| Success — đăng ký thành công | "A welcome message with further instructions has been sent to your email address." | core user module string; xem thêm WF-007-user-registration-and-onboarding.md ## Concrete flow |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.authwrap` đã ở chế độ 1 cột (`grid-template-columns:1fr`, kích hoạt từ ≤900px) nên `.authwrap__aside` (branding, số liệu, testimonial) bị ẩn hoàn toàn (`display:none`); chỉ còn `.authwrap__form` hiển thị, padding co theo `clamp(1.6rem,4vw,3.2rem)`, tab Log in/Register và form chiếm toàn bộ chiều rộng màn hình. |
| tablet (768–991px) | Ngưỡng ẩn panel trái của `.authwrap` là ≤900px (không trùng mốc chuẩn 768/991), nên phần lớn dải tablet (768–900px) vẫn hiển thị 1 cột như mobile (ẩn `.authwrap__aside`); chỉ từ khoảng 901–991px panel trái mới xuất hiện trở lại, đưa layout về 2 cột. |
| desktop (992–1199px) | Viewport vượt ngưỡng 900px nên `.authwrap{grid-template-columns:1fr 1fr}` áp dụng đầy đủ: panel trái (branding, thống kê, testimonial) và `.authwrap__form` (giới hạn `max-width:520px`, canh giữa) hiển thị song song thành 2 cột, `min-height:70vh`. |
| wide (≥1200px) | Bố cục 2 cột của `.authwrap` giữ nguyên như desktop; `.authwrap__form` vẫn giữ `max-width:520px` nên form không giãn rộng thêm, phần không gian dư được `.authwrap__aside` (padding `clamp(2rem,5vw,4rem)`) chiếm dụng nhiều hơn. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Auth | done | mobile OTP via lms_mobile_api |
