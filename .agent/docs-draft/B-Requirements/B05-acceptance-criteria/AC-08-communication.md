# AC-08 — Communication & Forum

**Feature ID:** F-008
**Epic:** EPIC-F — Community & Notification
**Epic Owner:** Student / Instructor / Moderator
**Related Workflow:** [WF-008 — Forum Flow](../B04-business-workflows/WF-008-forum-flow.md)
**Feature Doc:** [B03 F-008 Communication](../B03-features/08-communication.md)

---

## Acceptance Criteria

### AC-F02 — Notification preference respected

**Priority:** Must
**Given** User đã set notification preference: email=off, in-app=on
**When** System dispatch notification event
**Then** In-app notification tạo; không gửi email
**And** User với email=on và in-app=on nhận cả hai kênh

### AC-F03 — Forum thread creation & display

**Priority:** Must
**Given** Enrolled student ở course forum
**When** Student tạo topic với title + body và submit
**Then** Topic published, hiển thị trong forum list
**And** Instructor nhận notification (in-app + email theo preference)
**And** Student có thể reply → threaded display đúng (nested replies hiển thị indented)

### AC-F04 — Rating & review

**Priority:** Must
**Given** Student đã enrolled và có progress ≥1 lesson
**When** Student submit rating 1-5 sao + review text
**Then** Rating saved; course average rating cập nhật ngay
**And** Review hiển thị với student name + date; owner không thể rate own course

### AC-COM01 — Forum moderation

**Priority:** Must
**Given** User flag một reply là spam
**When** Flag submitted
**Then** Reply vào moderator queue; không bị xóa tự động
**And** Moderator thấy flagged content với action options: Approve / Hide / Delete

### AC-COM02 — Best answer marking

**Priority:** Should
**Given** Topic author hoặc instructor xem replies
**When** Click "Mark as Best Answer" trên một reply
**Then** Reply được đánh dấu Best Answer với visual indicator
**And** Topic status = Resolved; reply author nhận notification

### AC-COM03 — Course-scoped forum access control

**Priority:** Must
**Given** Student A enrolled trong Course X; Student B chưa enrolled
**When** Cả hai cố truy cập Course X forum
**Then** Student A xem được; Student B redirect với "Enroll first" message
**And** Instructor của Course X luôn có access dù không enrolled

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Notification gửi fail (SMTP error) | Retry 3 lần; in-app vẫn tạo; log lỗi |
| Forum topic với XSS content | HTML sanitized; script tags stripped |
| Rating update (student muốn đổi rating) | Cho phép update; recalculate average |
| Locked topic có reply attempt | Reply button disabled + tooltip "Topic locked" |
| Moderator là instructor của course | Có full mod access cho course forum |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Notification dispatch async (queue-based) |
| Security | HTML sanitization prevent XSS trong forum posts |
| Reliability | In-app notification không mất dù email fail |
