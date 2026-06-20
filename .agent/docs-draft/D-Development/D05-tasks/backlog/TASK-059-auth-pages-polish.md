# Task: Auth Pages Polish (Login / Register / Forgot Password / Email Verification)

**ID:** TASK-059
**Unit:** — (cross-unit: Drupal user module + `lms_base` hooks + `lms_theme`)
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Brand và polish các auth pages theo thiết kế Bootstrap 5.3 dark premium của `lms_theme`. Hiện tại các form từ Drupal core user module chưa được styled đồng nhất với hệ thống. Cần override template, thêm password strength meter, và dựng placeholder cho social login (tắt mặc định bằng feature flag).

Luồng email verification cho user đăng ký mới (WF-007) phải hoạt động end-to-end: gửi email xác nhận, click link để activate account, redirect về `/login` với success message.

## Acceptance Criteria
- [ ] `/login` page: branded, responsive, error state, "Remember me" checkbox, "Forgot password?" link
- [ ] `/register` page: branded, password strength meter (JS), all validation messages visible inline
- [ ] `/forgot-password` page: email input, submit, success/error feedback
- [ ] Email verification flow (WF-007): user nhận email → click link → account activated → redirect `/login`
- [ ] Social login placeholder buttons (Google, Facebook) hiển thị khi `FF_FEAT_SOCIAL_AUTH = true`, ẩn khi `false`
- [ ] All pages match HTML prototypes tại `.agent/docs/C-Design/C10-prototypes/html/public/login.html` và `register.html`
- [ ] Responsive mobile layout pass trên Chromium + Firefox
- [ ] No PHP warnings / Twig errors trong watchdog

## Technical Notes
- Override Drupal user templates: `user-login-form.html.twig`, `user-register-form.html.twig`, `user-pass.html.twig` trong `web/themes/custom/lms_theme/templates/user/`
- Thêm hook `hook_form_alter()` trong `web/modules/custom/lms_base/lms_base.module` cho password strength meter JS
- JS library `lms_theme/password-strength` (cần define trong `lms_theme.libraries.yml`)
- Feature flag `FF_FEAT_SOCIAL_AUTH` check qua `lms_base` LmsHelper hoặc Drupal State
- Email verification: Drupal core `user.mail` config + custom WF-007 hook trong `lms_base`
- SCSS: thêm `_auth.scss` vào `web/themes/custom/lms_theme/scss/`

## Dependencies
- None (Drupal user module là core)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (form submission + email verification flow)
- [ ] HTML prototype parity verified (screenshot comparison)
- [ ] `FF_FEAT_SOCIAL_AUTH` feature flag documented trong `H-Operations/H05-feature-flags.md`
