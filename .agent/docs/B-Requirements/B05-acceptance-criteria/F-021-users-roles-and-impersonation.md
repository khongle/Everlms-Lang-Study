# F-021 Acceptance Criteria - Users, roles & impersonation

## Core criteria
1. **Given** 7 role đã cấu hình, **when** một user giữ một role, **then** quyền truy cập route/entity khớp với permission của role đó.
2. **Given** một đăng ký web mới, **when** account được tạo, **then** account nhận role `student` mặc định.
3. **Given** một admin/support user có switch permission, **when** họ impersonate một user khác (`open_switcher`), **then** họ duyệt web như user đó và có thể switch back.
4. **Given** một user không có switch permission, **when** họ cố impersonate, **then** hành động bị từ chối.

## Evidence expected
- Module: [open_switcher](../../C-Design/C05-modules/open_switcher.md); roles trong [C02](../../C-Design/C02-user-roles/_index.md).
- Test: per-role access matrix test; impersonate + restore test; permission-denied test (E03).
