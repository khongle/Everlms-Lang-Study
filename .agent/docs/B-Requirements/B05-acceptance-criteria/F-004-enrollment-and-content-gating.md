# F-004 Acceptance Criteria - Enrollment & content gating

## Core criteria
1. **Given** một course miễn phí, **when** user enroll, **then** một bản ghi `lms_user_course` được tạo và nội dung lesson được mở khóa.
2. **Given** một course trả phí, **when** user không có order/membership mở course, **then** nội dung bị gate và checkout được đề xuất.
3. **Given** một paid order đã hoàn tất (WF-004), **when** order được finalise, **then** buyer được enroll và nội dung được mở khóa.
4. **Given** một membership đang active cho phép truy cập course, **when** member mở course, **then** nội dung được mở khóa mà không cần mua riêng từng course.

## Evidence expected
- Modules: [lms_progress](../../C-Design/C05-modules/lms_progress.md), [lms_course](../../C-Design/C05-modules/lms_course.md).
- Test: gating matrix test (free / paid-unpaid / paid-paid / membership) trên web và `/api/v1` (F-019).
