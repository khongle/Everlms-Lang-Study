# WF-008 — Forum & Discussion Flow

**ID:** WF-008
**Trigger:** User truy cập forum, tạo topic, hoặc reply vào topic
**Actors:** Student, Instructor, Moderator, System
**Preconditions:** User đã đăng nhập. Nếu là course-scoped forum: user đã enrolled.
**Related Features:** F-008 (Communication & Forum)
**Related Entities:** ForumTopic, ForumReply, Notification, User, Course

---

## Happy Path — Create Topic & Reply

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | User | Browse forum (global hoặc course-scoped) | Permission check: enrolled? logged in? |
| 2 | User | Click "New Topic" | Hiển thị form: title + body |
| 3 | User | Submit topic | Save, publish, dispatch notification |
| 4 | System | Notify course instructor | Email + in-app notification |
| 5 | User | Xem topic, viết reply | Threaded display |
| 6 | User | Submit reply | Save, notify topic author |
| 7 | Instructor/Topic Author | Mark reply as "Best Answer" | Topic status = Resolved |

## Moderation Flow

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | User | Flag/report post | Gửi đến moderator queue |
| 2 | Moderator | Review reported content | Options: Approve / Hide / Delete |
| 3 | Moderator | Take action | Content state updated, reporter notified |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 1a | Not logged in | Redirect đến login | After login, return to forum |
| 1b | Course-scoped forum, not enrolled | Redirect với message "Enroll first" | Student enrolls |
| 3a | Topic locked | Disable reply button | User contact moderator |
| 3b | Spam/bot detection | Block submission | CAPTCHA challenge |

## Forum Access Rules

| Forum Type | Who Can Access |
|-----------|---------------|
| Global forum | All authenticated users |
| Course-scoped forum | Enrolled students + course instructor |
| Pinned topics | Display first; only Admin/Manager can pin |
| Locked topics | No new replies; content visible |

## Business Rules

- **Best Answer**: chỉ topic author hoặc instructor có thể mark.
- **Pinned topics**: luôn display đầu danh sách; chỉ admin/manager pin.
- **Moderation**: Flag → moderator queue → approve/hide/delete.
- **Notifications**: dispatch theo user preferences (email + in-app).
- **Rich text**: body supports markdown/HTML (sanitized).

## Notification Triggers

| Event | Recipients |
|-------|-----------|
| New topic in course forum | Course instructor |
| New reply to topic | Topic author |
| Best answer marked | Reply author |
| Post hidden/deleted by moderator | Post author |

## Postconditions

- Topic/Reply persisted với correct author + timestamps.
- Notifications dispatched theo preference.
- Moderator actions logged.

## Related Docs

- B03 Feature: [F-008 Communication](../B03-features/08-communication.md)
- [WF-001 — Enrollment & Learning](./WF-001-enrollment-learning.md) (enrollment prerequisite)
