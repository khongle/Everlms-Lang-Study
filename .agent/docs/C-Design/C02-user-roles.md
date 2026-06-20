# C02 — User Roles

> Actors, roles, permission matrix, and access-enforcement model for EverLMS. Documentation mode: **as-built** (reflects actual config and code as of 2026-06-20).

---

## Role Registry

| Role ID | Machine Name | Drupal ID | Description |
|---------|-------------|-----------|-------------|
| ROLE-01 | `administrator` | `administrator` | Full-access Drupal administrator; bypasses all permission checks (Drupal convention). No explicit permissions listed in config — bypass is automatic. |
| ROLE-02 | `manager` | `manager` | LMS content + user manager. Has `administer lms_course` and `switch users`; manages workspace UI. |
| ROLE-03 | `content_editor` | `content_editor` | CMS content manager (node:article, node:page, taxonomy). Does NOT have LMS-specific permissions. |
| ROLE-04 | `instructor` | `instructor` | Course instructor. Has `administer comments` only as LMS-specific config permission — actual LMS access is via module code / entity access. |
| ROLE-05 | `student` | `student` | Registered learner. Auto-assigned to new users via `lms_base` `hook_user_insert`. Minimal config permissions; LMS learning access is via membership check and module code. |
| ROLE-06 | `authenticated` | `authenticated` | Base role inherited by all logged-in users. |
| ROLE-07 | `anonymous` | `anonymous` | Unauthenticated visitor. |

> **Important (as-built):** `student` and `instructor` have minimal config-defined permissions. The LMS enforces most learner/instructor access through:
> 1. Custom module code and entity access handlers (e.g., `OrderAccessControlHandler`, `lms_membership` checks).
> 2. `MembershipAccessSubscriber` — redirects to pricing page on `AccessDeniedException` for `/learn/*` and `/course/*` paths.
> 3. Route-level `_user_is_logged_in: TRUE` or `_permission: 'administer lms_course'` requirements.

---

## Role Hierarchy

```
administrator          ← bypasses all permission checks
  └── manager          ← administer lms_course + switch users
        └── content_editor    ← CMS node/taxonomy editing
              └── instructor   ← thin LMS config permissions
                    └── student ← auto-assigned on register
                          └── authenticated  ← base logged-in role
                                └── anonymous  ← unauthenticated visitors
```

> **Note:** This hierarchy is conceptual. Drupal roles are not explicitly inherited — each role's permissions are cumulative only insofar as a user can hold multiple roles simultaneously. The hierarchy above reflects intended capability levels.

---

## Route Group Access Matrix

| Role | Public `/*` (19 routes) | Learn `/dashboard /my-* /learn /quiz` (21 routes) | Workspace `/workspace /manage` (47 routes) | Admin `/admin` (17 routes) | API `/api/v1/*` (22 routes) | API internal `/api/lms/*` (9 routes) |
|------|------------------------|---------------------------------------------------|-------------------------------------------|----------------------------|-----------------------------|--------------------------------------|
| anonymous | ✅ (`access content` or `TRUE`) | ❌ | ❌ | ❌ | ✅ (auth-only endpoints enforced in controller) | 🔒 (unread-count only) |
| authenticated | ✅ | ✅ (logged_in) | ❌ (unless also manager) | ❌ | ✅ (controller-enforced) | ✅ |
| student | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| instructor | ✅ | ✅ | 🔒 (needs `administer lms_course` — NOT assigned in config) | ❌ | ✅ | ✅ |
| manager | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| content_editor | ✅ | ✅ | ❌ | ✅ (admin UI, content only) | ✅ | ✅ |
| administrator | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

> **Legend:** ✅ Allowed · ❌ Denied · 🔒 Condition-based (see note)
>
> **Note on instructor workspace access:** The `instructor` role does NOT have `administer lms_course` in config — only `manager` and `administrator` do. Instructors reach workspace features in practice only if they also carry the `manager` role or if they are given it. This is a known gap (config-inventory observation R11).
>
> **Note on mobile `/api/v1/*`:** All routes declare `_access: TRUE` but bearer-token authentication is enforced at controller level via custom keyvalue.expirable token validation (NOT OAuth2/JWT).

---

## Permissions Matrix — Config-Defined (as-built)

The tables below reflect actual `user.role.*.yml` config from `/config/sync/`. `authenticated` permissions are inherited by all logged-in users.

### Core / Platform Permissions

| Permission | anonymous | authenticated | student | instructor | manager | content_editor | administrator |
|-----------|:---------:|:-------------:|:-------:|:----------:|:-------:|:--------------:|:-------------:|
| access content | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (bypass) |
| search content | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (bypass) |
| access comments | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (bypass) |
| post comments | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ (bypass) |
| skip comment approval | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ (bypass) |
| edit own comments | ❌ | ❌ | ✅ | ✅ | ❌ | ✅ | ✅ (bypass) |
| administer comments | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ✅ (bypass) |
| use text format restricted_html | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| use text format basic_html | ❌ | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ (bypass) |
| use text format full_html | ❌ | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ (bypass) |
| access shortcuts | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ (bypass) |
| delete own files | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ (bypass) |

### LMS-Specific Config Permissions

| Permission | student | instructor | manager | content_editor | administrator |
|-----------|:-------:|:----------:|:-------:|:--------------:|:-------------:|
| administer lms_course | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| create lms_course | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| edit lms_course | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| edit own lms_course | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| delete lms_course | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| view lms_course | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| access h5p editor | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| access user profiles | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| switch users | ❌ | ❌ | ✅ | ❌ | ✅ (bypass) |
| view the administration theme | ❌ | ❌ | ✅ | ✅ | ✅ (bypass) |

### Content Editor-Specific Config Permissions

| Permission | content_editor |
|-----------|:--------------:|
| access administration pages | ✅ |
| access content overview | ✅ |
| access contextual links | ✅ |
| access files overview | ✅ |
| access toolbar | ✅ |
| administer url aliases / create url aliases | ✅ |
| create / delete / edit own article content | ✅ |
| create / delete / edit own page content | ✅ |
| create terms in tags / edit terms in tags | ✅ |
| view all revisions / revert all revisions | ✅ |
| view own unpublished content | ✅ |

---

## Access Enforcement Architecture (as-built)

Most LMS feature access is NOT controlled by Drupal permission config alone. Three layers combine:

### Layer 1: Route-level requirements (config)

| Requirement type | Used by | Example routes |
|----------------|---------|---------------|
| `_access: 'TRUE'` | Public + mobile API | `/courses`, `/api/v1/*` |
| `_permission: 'access content'` | Public learner pages | `/courses/{id}`, `/forum` |
| `_user_is_logged_in: 'TRUE'` | Learner portal | `/dashboard`, `/learn/*`, `/quiz/*` |
| `_permission: 'administer lms_course'` | Workspace/manage | `/workspace/*` (47 routes) |
| `_permission: 'administer site configuration'` | Site admin | `/admin/config/lms/ai`, `/admin/config/everlms/payment` |
| `entity_access:lms_course.update` | Entity-level edit | `/workspace/courses/{id}/edit` |
| `entity_create_access:lms_quiz` | Create access check | `/workspace/quizzes/create` |
| `_custom_access: SwitchController::canSwitchBack` | Switch-back guard | `/admin/switch-back` |

### Layer 2: Module-code / service checks

| Check | Implementation | Module |
|-------|---------------|--------|
| Membership active check | `MembershipManager::hasActiveMembership()` — returns TRUE if user has `administer lms_course` OR active `lms_user_membership` with `field_end_date > now()` | `lms_membership` |
| Order ownership | `OrderAccessControlHandler::checkAccess()` — view own order only; admin gets `administer lms_order` | `lms_order` |
| Checkout auth redirect | `CheckoutController::checkout()` — redirects anonymous to login for paid courses | `lms_order` |
| Mobile bearer token | Custom keyvalue.expirable store + OTP; validated in `MobileAuthController` | `lms_mobile_api` |

### Layer 3: Event subscriber redirect

| Subscriber | Listens to | Behavior |
|-----------|-----------|----------|
| `MembershipAccessSubscriber` | `KernelEvents::EXCEPTION` (priority 50) | On `AccessDeniedHttpException` for `/learn/*` or `/course/*` paths: redirects to `lms_course.pricing` route with a warning message |

---

## Role Definitions

### ROLE-01 — Administrator

**Description:** Drupal super-administrator. Bypasses all permission checks automatically — no explicit permissions needed.
**Assigned to:** Site owner / developers only.
**Auth method:** Drupal session (username/password; login form accepts email via lms_base validator).
**Access level:** super-admin (all operations on all entities and routes).
**Route groups:** All (Public, Learn, Workspace, Admin `/admin`, API).

#### Allowed
- All operations on all entities and routes.
- `/admin/switch-user` — user impersonation via open_switcher.
- AI engine settings (`/admin/config/lms/ai`, `/admin/everlms-ai-engine`).
- Payment/Zoom settings (`/admin/config/everlms/payment`, `/admin/config/lms/zoom`).
- Analytics reports (`/admin/lms/reports/*`).
- Certificate revoke (`/admin/lms/certificates/{id}/revoke`).
- Broadcast notifications (`/admin/lms/notifications/broadcast`).

#### Denied
- Nothing — bypass is unconditional.

#### Conditions
- By Drupal convention, uid=1 is always treated as administrator regardless of assigned roles.
- `open_switcher` prevents switching to uid=1 (unless caller is uid=1).

---

### ROLE-02 — Manager

**Description:** LMS operational administrator. Manages courses, users, orders, memberships, pricing plans, certificates, and ratings via the `/workspace` custom UI.
**Assigned to:** School/organization administrators, LMS operators.
**Auth method:** Drupal session.
**Access level:** read-write (full LMS management; no system-level Drupal config).
**Route groups:** Public, Learn, Workspace (`/workspace/*`, `/manage/*`), limited Admin (analytics: `administer lms_course` required — fulfilled).

#### Allowed (by config: `administer lms_course` + other manager permissions)
- Full `/workspace` UI: course CRUD, quiz CRUD, order management, user management, bulk users, certificate templates, membership plan management, pricing plans, ratings management.
- `/workspace/content-tools` via `administer lms content tools` — **not** assigned to manager in config; would require additional permission grant.
- `switch users` — impersonate any non-admin account via open_switcher.
- AI course builder (`/manage/courses/ai-builder`) — requires `administer lms_course`.
- Blog article CRUD (`/manage/blog`) — requires `administer lms_blog` (not confirmed in config; «unverified»).
- Analytics reports at `/admin/lms/reports/courses` (requires `administer lms_course` — fulfilled).

#### Denied
- Drupal system config (`administer site configuration`) — only administrator.
- Payment/Zoom/AI settings at `/admin/config/*`.
- User analytics at `/admin/lms/reports/users` — requires `administer users` (not assigned to manager).
- Quiz analytics at `/admin/lms/reports/quizzes` — requires `administer lms_quiz` (not assigned to manager).
- Certificate revoke at `/admin/lms/certificates/{id}/revoke` — requires `revoke any certificate` (not assigned to manager).

---

### ROLE-03 — Content Editor

**Description:** CMS-focused editor managing Drupal node content (article, page) and taxonomy. Not an LMS instructor — has no LMS entity permissions.
**Assigned to:** Blog/content staff, marketing editors.
**Auth method:** Drupal session.
**Access level:** read-write (CMS content only).
**Route groups:** Public, Admin UI (content management: `/admin/content/node`, `/admin/content/files`).

#### Allowed
- Create/edit/delete own article and page nodes.
- Create/edit tags taxonomy terms.
- Manage URL aliases.
- View/revert revisions.
- Access Drupal admin toolbar and content overview.

#### Denied
- All `/workspace/*` LMS management routes.
- All LMS entity operations (courses, quizzes, lessons, etc.).
- All commerce operations.

---

### ROLE-04 — Instructor

**Description:** Course instructor/teacher. In config, this role has only `administer comments` beyond authenticated-user baseline — all LMS content and teaching access depends on module-level ownership checks or additional roles granted by admin.
**Assigned to:** Course creators/teachers.
**Auth method:** Drupal session.
**Access level:** thin as-built (comment moderation via config; LMS features via ownership/module code or co-assignment of manager role).
**Route groups:** Public, Learn (as authenticated user). Workspace access requires `administer lms_course` — **not** present in instructor config.

#### Allowed (config-defined)
- All `authenticated` baseline permissions.
- `administer comments` — moderate comments sitewide.
- `edit own comments`.

#### Allowed (via module code / typical usage)
- View own course progress data (if enrolled).
- Take quizzes (as authenticated user).
- Forum topic creation and replies (via `entity_create_access` — available to any logged-in user).

#### Denied (notable gap)
- Workspace UI (`/workspace/*`) — blocked because `administer lms_course` not in config.
- Course/chapter/lesson creation via workspace — blocked.
- Quiz creation, order management, user management — blocked.

> **Note (as-built):** The instructor role as defined in config is very thin. The intent (create/manage own courses) requires either: (a) granting `administer lms_course` to instructor role (not done), or (b) assigning both `instructor` + `manager` roles to instructors in practice. This is documented as a known gap (config-inventory R11).

---

### ROLE-05 — Student

**Description:** Registered learner. Auto-assigned to every new user by `lms_base` `hook_user_insert` (unless already a different role). The primary consumer of the LMS learning experience.
**Assigned to:** Automatically — all new self-registered users.
**Auth method:** Drupal session (email-as-username login via `lms_base` form validator); OTP-based for mobile app.
**Access level:** read + participate (own learning only).
**Route groups:** Public, Learn (`/dashboard`, `/my-*`, `/learn/*`, `/quiz/*`, `/notifications`, `/forum`).

#### Allowed (config-defined, beyond `authenticated`)
- `access comments`, `edit own comments`, `post comments`, `skip comment approval`.

#### Allowed (via route requirements: `_user_is_logged_in: TRUE`)
- `/dashboard`, `/my-courses`, `/my-certificates`, `/my-notes`, `/my-wishlist`, `/my-quiz-results`, `/my-membership`.
- `/learn/{course}/{lesson}` — in-lesson view (progress, video, notes, quiz).
- `/quiz/{id}/take`, `/quiz/{id}/submit`, `/quiz-result/{id}`.
- `/zoom/join/{meeting_id}`.
- `/notifications`, `/forum/new`, `/forum/{topic}/reply`.
- All `/api/lms/*` AJAX endpoints.
- Mobile `/api/v1/*` (authenticated in controller).

#### Denied
- `/workspace/*` — no `administer lms_course`.
- All `/admin/*` routes.
- Course/chapter/lesson creation or editing.
- Commerce management (can purchase, but not manage orders for others).

#### Conditions
- Lesson/course access enforced by `MembershipAccessSubscriber`: accessing paid content without active membership → redirect to pricing.
- Order (purchase) view: own orders only (`OrderAccessControlHandler`).

---

### ROLE-06 — Authenticated

**Description:** Base role granted to all logged-in users. Provides Drupal standard permissions common to all registered accounts.
**Assigned to:** All logged-in users (auto by Drupal).
**Auth method:** Drupal session.
**Access level:** basic read + contribute.
**Route groups:** Public, basic Learn (some `/my-*` routes).

#### Allowed
- `access content`, `access comments`, `access shortcuts`.
- `post comments`, `skip comment approval`.
- `delete own files`.
- `use text format basic_html`, `use text format full_html`, `use text format restricted_html`.
- `search content`.

---

### ROLE-07 — Anonymous

**Description:** Unauthenticated site visitor. Can browse public course catalog, news, and forum listings without logging in.
**Assigned to:** All unauthenticated visitors (auto by Drupal).
**Auth method:** None (session-less or cookie session without login).
**Access level:** read-only (public content).
**Route groups:** Public routes (19 routes, `access content` or `_access: TRUE`).

#### Allowed
- Browse: `/homepage`, `/courses`, `/courses/{id}`, `/categories`, `/news`, `/news/{node}`, `/forum`, `/about`, `/contact`, `/pricing`, `/glossary`, `/instructor/{user}`.
- `/certificate/verify` — public certificate verification form.
- `/checkout/{course_id}`, `/checkout/membership/{plan_id}` — checkout pages (redirected to login by `CheckoutController` if payment required).
- `/api/lms/notifications/unread-count` (returns 0 for anon).
- Course search API `/api/courses/search`.

#### Denied
- All Learn/App routes (redirect to login).
- All Workspace/Admin routes.
- All authenticated API endpoints.

---

## open_switcher — User Impersonation

`open_switcher` is a separate utility module (package: "Open Platform", not "EverLMS") that enables admin-level impersonation for testing and support.

| Attribute | Value |
|-----------|-------|
| Permission | `switch users` (assigned to: `manager`, `administrator`) |
| Admin route | `/admin/switch-user` — `SwitchUserForm` (paged user table with role filter) |
| Frontend route | `/app/switch-user` — `SwitchFrontendController` (EverLMS-styled impersonation picker) |
| Switch-to route | `/admin/switch-user/{uid}` and `/app/switch-user/{uid}` |
| Switch-back route | `/admin/switch-back` — guarded by `SwitchController::canSwitchBack()` (checks session key) |

**Mechanism:**
1. Original UID + name stored in Drupal session (`open_switcher_original_uid`, `open_switcher_original_name`).
2. `user_login_finalize($account)` called to finalize the impersonated user's session.
3. On switch-back: session keys cleared, original UID re-finalized.
4. A `open_switcher_dialog` one-time session value triggers a JS confirmation dialog on the next page load.

**Safety guards:**
- Cannot switch to self.
- Cannot switch to uid=1 (unless the impersonating user IS uid=1).
- Cannot switch to `administrator`-role users (filtered out in `SwitchFrontendController::page()`).
- Switch-back requires the session key to be present (prevents unauthorized invocation).

**Menu links** (account menu):
- "Switch User" → `/app/switch-user` (visible when `switch users` permission held).
- "Switch Back" → `/admin/switch-back` (visible when actively impersonating, via `hook_preprocess_menu`).

---

## Editorial Management Matrix

| Surface / Entity | Create | Edit own | Edit any | Publish | Delete | Admin URL |
|---|---|---|---|---|---|---|
| `lms_course` | manager+ | manager+ | manager+ | manager+ | manager+ | `/workspace/courses` |
| `lms_chapter` | manager+ | manager+ | manager+ | N/A | manager+ | `/workspace/chapters/{id}/admin-edit` |
| `lms_lesson` | manager+ | manager+ | manager+ | N/A | manager+ | `/workspace/lessons/{id}/admin-edit` |
| `lms_quiz` | manager+ | manager+ | manager+ | N/A | manager+ | `/workspace/quizzes` |
| `lms_article` (blog) | manager+ | manager+ | manager+ | manager+ | manager+ | `/manage/blog` |
| `node:blog` | admin only | admin only | admin only | admin only | admin only | `/admin/content/node` |
| `node:article` | content_editor+ | content_editor+ | content_editor+ | content_editor+ | own only | `/admin/content/node` |
| `node:page` | content_editor+ | content_editor+ | content_editor+ | content_editor+ | own only | `/admin/content/node` |
| `lms_membership_plan` | manager+ | manager+ | manager+ | N/A (status toggle) | manager+ | `/workspace/pricing` |
| `lms_user_membership` | manager+ | manager+ | manager+ | N/A | manager+ | `/workspace/user-memberships` |
| `lms_order` | (system) | admin only | admin only | N/A | admin only | `/workspace/orders` |
| `lms_certificate_template` | manager+ | manager+ | manager+ | N/A | manager+ | `/workspace/certificate-templates` |
| `lms_rating` | authenticated (via course page) | own | manager+ | manager+ (approve) | manager+ | `/workspace/ratings` |
| `lms_forum_topic` | authenticated (logged_in) | own | manager+ | N/A | own/manager+ | `/forum` |

> "manager+" means `manager` role or `administrator`.

---

## API Consumer Roles

| Role | Auth Method | Endpoints | Notes |
|------|-------------|-----------|-------|
| Mobile app (Flutter) | Custom bearer token (keyvalue.expirable) issued after OTP verification | `/api/v1/*` (22 endpoints) | Token issued via `/api/v1/oauth/token`; NOT OAuth2/JWT despite route name. All `/api/v1/*` routes are `_access: TRUE` — auth enforced in controller. |
| Web frontend (AJAX) | Drupal session cookie | `/api/lms/*`, `/api/courses/*` | Standard Drupal CSRF + session auth; `_user_is_logged_in: TRUE` or `_access: TRUE` per route. |

---

## Change Log

| Date | Changed By | Change |
|------|------------|--------|
| 2026-06-20 | SA | Initial as-built document; grounded in config-inventory.md, routes-controllers-templates.md, modules-commerce.md, modules-foundation.md |
