# Task: Screen Reader Compatibility Pass (NVDA + VoiceOver)

**ID:** TASK-108
**Unit:** — (cross-cutting: a11y)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thực hiện screen reader pass đầy đủ trên golden user flows của EverLMS, dùng NVDA (Windows + Chrome) và VoiceOver (macOS + Safari). Verify ARIA labels, heading hierarchy, form labels, skip links, và landmark roles. Mục tiêu: người dùng mù hoặc low vision có thể hoàn thành golden flows không cần thao tác chuột.

Golden flows: đăng ký tài khoản → đăng nhập → xem course catalog → enroll → hoàn thành lesson → làm quiz → xem certificate.

## Acceptance Criteria
- [ ] NVDA test trên Windows (Chrome browser) — tất cả golden flow steps navigable bằng bàn phím
- [ ] VoiceOver test trên macOS (Safari browser) — spot-check 3 flows quan trọng nhất
- [ ] Skip link: "Skip to main content" có mặt và hoạt động trên mọi page (Tab từ URL bar → skip link nổi lên)
- [ ] Heading hierarchy: không skip levels (không có h1 → h3 bỏ qua h2) trên mọi page
- [ ] Form labels: tất cả `<input>`, `<select>`, `<textarea>` có `<label>` hoặc `aria-label` hoặc `aria-labelledby`
- [ ] ARIA landmarks: `<header>`, `<main>`, `<nav>`, `<footer>` đúng roles — screen reader có thể jump bằng landmark navigation
- [ ] Images: tất cả `<img>` có `alt` text (decorative images có `alt=""`)
- [ ] Video player (lesson): keyboard controls hoạt động; play/pause/seek accessible qua keyboard
- [ ] Quiz: radio/checkbox options announced correctly với screen reader (group label + option label)
- [ ] Modal dialogs: focus trapped trong modal khi mở; Esc đóng modal; focus return về trigger khi đóng
- [ ] Live regions: enrollment success / payment confirmation announced qua `aria-live="polite"`
- [ ] Report: issues documented trong `.agent/docs/K-QA/K04-a11y-audit/SR-PASS-001.md`
- [ ] P0/P1 issues fixed; P2/P3 ticketed

## Technical Notes
- NVDA download: nvaccess.org (free) — test trên Windows VM nếu dev dùng Linux/Mac
- VoiceOver: built-in macOS — Cmd+F5 để toggle
- NVDA shortcuts: Insert+F7 (elements list), H (jump heading), F (form field), B (button), D (landmark)
- VoiceOver shortcuts: VO+U (rotor), VO+Right (next item), VO+Space (activate)
- Custom video player (nếu dùng): phải có `aria-label` cho controls; `<video>` element có `role="application"`
- Drupal theme: `lms_theme` — check `templates/` cho ARIA attributes
- Tham chiếu: TASK-107 (WCAG audit — issues từ đây có thể overlap)

## Dependencies
- TASK-107 (WCAG audit — fix P0/P1 trước để screen reader pass không bị block bởi known issues)

## Definition of Done
- [ ] Code complete (SR fixes committed)
- [ ] G1-G4 gates passed
- [ ] SR-PASS-001 report committed
- [ ] Golden flows navigable end-to-end bằng keyboard + NVDA verified bởi tester
- [ ] No new debt introduced
