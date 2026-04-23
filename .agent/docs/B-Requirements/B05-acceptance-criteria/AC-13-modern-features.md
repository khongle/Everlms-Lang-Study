# AC-13 — Suggested Modern Features

**Feature ID:** F-013
**Epic:** EPIC-H — AI & Modern Enhancements
**Epic Owner:** Product / Admin
**Feature Doc:** [B03 F-013 Suggested Modern Features](../B03-features/13-suggested-modern-features.md)

---

## Acceptance Criteria

### AC-MOD01 — Dark mode support

**Priority:** Could
**Given** User có OS-level dark mode enabled hoặc click dark mode toggle
**When** Page loads hoặc user toggle
**Then** UI chuyển sang dark color scheme; không có flicker (FOUC)
**And** Preference stored; persist across sessions

### AC-MOD02 — Learning streak / gamification

**Priority:** Could
**Given** Student học ít nhất 1 lesson mỗi ngày trong N ngày liên tiếp
**When** Student view dashboard
**Then** Streak counter hiển thị đúng số ngày
**And** Streak broken khi miss 1 ngày; notification 23h trước khi break

### AC-MOD03 — Wishlist / bookmark

**Priority:** Could
**Given** Logged-in student xem course họ chưa mua
**When** Click "Add to Wishlist"
**Then** Course thêm vào wishlist; count update
**And** Wishlist accessible từ profile; "Remove" hoạt động

### AC-MOD04 — Course bundle

**Priority:** Should
**Given** Admin tạo bundle gồm 3 courses với discounted bundle price
**When** Student mua bundle
**Then** Order tạo với bundle item; access granted cho cả 3 courses
**And** Bundle discount áp dụng đúng; individual course prices hiển thị crossed-out

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Streak timezone edge case (midnight) | Server timezone consistent; không penalize timezone difference |
| Bundle có course bị unpublish | Bundle vẫn valid; unpublished course accessible với direct URL |
| Wishlist course được mua | Course vẫn trong wishlist nhưng hiển thị "Enrolled" |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| UX | Modern features không impact core learning performance |
| Graceful degradation | Tắt modern features không break core flows |
