# Feature Guide: Admin — User & Role Management

**Feature ID:** FEAT-07 (user-management)
**Available since:** v0.1.0
**User roles:** Administrator only
**Prerequisites:** Role `administrator`.

> Phạm vi: quick reference. Chi tiết operations (bulk, audit) xem [U08 — Admin Guide](../U08-admin-guide.md) §User Management.

## Roles (7)

| Role | Scope | Key Permissions |
|------|-------|-----------------|
| **Administrator** | Toàn hệ thống | All permissions + config |
| **Manager** | Reporting + moderation | View all users, view revenue reports, moderate forum, không edit config |
| **Instructor** | Own content | Create/edit own courses, grade quiz, view own students |
| **Content Editor** | All content | Edit any course/lesson; không access user PII |
| **Student** | Self-service | Enroll, take course, submit quiz, post forum, earn cert |
| **Authenticated** | Logged-in base | Implicit — kế thừa Student defaults |
| **Anonymous** | Public | View public pages (/courses public listing, certificate verify) |

Full matrix trong `C-Design/C02-user-roles.md`.

## How to Use

### Tạo user mới

1. `/admin/people` → **+ Add user**.
2. Form: username, email, password (hoặc let user reset).
3. Assign roles (multi-select).
4. Save → optional email notify.

### Edit user

1. `/admin/people` → filter/search → click user → **Edit**.
2. Tabs: **Account**, **Roles**, **Profile**, **Orders**, **Enrollments**, **Activity Log**.

### Bulk operations

1. `/admin/people` → checkbox users → **Apply action to selected items**.
2. Actions: block / unblock / add role / remove role / cancel account / email.

### Cancel account

Options:
- **Disable the account and keep its content** (soft-block; content untouched).
- **Disable the account and unpublish its content** (hide forum posts).
- **Delete the account and make its content belong to Anonymous** (preserve content, orphan authorship).
- **Delete the account and its content** (hard delete — GDPR).

### Impersonate (debugging)

- Module `devel` / `masquerade` (dev only) — **disabled in production**.

### Activity log

- `/admin/lms/user-log/{uid}`:
  - Login history (IP, user-agent, timestamp).
  - Enrollments.
  - Orders + refunds.
  - Quiz attempts.
  - Forum activity (posts, reports received).
  - Notification preferences changes.

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `user.register` | visitors | Ai tạo account được: visitors / admin_only / visitors_with_approval |
| `user.verify_mail` | true | Email confirm khi register |
| `user.password_strength` | medium | low / medium / high |
| `user.session_timeout_min` | 180 | Inactive timeout |

## Tips & Best Practices

- **Principle of least privilege:** Chỉ cấp role cần thiết. Instructor không nên có Content Editor.
- **Periodic review:** Quarterly audit role list — revoke stale elevated access.
- **Audit log retention:** 12 months minimum (compliance).
- **Bulk role changes** — filter trước khi tick all; double-check preview.

## Known Limitations

- RBAC chưa fully scaffolded — RISK-001 tracked bởi TASK-016 (access-control-hardening).
- Không có fine-grained permission per-course (roadmap v0.3.0).

## Related

- Feature spec: `B-Requirements/B03-features/07-user-management.md`
- Role matrix: `C-Design/C02-user-roles.md`
- Workflow: `B-Requirements/B04-business-workflows/WF-007-user-registration-onboarding.md`
- Risk: G02 RISK-001 (API validation broad scope), NFR-007 (RBAC)
- Full admin guide: `U08-admin-guide.md`
