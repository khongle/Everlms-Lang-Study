# F-020 Acceptance Criteria - Analytics & reporting

> ⚠️ As-built coverage chỉ partial; độ sâu được confirm trong E06.

## Core criteria
1. **Given** một manager role, **when** họ mở analytics dashboard, **then** summary enrollment/completion/revenue được render từ live data.
2. **Given** một instructor role, **when** họ mở analytics, **then** họ thấy data chỉ giới hạn trong course của riêng mình.
3. **Given** đã có quiz result, **when** pass-rate report chạy, **then** report phản ánh data của `lms_quiz_result`.
4. **Given** một user không có quyền, **when** họ truy cập một analytics route, **then** quyền truy cập bị từ chối.

## Evidence expected
- Module: [lms_analytics](../../C-Design/C05-modules/lms_analytics.md).
- Test: role-scoped access test; spot check độ chính xác report so với DB; gap được ghi nhận trong [E06](../../E-Quality/E06-implementation-status/_index.md).
