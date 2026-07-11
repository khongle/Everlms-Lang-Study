# F-014 Acceptance Criteria - Ratings & reviews

## Core criteria
1. **Given** một learner đã enroll, **when** họ submit một rating 1–5 sao (kèm text tùy chọn), **then** một `lms_rating` được lưu lại.
2. **Given** đã có rating, **when** một course được xem, **then** aggregate rating được hiển thị trên card và trang detail.
3. **Given** một user chưa enroll, **when** họ cố rating, **then** rating không được phép (hoặc bị giới hạn theo policy).
4. **Given** một user đã rating rồi, **when** họ rating lại, **then** rating của họ được cập nhật, không bị nhân đôi.

## Evidence expected
- Module: [lms_rating](../../C-Design/C05-modules/lms_rating.md).
- Test: rating-create + aggregate-display test; one-per-user constraint test (E03).
