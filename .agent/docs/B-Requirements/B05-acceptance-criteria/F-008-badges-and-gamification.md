# F-008 Acceptance Criteria - Badges & gamification

> ⚠️ **As-built gap (R-003 / BLK-003):** logic trao badge chưa được implement. Tiêu chí 1–3 mô tả hành vi `target` và hiện đang **FAIL**; tiêu chí 4 (definition/admin) pass.

## Core criteria
1. **(target)** **Given** một award rule (ví dụ course completed), **when** learner đáp ứng rule đó, **then** một `lms_user_badge` được cấp tự động. — *FAILS hiện tại (chưa có trigger).*
2. **(target)** **Given** một badge đã đạt được, **when** learner xem profile của mình, **then** badge được hiển thị.
3. **(target)** **Given** milestone tương tự bị trigger lại, **when** được đánh giá, **then** badge không bị nhân đôi.
4. **Given** một admin, **when** họ định nghĩa một badge (`lms_badge`, `badge_type`), **then** badge được lưu lại và có thể quản lý được. — *pass.*

## Evidence expected
- Module: [lms_badge](../../C-Design/C05-modules/lms_badge.md).
- Test: badge-definition CRUD (pass); award-trigger test (hiện đang red) được track trong [E06](../../E-Quality/E06-implementation-status/_index.md) / [G02 R-003](../../G-Governance/G02-risk-register/_index.md).
