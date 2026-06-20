# lms_membership — Membership Plans & Subscriptions Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_membership` |
| Name | LMS Membership |
| Layer | **Commerce** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_payment` |

## Mô tả

Module quản lý gói membership và subscription của user. Cung cấp entity MembershipPlan (template gói) và UserMembership (instance). `MembershipManager` là service chính để kiểm tra active membership. `MembershipAccessSubscriber` bắt event để enforce access.

---

## Provided Entities

### `lms_membership_plan` (Content Entity)
| Fields | Description |
|--------|-------------|
| name | Tên gói |
| price | Giá |
| duration_days | Thời hạn (ngày) |
| features | Danh sách tính năng (text list) |
| is_active | Published status |

### `lms_user_membership` (Content Entity)
| Fields | Description |
|--------|-------------|
| uid | User |
| plan ref | → lms_membership_plan |
| start_date | Ngày bắt đầu |
| end_date | Ngày hết hạn |
| status | active/expired/cancelled |
| order ref | → lms_order |

---

## Provided Routes

| Route ID | Path | Controller | Access |
|----------|------|-----------|--------|
| `lms_membership.my_membership` | `/my-membership` | `MembershipController::myMembership` | logged in |
| `lms_membership.cancel_membership` | `/my-membership/{lms_user_membership}/cancel` | `MembershipController::cancelMembership` | logged in |
| `lms_membership.admin_pricing` | `/workspace/pricing` | `MembershipAdminController::pricingList` | `administer lms_course` |
| `lms_membership.admin_pricing.add` | `/workspace/pricing/add` | `MembershipAdminController::pricingAddForm` | `administer lms_course` |
| `lms_membership.admin_pricing.edit` | `/workspace/pricing/{lms_membership_plan}/edit` | `MembershipAdminController::pricingEditForm` | `administer lms_course` |
| `lms_membership.admin_pricing.toggle` | `/workspace/pricing/{lms_membership_plan}/toggle` | `MembershipAdminController::toggleStatus` | `administer lms_course` |
| `lms_membership.admin_user_memberships` | `/workspace/user-memberships` | `MembershipAdminController::userMembershipsList` | `administer lms_course` |
| `lms_membership.admin_user_memberships.add` | `/workspace/user-memberships/add` | `MembershipAdminController::userMembershipAddForm` | `administer lms_course` |
| `lms_membership.admin_user_memberships.edit` | `/workspace/user-memberships/{lms_user_membership}/edit` | `MembershipAdminController::userMembershipEditForm` | `administer lms_course` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_membership.manager` | `MembershipManager` | `hasActiveMembership(account)` — check user có active membership không |
| `lms_membership.access_subscriber` | `MembershipAccessSubscriber` | EventSubscriber enforce access, inject messenger |

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Templates được render qua controllers. Cache per-user vì membership status là user-specific.

`lms_course.module` → `hook_entity_access` gọi `lms_membership.manager::hasActiveMembership()` để kiểm tra quyền xem course/lesson.

---

## Related Units

- **C03** § Commerce Domain — entities MembershipPlan, UserMembership
- **C05** `03-progress-commerce.md` — layer overview
- `lms_course.md` — consumer của `hasActiveMembership`
- `lms_order.md` — membership checkout
- `lms_payment.md` — payment processing

---

## Known Issues / Debt

- `cancel_membership` route không verify ownership — user có thể cancel membership của người khác nếu biết ID.
- Không có auto-expiry job — expired membership chỉ được phát hiện khi `hasActiveMembership()` được gọi (lazy check), không có cron để set status = expired.
- MembershipManager chỉ có một method `hasActiveMembership` — cần thêm `getActiveMembership()`, `isExpiringSoon()` v.v.
