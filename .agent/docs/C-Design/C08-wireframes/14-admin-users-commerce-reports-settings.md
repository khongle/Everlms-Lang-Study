# Wireframe: Admin — Users + Commerce + Certificates + Reports + Settings

## User Management (`/manage/users`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR                                                    │
├─────────────────────────────────────────────────────────────────────┤
│ "User Management"                               [+ Create User]     │
│ [All Users] [Enrollments]                                            │
│ Filters: [Role ▼] [Status ▼]   Search: [_________ 🔍]              │
├─────────────────────────────────────────────────────────────────────┤
│ # │ Name           │Email              │Role      │Courses│Status   │
│ 1 │ 👤 John Doe    │john@email.com     │Student   │8      │✅ Active│
│ 2 │ 👤 Sarah Lee   │sarah@email.com    │Instructor│15     │✅ Active│
│ 3 │ 👤 Mike Chen   │mike@email.com     │Student   │3      │❌ Block │
│ Bulk: [Activate] [Block] [Assign Role] [Export]                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Order Management (`/manage/orders`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Order Management"                                                   │
│ KPIs: [💰 $12K Today] [📦 45 Orders] [↩️ 2 Refunds] [⏳ 5 Pending]│
│ Filters: [Status ▼] [Date ▼] [Method ▼]                            │
├─────────────────────────────────────────────────────────────────────┤
│ # │ Order ID │Customer    │Amount │Method │Status    │Date      │Act│
│ 1 │ #12345   │John Doe    │$49.99 │Stripe │✅ Paid   │Mar 8     │[👁]│
│ 2 │ #12344   │Sarah Lee   │$299   │PayPal │✅ Complet│Mar 7     │[👁]│
│ 3 │ #12343   │Mike Chen   │$29.99 │Stripe │⏳ Pending│Mar 7     │[👁]│
│ 4 │ #12342   │Anna Wu     │$49.99 │Stripe │↩️ Refund │Mar 6     │[👁]│
└─────────────────────────────────────────────────────────────────────┘
```

---

## Certificate Templates (`/manage/certificate-templates`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Certificate Templates"                    [+ Create Template]       │
├─────────────────────────────────────────────────────────────────────┤
│ ┌────────────────┐  ┌────────────────┐  ┌────────────────┐         │
│ │ [Preview]      │  │ [Preview]      │  │ [Preview]      │         │
│ │ Classic Gold   │  │ Modern Blue    │  │ Tech Green     │         │
│ │ Used by: 5     │  │ Used by: 8     │  │ Used by: 3     │         │
│ │ ✅ Active      │  │ ✅ Active      │  │ ❌ Inactive    │         │
│ │ [Edit] [Clone] │  │ [Edit] [Clone] │  │ [Edit] [Clone] │         │
│ └────────────────┘  └────────────────┘  └────────────────┘         │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Reports Dashboard (`/manage/reports`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Reports & Analytics"                        Date: [Mar 1-8 ▼]      │
│ [Overview] [Courses] [Users] [Revenue] [Quizzes]                    │
├──────────────────────────────────────────┬──────────────────────────┤
│ REVENUE CHART (col-8)                    │ TOP COURSES (col-4)      │
│ [Line/Bar toggle]                        │ 1. Web Dev — $12K        │
│ ┌────────────────────────────────────┐  │ 2. React — $8K           │
│ │  Monthly Revenue Trend             │  │ 3. CSS — $5K             │
│ └────────────────────────────────────┘  │ [View All →]             │
├──────────────────────────┬───────────────┴──────────────────────────┤
│ ENROLLMENT TREND (col-6) │ COMPLETION PIE (col-6)                   │
│ [Area chart]             │ [Doughnut: Completed 65%, In Progress    │
│                          │  25%, Not Started 10%]                   │
├──────────────────────────┴─────────────────────────────────────────┤
│ RECENT ACTIVITY TABLE                                                │
│ # │ Action          │User        │Detail         │Time              │
│ 1 │ Enrollment      │John Doe    │Web Dev Boot.. │2h ago            │
│ 2 │ Quiz Passed     │Sarah Lee   │HTML Quiz (85%)│5h ago            │
│ 3 │ Certificate     │Mike Chen   │CSS Mastery    │1d ago            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Settings (`/manage/settings`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Settings"                                                           │
│ [General] [Payment] [Email] [Notifications] [Integrations]          │
├─────────────────────────────────────────────────────────────────────┤
│ ── GENERAL TAB ──                                                    │
│                                                                      │
│ Site Name:        [EverLMS________________]                         │
│ Site Logo:        [📤 Upload]  Current: [logo.png]                  │
│ Default Currency: [USD ▼]                                           │
│ Timezone:         [UTC+7 Ho Chi Minh ▼]                             │
│ Language:         [English ▼]                                       │
│ Registration:     ☑ Allow self-registration                         │
│ Email Verify:     ☑ Require email verification                      │
│                                                                      │
│ [Save Changes]                                                       │
│                                                                      │
│ ── PAYMENT TAB ──                                                    │
│ Stripe API Key:     [sk_live_**********]                            │
│ Stripe Secret:      [sk_secret_*******]                             │
│ PayPal Client ID:   [**********]                                    │
│ PayPal Secret:      [**********]                                    │
│ Test Mode:          ☐ Enable sandbox mode                           │
│ [Save Changes]                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built (all core sections); badges and some settings tabs are partial/stub.**

**Users:**
- `/workspace/users` → `AdminController::userList` → `lms-admin-users.html.twig`. Bulk actions via `/workspace/users/bulk`. User create/edit: `/workspace/users/create`, `/workspace/users/{user}/admin-edit`.
- Impersonation: `/admin/switch-user` + `/app/switch-user` (`open_switcher` module).

**Orders / Commerce:**
- Order list: `/workspace/orders` → `AdminController::orderList` → `lms-admin-orders.html.twig`.
- Membership plans: `/workspace/pricing` → `MembershipAdminController`. User memberships: `/workspace/user-memberships`.
- Certificate management: `/workspace/certificates` → `CertificateAdminController`. Certificate template CRUD: `/workspace/certificate-templates`.
- **Badges:** `lms_badge` module has entity definitions only — no award logic, no admin UI beyond auto-generated routes at `/manage/badges`. Effectively a near-stub.

**Reports:**
- Analytics: `/admin/lms/reports/courses|users|quizzes` — fully implemented. Note: `lms_analytics` module description mentions "GA4" but no GA4 code exists.

**Settings:**
- Payment settings: `/admin/config/everlms/payment` — real form, saves to `lms_payment.settings`. SECURITY NOTE: third-party keys (Stripe, PayPal, Gemini, OpenAI, Zoom) are stored in plaintext committed YAML config — no Key module («secret in config — see security risk»).
- Zoom settings: `/admin/config/lms/zoom`.
- AI settings: `/admin/config/lms/ai`.

Related: [../C04-entities/04-commerce-entities.md](../C04-entities/04-commerce-entities.md) · [../C05-modules/lms_payment.md](../C05-modules/lms_payment.md) · [../C07-sitemap.md](../C07-sitemap.md)
