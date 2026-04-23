# AC-07 — User Management

**Feature ID:** F-007
**Epic:** EPIC-F — Community & Access Control
**Epic Owner:** Admin / System
**Related Workflow:** [WF-007 — User Registration](../B04-business-workflows/WF-007-user-registration.md)
**Feature Doc:** [B03 F-007 User Management](../B03-features/07-user-management.md)

---

## Acceptance Criteria

### AC-F01 — Role matrix enforced

**Priority:** Must
**Given** Role matrix định nghĩa: Student chỉ read content; Instructor CRUD own courses; Admin full access
**When** User thực hiện create/update/delete action
**Then** Permission check enforce theo role; unauthorized action → 403 với message
**And** No privilege escalation via direct URL/API manipulation

### AC-UM01 — Email registration validation

**Priority:** Must
**Given** Visitor submit registration form
**When** Form có email đã tồn tại hoặc password yếu
**Then** Inline validation error hiển thị ngay field (không submit đến server)
**And** Server-side validation lại ngay cả khi client bypass

### AC-UM02 — Email verification bắt buộc

**Priority:** Must
**Given** User đăng ký bằng email/password
**When** User cố đăng nhập trước khi verify email
**Then** Login block với message "Please verify your email"
**And** "Resend verification email" option available

### AC-UM03 — Social login link account

**Priority:** Should
**Given** User đã có account email "foo@example.com"
**When** User login với Google account cùng email
**Then** Social account link với existing account; không tạo duplicate
**And** User có thể dùng cả email/password và Google login

### AC-UM04 — Admin user management

**Priority:** Must
**Given** Admin ở trang Users management
**When** Admin search/filter users và thực hiện bulk actions
**Then** Filter by role, status, join date hoạt động; results paginated
**And** Admin có thể activate/deactivate, change role, view activity

### AC-UM05 — Profile update

**Priority:** Must
**Given** Logged-in user ở profile page
**When** User update: avatar, display name, bio, notification preferences
**Then** Changes persist; avatar resize đúng; notification preferences honored
**And** Email change require re-verification

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Concurrent registration với cùng email | Race condition: unique constraint catch; second request gets "email taken" |
| User deactivated khi đang logged in | Session invalidated trong ≤60s |
| Password reset với expired link | "Link expired" message với option to request new |
| Admin deletes own account | Block với error: "Cannot delete your own account" |
| User với nhiều roles | Permissions union; highest-privilege role apply |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Security | Password hashed bcrypt ≥10 rounds |
| Security | Brute-force protection: lockout sau 5 failed attempts |
| Privacy | GDPR: user có thể request data export và account deletion |
