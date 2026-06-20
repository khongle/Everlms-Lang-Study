# Task: Manage Settings Sub-Pages (Payment / Zoom / AI / Email)

**ID:** TASK-066
**Unit:** — (cross-unit: lms_payment, lms_zoom, lms_ai, lms_email / lms_base)
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build 4 admin config pages cho C07 sitemap entries dưới `/manage/settings/`. Mỗi trang là một Drupal ConfigForm expose module-relevant settings, lưu vào Drupal config system (config YAMLs). Admin (role) có thể thay đổi API keys, credentials, và runtime parameters mà không cần sửa code.

Settings nhạy cảm (API keys, secrets) phải lưu qua `Settings::get()` hoặc environment variable — không lưu trực tiếp trong config DB nếu có thể bị export ra repo.

## Acceptance Criteria
- [ ] `/manage/settings/payment` — Stripe: publishable key, secret key (masked), webhook secret, test mode toggle; PayPal: client ID, secret (masked), sandbox toggle
- [ ] `/manage/settings/zoom` — Zoom OAuth: Account ID, Client ID, Client Secret (masked), Webhook secret; meeting defaults (duration, timezone)
- [ ] `/manage/settings/ai` — AI provider selector (dropdown: OpenAI / Gemini / None), API key (masked), daily cost cap (USD), max requests per minute, prompt temperature
- [ ] `/manage/settings/email` — SMTP host, port, username, password (masked), encryption (TLS/SSL/None), from address, from name; "Send test email" button
- [ ] Tất cả sensitive fields (key, secret, password): display masked (`****`), có "Show/Hide" toggle JS
- [ ] Config được lưu vào Drupal config: `lms_payment.settings`, `lms_zoom.settings`, `lms_ai.settings`, `lms_email.settings`
- [ ] Form validation: URL format, numeric range, required fields
- [ ] "Send test email" button gọi `MailManager` và hiển thị success/fail inline
- [ ] Access: Admin role only

## Technical Notes
- Pattern: Drupal `ConfigFormBase` trong từng module:
  - `web/modules/custom/lms_payment/src/Form/PaymentSettingsForm.php`
  - `web/modules/custom/lms_zoom/src/Form/ZoomSettingsForm.php` (U-045)
  - `web/modules/custom/lms_ai/src/Form/AiSettingsForm.php` (U-047)
  - `web/modules/custom/lms_email/src/Form/EmailSettingsForm.php` hoặc `lms_base`
- Routes: thêm vào respective `*.routing.yml`, path `/manage/settings/{section}`
- Sensitive values: consider `key` module (drupal/key) hoặc environment variables via `$settings` trong `settings.php`
- Test email: inject `MailManagerInterface`, gửi test message đến admin email
- Menu: thêm `/manage/settings` overview link trong management menu
- SCSS: `_settings-forms.scss`

## Dependencies
- U-045 (ZoomService — DONE, cần verify routing)
- TASK-032 (U-063 AI rollback — AI settings cần có trước khi hardening)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (ConfigForm save + validation)
- [ ] Sensitive fields KHÔNG xuất hiện trong `drush config:export` plain text
- [ ] "Send test email" functional trong DDEV environment
- [ ] 4 routes đúng C07 sitemap
