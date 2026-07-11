# F-013 Acceptance Criteria - Community forum

## Core criteria
1. **Given** một user đã authenticated, **when** họ tạo một topic (`lms_forum_topic`) trong một `forum_category`, **then** topic được lưu lại và được liệt kê.
2. **Given** một topic, **when** user đăng một reply (`lms_forum_reply`), **then** reply xuất hiện dưới topic đó.
3. **Given** một reply mới, **when** được đăng, **then** subscriber/instructor liên quan có thể được thông báo (F-017).
4. **Given** một moderator role, **when** họ edit/delete/lock một topic, **then** hành động được cho phép; user thường thì không.

## Evidence expected
- Module: [lms_forum](../../C-Design/C05-modules/lms_forum.md).
- Test: topic/reply CRUD + permission test theo role (E03).
