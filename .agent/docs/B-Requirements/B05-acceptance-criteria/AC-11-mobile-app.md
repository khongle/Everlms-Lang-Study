# AC-11 — Mobile App

**Feature ID:** F-011
**Epic:** EPIC-A — Content Lifecycle (mobile access)
**Epic Owner:** Student / System
**Feature Doc:** [B03 F-011 Mobile App](../B03-features/11-mobile-app.md)

---

## Acceptance Criteria

### AC-MOB01 — Responsive web trên mobile

**Priority:** Must
**Given** Student truy cập EverLMS trên mobile browser (iOS Safari, Android Chrome)
**When** Student navigate course catalog, lesson content, dashboard
**Then** UI responsive tại breakpoints 375px, 414px, 768px
**And** Không có horizontal scroll; touch targets ≥44px; font legible

### AC-MOB02 — Video playback trên mobile

**Priority:** Must
**Given** Student xem video lesson trên mobile
**When** Student tap play
**Then** Video load và play với native controls
**And** Orientation change (portrait/landscape) không crash player

### AC-MOB03 — Offline fallback

**Priority:** Could
**Given** Student đang học và mất kết nối internet
**When** Network disconnected
**Then** Đối với content đã cached: tiếp tục xem
**And** Mark-complete actions queue và sync khi online lại
**And** Offline indicator hiển thị rõ

### AC-MOB04 — Push notification (nếu có app native)

**Priority:** Could
**Given** Student cài native app và enable push notification
**When** System dispatch notification (quiz result, certificate, forum reply)
**Then** Push notification nhận đúng; tap notification mở đúng context trong app
**And** Notification preference từ web profile sync sang app

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Network switch từ WiFi sang 4G khi xem video | Adaptive bitrate; minimal interruption |
| Low memory device | Giải phóng H5P/SCORM assets khi không dùng |
| Dark mode trên iOS/Android | UI không bị mất contrast; colors adapt |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | First Contentful Paint ≤2s trên 4G mobile |
| UX | Lighthouse mobile score ≥80 |
| Accessibility | Font size minimum 16px; color contrast ≥4.5:1 |
