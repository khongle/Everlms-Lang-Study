# F-012 Acceptance Criteria - Memberships & subscriptions

## Core criteria
1. **Given** một admin, **when** họ tạo một plan (`lms_membership_plan`, `field_features`), **then** plan xuất hiện ở trạng thái có thể mua được.
2. **Given** một user subscribe qua một order, **when** payment hoàn tất, **then** một `lms_user_membership` có start/end date được tạo.
3. **Given** một membership đang active, **when** member mở một course được entitle, **then** quyền truy cập được cấp mà không cần mua riêng từng course (F-004).
4. **Given** một membership đã hết hạn, **when** member mở một course từng được entitle, **then** quyền truy cập rộng bị thu hồi.

## Evidence expected
- Module: [lms_membership](../../C-Design/C05-modules/lms_membership.md).
- Test: membership-entitlement test (active vs expired) tích hợp với gating matrix của F-004 (E03).
