# Task: Transactional Email Template Translations (VI + EN)

**ID:** TASK-113
**Unit:** — (cross-cutting: i18n + email)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Tất cả transactional emails của EverLMS phải có bản VI và EN. Các email cần dịch: welcome, enrollment-confirmation, certificate-issued, password-reset, payment-receipt. Thiết lập template inheritance pattern để dễ maintain (base layout shared, chỉ content block thay đổi). Ngôn ngữ email theo language preference của user account.

## Acceptance Criteria
- [ ] 5 loại email × 2 ngôn ngữ = 10 email templates hoàn chỉnh:
  - [ ] Welcome email (VI + EN)
  - [ ] Enrollment confirmation (VI + EN)
  - [ ] Certificate issued (VI + EN)
  - [ ] Password reset (VI + EN)
  - [ ] Payment receipt (VI + EN)
- [ ] Template inheritance: base HTML template `email-base.html.twig` (header, footer, brand colors) — content templates extend base
- [ ] Language selection: email gửi theo `user.preferred_langcode` — nếu null, fallback VI
- [ ] VND formatting trong payment-receipt: dùng `CurrencyFormatter` từ TASK-112
- [ ] Date format trong templates: `d/m/Y H:i` cho VI; `Y-m-d H:i` cho EN
- [ ] Subject line đúng ngôn ngữ (không chỉ body)
- [ ] HTML email: inline CSS, max-width 600px, responsive (mobile-friendly cho Gmail app)
- [ ] Plain text fallback: mỗi template có bản text/plain (multipart/alternative)
- [ ] Test email preview: `drush ev "\Drupal::service('plugin.manager.mail')->mail(...)"` — verify render đúng
- [ ] Email template files tại: `web/modules/custom/lms_base/templates/email/`
- [ ] PHPUnit test: `EmailTemplateTest` verify correct language selection + token replacement

## Technical Notes
- Drupal mail: implement hook `hook_mail()` trong `lms_base.module` hoặc mail plugin
- Twig templates cho email: `lms_base/templates/email/welcome--vi.html.twig`, `welcome--en.html.twig`
- Template suggestion: `hook_theme_suggestions_HOOK()` để pick đúng language template
- Tokens: `[user:display-name]`, `[site:name]`, `[lms_course:title]`, `[lms_certificate:url]`
- Existing email implementation: check `lms_base` hoặc `lms_notification` module (TASK-011)
- SMTP config: dùng `drupal/swiftmailer` hoặc `drupal/symfony_mailer` + SMTP server config
- Tham chiếu: TASK-011 (email notification), TASK-110 (i18n infra), TASK-112 (VND + date format)

## Dependencies
- TASK-011 (email notification — base email implementation)
- TASK-110 (i18n infrastructure)
- TASK-112 (currency + date formatter)

## Definition of Done
- [ ] Code complete (10 templates + EmailTemplateTest committed)
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] Manual test: send test email trong cả VI + EN — screenshot attached to MR
- [ ] No new debt introduced
