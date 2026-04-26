# Task: Session Hardening — Timeout, Cookie Flags, Regen, Single-Session

**ID:** TASK-094
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Hardening session management cho EverLMS: rút ngắn idle timeout (30 phút), enforce absolute session max (12 giờ), set đúng cookie flags (HttpOnly + Secure + SameSite=Strict), regenerate session ID khi privilege change, implement single-session-per-user option, và logout-all-devices functionality.

Các setting này phòng chống session hijacking, session fixation, và CSRF — bổ sung cho CSP headers (TASK-090) và brute-force protection (TASK-092).

## Acceptance Criteria
- [ ] **Timeout:**
  - [ ] Idle timeout: 30 phút inactive → auto-logout, redirect `/user/login?expired=1`
  - [ ] Absolute max: 12 giờ kể từ login → force logout dù đang active
  - [ ] "Remember me" option: extend idle tới 7 ngày (opt-in only, không default)
- [ ] **Cookie flags:**
  - [ ] `HttpOnly` flag: set trên session cookie (không access được từ JavaScript)
  - [ ] `Secure` flag: set trong HTTPS environment (DDEV + production)
  - [ ] `SameSite=Strict`: set để mitigate CSRF
  - [ ] Cookie name: đổi từ default `SESS{hash}` → configurable (tránh fingerprinting)
- [ ] **Session regeneration:**
  - [ ] Session ID regenerated ngay sau login thành công (`session_regenerate_id(true)`)
  - [ ] Session ID regenerated sau privilege escalation (e.g., sudo-style admin action)
  - [ ] Session ID regenerated sau 2FA completion (TASK-093)
- [ ] **Single-session-per-user (optional, admin-configurable):**
  - [ ] Config: `lms_base.settings` → `single_session: true/false`
  - [ ] Khi enable: login mới invalidate tất cả sessions cũ của user đó
  - [ ] User nhận thông báo "Bạn đã đăng nhập từ thiết bị khác"
- [ ] **Logout all devices:**
  - [ ] `/user/{uid}/sessions` page liệt kê active sessions (IP, user agent, last active)
  - [ ] "Đăng xuất tất cả thiết bị" button invalidate tất cả sessions trừ current
- [ ] PHPUnit kernel test: verify idle timeout + cookie flags + session regen

## Technical Notes
- PHP session config: `web/sites/default/settings.php`
  ```php
  ini_set('session.cookie_httponly', 1);
  ini_set('session.cookie_secure', 1);
  ini_set('session.cookie_samesite', 'Strict');
  ini_set('session.gc_maxlifetime', 1800); // 30 min idle
  ```
- Drupal session management: extend `Drupal\Core\Session\SessionManager` hoặc EventSubscriber
- Idle timeout: track `$_SESSION['last_activity']` timestamp, check on each request
- Session storage: Drupal dùng `session` table (MariaDB) hoặc Redis — `lms_base.services.yml`
- Single-session: store `active_session_id` trong user data, compare trên login
- Active sessions list: query `sessions` table filtered by `uid`
- Absolute max: `$_SESSION['login_time']` + 12h check

## Dependencies
- TASK-090 (CSP headers — session cookie flags complement)
- TASK-093 (2FA — session regen after 2FA verify)

## Definition of Done
- [ ] Code complete — settings.php + SessionHardeningSubscriber + logout-all UI
- [ ] G1-G4 gates passed
- [ ] Cookie flags verified via browser DevTools + curl -I
- [ ] Idle timeout test: session expire after 30min simulated
- [ ] Logout all devices functional
