# Task: 2FA Opt-In — TOTP for Staff Roles (Admin/Manager/Instructor)

**ID:** TASK-093
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement 2FA opt-in dùng TOTP (Time-based One-Time Password) cho các staff roles trong EverLMS: Admin, Manager, Instructor. User tự setup qua authenticator app (Google Authenticator, Authy, 1Password). Recovery codes được generate khi setup. Sau v1.0 release, 2FA sẽ được force-enable cho Admin role.

Implementation dùng `drupal/tfa` contrib module (nếu compatible với Drupal 11) hoặc custom `lms_2fa` module với `OTPHP` PHP library.

## Acceptance Criteria
- [ ] **Setup flow:**
  - [ ] Profile page `/user/{uid}/security` có section "Two-Factor Authentication"
  - [ ] QR code display (base32 secret) để scan bằng authenticator app
  - [ ] Verify code nhập lần đầu để confirm setup thành công
  - [ ] 10 single-use recovery codes được generate + display (download option)
  - [ ] Recovery codes hashed (bcrypt) trước khi lưu DB
- [ ] **Login flow (khi 2FA enabled):**
  - [ ] Sau username/password → redirect tới `/user/login/tfa` page
  - [ ] 6-digit TOTP code input với 30-second window + 1 clock-drift tolerance
  - [ ] "Use recovery code" option: dùng recovery code thay TOTP → invalidate code sau use
  - [ ] 5 failed TOTP attempts → temp lock 15 phút (chống TOTP brute-force)
- [ ] **Admin enforcement:**
  - [ ] Config: `lms_2fa.settings` — `force_roles[]`: Admin (post-v1.0 flag)
  - [ ] Forced users không thể access site cho đến khi setup 2FA
- [ ] **Recovery:**
  - [ ] Admin có thể reset 2FA cho user từ `/admin/lms/users/{uid}`
  - [ ] Self-service: nếu lost device + recovery codes → support email flow
- [ ] PHPUnit kernel test: TOTP verify correct/incorrect code; recovery code single-use

## Technical Notes
- Option A: `drupal/tfa` contrib — check Drupal 11 compatibility trước
- Option B: custom `web/modules/custom/lms_2fa/` với:
  - `spomky-labs/otphp` PHP library (TOTP generation/verify)
  - `endroid/qr-code` cho QR code generation
- TOTP secret: stored encrypted trong user field `field_2fa_secret` (lms_base user bundle)
- Recovery codes: custom entity `lms_recovery_code` hoặc user field serialized
- Session: sau TOTP verify → set `$_SESSION['tfa_authenticated'] = TRUE`
- Route protection: EventSubscriber check `tfa_authenticated` session flag
- Test: `OTPHP\TOTP::create($secret)->at(time())` để generate valid code trong test

## Dependencies
- TASK-092 (brute force — TOTP lockout shares infrastructure)
- TASK-094 (session hardening — session management after 2FA auth)

## Definition of Done
- [ ] Code complete — setup flow + login flow + admin reset
- [ ] G1-G4 gates passed
- [ ] Recovery codes: single-use + hashed confirmed
- [ ] Admin force-enable flag implemented
- [ ] Kernel test: correct/incorrect TOTP + recovery code verify
