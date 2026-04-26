# Task: Login Brute-Force Protection — Progressive Delays + Lockout + CAPTCHA

**ID:** TASK-092
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement comprehensive login brute-force protection cho EverLMS auth layer: progressive delay sau N failed attempts, temporary account lockout, IP-based throttling, CAPTCHA trigger sau 5 failures, và email alert khi detect suspicious login pattern. Cover cả web form login và `lms_mobile_api` auth endpoint.

Drupal core đã có cơ bản flood protection — task này extend thêm: progressive delay, CAPTCHA integration, và email alert mechanism.

## Acceptance Criteria
- [ ] **Progressive delay:**
  - [ ] 1–3 failed attempts: no delay (normal flood)
  - [ ] 4–5 attempts: 5 second server-side delay trước khi trả response
  - [ ] 6–10 attempts: 30 second delay
  - [ ] >10 attempts trong 1h: account temp-locked (15 phút)
- [ ] **CAPTCHA trigger:**
  - [ ] Sau 5 failed attempts từ cùng IP: login form hiển thị CAPTCHA (hCaptcha hoặc reCAPTCHA v3)
  - [ ] `lms_mobile_api` auth endpoint: CAPTCHA challenge response required sau 5 failures
- [ ] **Account temp-lockout:**
  - [ ] Account locked 15 phút sau 10 failures
  - [ ] Lock message: "Tài khoản tạm khóa. Thử lại sau X phút."
  - [ ] Admin có thể unlock ngay từ `/admin/lms/users/{uid}`
- [ ] **IP throttling:**
  - [ ] IP bị block 1h sau 20 failed attempts từ nhiều accounts khác nhau (credential stuffing detection)
  - [ ] IP whitelist support (`$settings['login_ip_whitelist']`)
- [ ] **Email alert:**
  - [ ] Khi account bị lock: email cảnh báo tới account owner
  - [ ] Khi IP bị block: email tới admin (`hainn.we@gmail.com` placeholder)
- [ ] PHPUnit kernel test: verify lockout sau 10 attempts, verify delay applied

## Technical Notes
- Extend Drupal flood: `web/modules/custom/lms_base/src/EventSubscriber/LoginBruteForceSubscriber.php`
- Hook: `hook_user_login_failed()` hoặc `KernelEvents::REQUEST` intercept trên `/user/login`
- CAPTCHA: `drupal/captcha` + `drupal/hcaptcha` contrib (hoặc custom `lms_captcha` nếu cần branding)
- Progressive delay: `sleep()` trong subscriber — chú ý PHP timeout settings
- Mobile API: `lms_mobile_api/src/Controller/AuthController.php` — inject flood service
- Email alert: dùng `\Drupal::service('plugin.manager.mail')` với custom mail plugin
- Lock storage: Drupal flood table hoặc Redis key `login_lock:{uid}` với TTL 15min

## Dependencies
- TASK-091 (rate limit — shared flood infrastructure)
- TASK-074 (lms_mobile_api — auth endpoint integration)

## Definition of Done
- [ ] Code complete — progressive delay + lockout + CAPTCHA + email alert
- [ ] G1-G4 gates passed
- [ ] Kernel test: 10 attempts → lockout verified
- [ ] CAPTCHA visible sau 5 failures on web form
- [ ] Email alert received in test run
