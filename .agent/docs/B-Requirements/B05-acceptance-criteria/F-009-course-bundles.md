# F-009 Acceptance Criteria - Course bundles

## Core criteria
1. **Given** một editor, **when** họ tạo một bundle và thêm course (`field_courses`), **then** bundle và price của nó được lưu lại.
2. **Given** một bundle, **when** buyer mua nó (WF-004), **then** buyer được enroll vào **tất cả** course có trong bundle.
3. **Given** trang detail của bundle, **when** được xem, **then** trang liệt kê các course có trong bundle và bundle price.

## Evidence expected
- Module: [lms_bundle](../../C-Design/C05-modules/lms_bundle.md).
- Test: bundle-purchase fan-out test (enrollment được tạo cho mỗi course có trong bundle) (E03).
