---
page: auth-login-register
mode: as-built
owner_module: lms_base
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Authentication (Login / Register / Password Reset)

Three pages sharing the same authentication surface: login, register, and password reset. All use Drupal core user forms (`user_login_form`, `user_register_form`, `user_pass`) customized via `lms_base` hooks and branded via `lms_theme` custom page templates.

---

**Canonical URLs:**
- Login: `/user/login`
- Register: `/user/register`
- Password Reset: `/user/password`

**Route names:**
- `user.login` (core)
- `user.register` (core)
- `user.pass` (core)

**Solution:** Drupal core user forms rendered through `lms_theme` custom page templates (`page--user--login.html.twig`, `page--user--register.html.twig`, `page--user--password.html.twig`). `lms_base` module alters the forms via `hook_form_alter` to: rename email label, add placeholders, email-based login validation, hide profile fields for anonymous. Auto-assigns `student` role on registration via `hook_user_insert`.

**Owner module:** `lms_base` (form hooks + role assignment) · `lms_theme` (page templates)

**Primary source:** Drupal core user system (`user.login`, `user.register`, `user.pass`)

**Editable source:** Site branding and copy from `lms_theme.settings` (logo, site name); Drupal core user settings at `/admin/config/people/accounts`; social auth via `social_auth` (not configured — `social_auth.settings` has no active providers)

**Editor role:** `administrator`

**Editor management UI:** `/admin/config/people/accounts` (user account settings) · `/admin/appearance/settings/lms_theme` (branding)

**Revision / moderation:** N/A

**Forms:**
- `user_login_form` — altered by `lms_base_form_alter()`: renames "Username or email address" label to "Email address"; adds email validator `lms_base_user_login_validate` (converts email to username before core auth)
- `user_register_form` — altered by `lms_base_form_alter()`: adds placeholders; hides profile fields for anonymous users
- `user_pass` — altered by `lms_base_form_alter()`: adds "Enter your email address" placeholder

**Theme template:**
- `lms_theme/templates/page/page--user--login.html.twig`
- `lms_theme/templates/page/page--user--register.html.twig`
- `lms_theme/templates/page/page--user--password.html.twig`
- `lms_theme/templates/user/user-login-form.html.twig` (form-level override)
- `lms_theme/templates/user/user-register-form.html.twig` (form-level override)

**Access:** Public (anonymous) — Drupal core access; authenticated users redirected to front page

**Cache:** No-cache (user-specific form tokens; POST forms)

**Translation:** Monolingual EN

**Related entities:** `user` (Drupal core)

**Prototype reference:** [`../../C10-prototypes/html/public/login.html`](../../C10-prototypes/html/public/login.html) · [`../../C10-prototypes/html/public/register.html`](../../C10-prototypes/html/public/register.html)

**Notes:** The system is **email-based login** (not username-based). `lms_base_user_login_validate` converts the submitted email to a Drupal username before passing to core auth. No OAuth/social login providers are active (`social_auth.settings.yml` shows empty providers array). Post-login redirect is `/user` (social_auth default). After successful login, `hook_user_login` clears lingering error messages. New users get `student` role automatically via `hook_user_insert`. Password policy is installed (`password_policy` module referenced in user entity fields: `field_last_password_reset`, `field_password_expiration`, `field_pending_expire_sent`) but no active policies beyond cron threshold are configured.

---

## Components — Login Page (`/user/login`)

> Global chrome is defined in [`../_common.md`](../_common.md). Auth pages typically use a minimal page template (no nav).

### 1. Login Card / Brand Panel

- **Scope:** Page-specific
- **Implementation mechanism:** `page--user--login.html.twig` — centered card layout with site logo + form
- **Plugin / Module:** `lms_theme` (page template) · `lms_base` (hook_form_alter)
- **Source content type / Entity:** `lms_theme.settings` (logo, site_name) · `system.site` (site name fallback)
- **Backed by fields / config keys:** `logo`, `site_name` from `lms_theme.settings` / `system.site.name`
- **Editable source:** `lms_theme.settings` · `system.site`
- **Editor role / permission:** `administrator` / `administer themes`
- **Editor management UI:** `/admin/appearance/settings/lms_theme` · `/admin/config/system/site-information`
- **Revision / moderation:** N/A
- **Translation source:** Config translation
- **Edit smoke:** Update site name → reload `/user/login` → name appears on card
- **View mode / Projection:** Centred auth card (dark glassmorphism per brand theme)
- **Region / Placement:** Full-page centred (no standard header/nav in auth templates)
- **Notes:** Prototype shows "Welcome Back" heading, logo, split layout. Auth pages use minimal template (no primary nav, no footer). Dark glassmorphism per `lms_theme` design system
- **How to implement (EN):** Custom page template for login: hide header/nav/footer; render logo + "Welcome Back" heading; embed user_login_form
- **How to implement (VI):** Template trang tùy chỉnh cho login: ẩn header/nav/footer; render logo + tiêu đề "Welcome Back"; nhúng user_login_form
- **Solution Architect Request:** —

### 2. Login Form

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal core `user_login_form` altered by `lms_base_form_alter`
- **Plugin / Module:** `lms_base` (form alter) · Drupal core `user` module (form)
- **Source content type / Entity:** Drupal core user authentication
- **Backed by fields / config keys:** `name` field (used as email input after `lms_base_user_login_validate` translates it); `pass` field
- **Editable source:** User account (email stored in `mail` field of `users_field_data`)
- **Editor role / permission:** N/A (self-service form)
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Login with valid email/password → redirected to front page or intended destination
- **View mode / Projection:** Email + password form, "Remember me" checkbox, submit button
- **Region / Placement:** Inside login card
- **Notes:** Email label renamed from "Username or email address" to "Email address" via hook_form_alter. Validator converts email → username. "Forgot password" link points to `/user/password`. No OAuth/social login buttons (social_auth not active). Prototype shows error state (red border + error message) as a second form variant
- **How to implement (EN):** Use Drupal core user_login_form; add hook_form_alter in lms_base to rename label and add email validator; theme via user-login-form.html.twig
- **How to implement (VI):** Dùng user_login_form của Drupal core; thêm hook_form_alter trong lms_base để đổi nhãn và thêm email validator; theme qua user-login-form.html.twig
- **Solution Architect Request:** —

### 3. "New here?" Register Link

- **Scope:** Page-specific
- **Implementation mechanism:** Static link in `page--user--login.html.twig` or form alter
- **Plugin / Module:** `lms_theme` (template) or `lms_base` (form alter)
- **Source content type / Entity:** Static URL `/user/register`
- **Backed by fields / config keys:** Static template link
- **Editable source:** Template (static)
- **Editor role / permission:** Developer
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** Inline text link
- **Region / Placement:** Below login form
- **Notes:** Standard cross-link between login and register pages
- **How to implement (EN):** Add static link to `/user/register` below the login form in template
- **How to implement (VI):** Thêm link tĩnh đến `/user/register` bên dưới form đăng nhập trong template
- **Solution Architect Request:** —

---

## Components — Register Page (`/user/register`)

### 4. Registration Form

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal core `user_register_form` altered by `lms_base_form_alter`
- **Plugin / Module:** `lms_base` (form alter) · Drupal core `user` module
- **Source content type / Entity:** Drupal core user entity
- **Backed by fields / config keys:** `mail`, `name`, `pass`, profile fields (hidden for anonymous by lms_base alter)
- **Editable source:** User account fields; user account settings at `/admin/config/people/accounts`
- **Editor role / permission:** N/A (self-service form); admin configures field visibility
- **Editor management UI:** `/admin/config/people/accounts` · `/admin/structure/types` for user entity display
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Register new account → verify student role auto-assigned → verify email received (if SMTP configured)
- **View mode / Projection:** Registration form (email, username, password, optional profile fields)
- **Region / Placement:** Centred card (same layout as login)
- **Notes:** `lms_base_form_alter` adds placeholders and hides profile fields for anonymous. `hook_user_insert` auto-assigns `student` role to new users. Email verification behaviour depends on `/admin/config/people/accounts` settings (currently SMTP is disabled — `smtp.settings.yml` shows smtp_on=false). Prototype shows "Join EverLMS" heading
- **How to implement (EN):** Use Drupal core user_register_form; alter in lms_base to add placeholders and hide irrelevant profile fields; ensure hook_user_insert assigns student role
- **How to implement (VI):** Dùng user_register_form Drupal core; alter trong lms_base để thêm placeholder và ẩn trường hồ sơ không liên quan; đảm bảo hook_user_insert gán role student
- **Solution Architect Request:** —

---

## Components — Password Reset Page (`/user/password`)

### 5. Password Reset Form

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal core `user_pass` form altered by `lms_base_form_alter` (adds placeholder)
- **Plugin / Module:** `lms_base` (form alter) · Drupal core `user` module
- **Source content type / Entity:** Drupal core user (looked up by email)
- **Backed by fields / config keys:** `mail` (user email field)
- **Editable source:** N/A (self-service password recovery)
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Submit email → one-time login link sent (when SMTP configured) or email logged
- **View mode / Projection:** Single email input + submit button
- **Region / Placement:** Centred card
- **Notes:** `lms_base_form_alter` adds "Enter your email address" placeholder to `user_pass` form. Mail delivery requires SMTP configuration (currently disabled). Standard Drupal one-time-login flow
- **How to implement (EN):** Use Drupal core user_pass form; add placeholder via hook_form_alter; render in page--user--password.html.twig
- **How to implement (VI):** Dùng form user_pass Drupal core; thêm placeholder qua hook_form_alter; render trong page--user--password.html.twig
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Brand Panel (all auth pages) | Logo, site name, page title ("Welcome Back" / "Join EverLMS" / "Reset Password") | `lms_theme.settings`, `system.site` | logo, site_name | `/admin/appearance/settings/lms_theme` | administrator | N/A | Config translation | config:lms_theme.settings | Update site name → reload auth page |
| Login Form | Email field, password field, "Remember me", submit, forgot-password link | Drupal core `user_login_form` (altered by lms_base) | name (email via validator), pass | N/A (self-service) | N/A | N/A | Interface translation | No-cache | Login with valid credentials → redirect |
| Registration Form | Email, username, password, optional profile fields | Drupal core `user_register_form` (altered by lms_base) | mail, name, pass, profile fields | `/admin/config/people/accounts` | administrator | N/A | Interface translation | No-cache | Register → student role assigned |
| Password Reset Form | Email field, submit | Drupal core `user_pass` (altered by lms_base) | mail | N/A | N/A | N/A | Interface translation | No-cache | Submit email → reset link sent |
| Register / Login cross-links | "Already have an account? Login" / "New here? Sign up" | Static template links | — | Template edit | Developer | N/A | Interface translation | N/A | Check template links resolve correctly |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "Welcome Back" / "Join EverLMS" headings | Static page template strings | `lms_theme` templates | Move to lms_theme.settings if copy needs to vary |
| "Email address" field label override | lms_base hook_form_alter static string | `lms_base/lms_base.module` | Move to translatable string if i18n needed |
| Placeholder text on all auth forms | lms_base hook_form_alter static strings | `lms_base/lms_base.module` | Acceptable; update in code if copy changes |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Login Form (email-based) | DONE | — | lms_base hook_form_alter + lms_base_user_login_validate |
| Login Page Template | DONE | — | page--user--login.html.twig + user-login-form.html.twig in lms_theme |
| Registration Form | DONE | — | lms_base form_alter; student role auto-assign on user_insert |
| Registration Page Template | DONE | — | page--user--register.html.twig + user-register-form.html.twig in lms_theme |
| Password Reset Form | DONE | — | lms_base adds placeholder; core user_pass form |
| Password Reset Template | DONE | — | page--user--password.html.twig in lms_theme |
| Social Login (OAuth) | NOT IMPLEMENTED | — | social_auth module installed but no providers active |
| Email delivery | PLANNED | — | SMTP disabled; requires SMTP or transactional email config for production |
