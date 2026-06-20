---
page: about-contact
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: About Us / Contact Us

Two static marketing pages served by `PageController` in `lms_course`. Documented together as they share the same controller, same pattern, and similar component structure.

---

## About Us — `/about`

**Canonical URL:** `/about`
**Route name:** `lms_course.about`
**Solution:** `PageController::about()` renders `lms_about` Twig template with static team data + theme settings.
**Owner module:** `lms_course` (`web/modules/custom/lms_course/src/Controller/PageController.php`)
**Primary source:** `lms_theme.settings` (copy, branding) + hardcoded team data in `PageController::about()`
**Editable source:** `lms_theme.settings` for site copy; **team member data is hardcoded in PHP** (not entity-managed) — known editorial gap
**Editor role:** `administrator` (theme settings); developer only for team data
**Editor management UI:** `/admin/appearance/settings/lms_theme`
**Revision / moderation:** N/A (config + static)
**Forms:** `—`
**Theme template:** `lms_course/templates/lms-about.html.twig`
**Access:** Public (anonymous) — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `config:lms_theme.settings`
**Translation:** Monolingual EN
**Related entities:** None (static page)
**Prototype reference:** [`../../C10-prototypes/html/public/about.html`](../../C10-prototypes/html/public/about.html)
**Notes:** `PageController::about()` calls `getPublicPageSettings()` (reads lms_theme.settings + system.site) and passes a static team array with hardcoded names ("John Smith, Sarah Johnson, …"). These placeholder names are committed in source code — not loaded from any user or content entity. This is a known implementation gap: team content should be entity-backed for production. Prototype shows hero, mission/vision sections, team cards, timeline, and values section.

---

## Contact Us — `/contact`

**Canonical URL:** `/contact`
**Route name:** `lms_course.contact`
**Solution:** `PageController::contact()` renders `lms_contact` Twig template with contact info from `lms_theme.settings`.
**Owner module:** `lms_course` (`web/modules/custom/lms_course/src/Controller/PageController.php`)
**Primary source:** `lms_theme.settings` (contact address, email, phone, social links)
**Editable source:** `lms_theme.settings` via theme settings admin form
**Editor role:** `administrator`
**Editor management UI:** `/admin/appearance/settings/lms_theme`
**Revision / moderation:** N/A (config)
**Forms:** `—` (no contact form in current implementation; contact info display only)
**Theme template:** `lms_course/templates/lms-contact.html.twig`
**Access:** Public (anonymous) — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `config:lms_theme.settings`
**Translation:** Monolingual EN
**Related entities:** None
**Prototype reference:** [`../../C10-prototypes/html/public/about.html`](../../C10-prototypes/html/public/about.html) *(about + contact share prototype file)*
**Notes:** No contact form (no `webform`, no `contact` module integration). `getPublicPageSettings()` reads contact fields from `lms_theme.settings`. If settings not configured, defaults to placeholders. A form-based contact page is target-state.

---

## Components — About Us (visual order)

> Global chrome is defined in [`../_common.md`](../_common.md).

### 1. Hero Section

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in `lms-about.html.twig`
- **Plugin / Module:** `lms_course` — `PageController`
- **Source content type / Entity:** `lms_theme.settings` (about_headline, about_subheadline, hero image)
- **Backed by fields / config keys:** Theme settings keys for about page copy
- **Editable source:** `lms_theme.settings`
- **Editor role / permission:** `administrator` / `administer themes`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A
- **Translation source:** Config translation
- **Edit smoke:** Update about_headline in theme settings → reload `/about` → headline changes
- **View mode / Projection:** Full-width hero with text overlay
- **Region / Placement:** Top of page
- **Notes:** Prototype shows "Empowering Learners, One Skill at a Time" headline with CTA button
- **How to implement (EN):** Read about page copy from lms_theme.settings; pass to template; render hero section
- **How to implement (VI):** Đọc nội dung trang About từ lms_theme.settings; truyền vào template; render section hero
- **Solution Architect Request:** —

### 2. Stats Bar

- **Scope:** Page-specific
- **Implementation mechanism:** Static section or from `PageController::getHomepageStats()` pattern
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Live DB counts (courses, learners, certificates) from `lms_theme.settings` string overrides or live query
- **Backed by fields / config keys:** Course count, cert count, learner count (live or overridden in theme settings)
- **Editable source:** `lms_theme.settings` (stat overrides) or computed from DB
- **Editor role / permission:** `administrator`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A (computed values)
- **View mode / Projection:** Stats bar (number + label)
- **Region / Placement:** Below hero (per prototype)
- **Notes:** Prototype shows stats: courses, learners, instructors, certificates. May use `getHomepageStats()` pattern from `CoursePublicController`; about page stats not confirmed in `PageController::about()` facts
- **How to implement (EN):** Use same stats helper as homepage; pass course_count, cert_count, learner_count; render stat bar in template
- **How to implement (VI):** Dùng helper stats giống homepage; truyền course_count, cert_count, learner_count; render thanh thống kê trong template
- **Solution Architect Request:** —

### 3. Mission / Vision Sections

- **Scope:** Page-specific
- **Implementation mechanism:** Static sections in `lms-about.html.twig`
- **Plugin / Module:** `lms_course` — `PageController::getPublicPageSettings()`
- **Source content type / Entity:** `lms_theme.settings` (about_mission, about_vision copy)
- **Backed by fields / config keys:** Theme settings copy keys
- **Editable source:** `lms_theme.settings`
- **Editor role / permission:** `administrator`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A
- **Translation source:** Config translation
- **Edit smoke:** Update about_mission setting → reload `/about` → text updates
- **View mode / Projection:** Two-column text + image sections
- **Region / Placement:** Mid-page (per prototype)
- **Notes:** Prototype shows "Learning without limits" and "A skill-first world by 2030" sections
- **How to implement (EN):** Define theme settings keys for mission/vision text; pass to template
- **How to implement (VI):** Định nghĩa các key theme settings cho nội dung sứ mệnh/tầm nhìn; truyền vào template
- **Solution Architect Request:** —

### 4. Team Cards

- **Scope:** Page-specific
- **Implementation mechanism:** Static array in `PageController::about()` (hardcoded PHP)
- **Plugin / Module:** `lms_course` — `PageController::about()`
- **Source content type / Entity:** **Hardcoded static array** in PHP — names like "John Smith, Sarah Johnson, …" — NOT entity-backed
- **Backed by fields / config keys:** Hardcoded in `PageController::about()` method
- **Editable source:** Source code only — `web/modules/custom/lms_course/src/Controller/PageController.php`
- **Editor role / permission:** Developer only (code change required)
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A (requires code deploy)
- **View mode / Projection:** Team member cards (photo, name, title, bio)
- **Region / Placement:** "People behind EverLMS" section
- **Notes:** KNOWN GAP — team data is hardcoded placeholder content. Production requires either: (a) loading from Drupal `user` entities (role=instructor), or (b) a dedicated `node:team_member` content type, or (c) theme settings keys for team members
- **How to implement (EN):** Replace hardcoded array with query on user entities filtered by `instructor` role; or create a `node:team_member` content type and load nodes here
- **How to implement (VI):** Thay thế mảng hardcode bằng truy vấn user entities theo role `instructor`; hoặc tạo content type `node:team_member` và tải nodes tại đây
- **Solution Architect Request:** —

### 5. Timeline / Milestones

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in `lms-about.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static content in template or `lms_theme.settings`
- **Backed by fields / config keys:** Static or theme settings
- **Editable source:** `lms_theme.settings` or template (static)
- **Editor role / permission:** `administrator` or developer
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Timeline (horizontal or vertical)
- **Region / Placement:** Mid-page
- **Notes:** Prototype shows "Our milestones" timeline. Static content in template; not entity-managed
- **How to implement (EN):** Hardcode milestone entries in template or read from theme settings JSON field
- **How to implement (VI):** Hardcode các mốc quan trọng trong template hoặc đọc từ trường JSON trong theme settings
- **Solution Architect Request:** —

### 6. Values / CTA Section

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in `lms-about.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static
- **Backed by fields / config keys:** Static template strings
- **Editable source:** Template (static)
- **Editor role / permission:** Developer
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Value cards + CTA
- **Region / Placement:** Bottom of page
- **Notes:** Prototype shows "What we stand for" values and "Ready to start your journey?" CTA
- **How to implement (EN):** Static section in template with link to /courses
- **How to implement (VI):** Section tĩnh trong template với link đến /courses
- **Solution Architect Request:** —

---

## Components — Contact Us (visual order)

### 1. Contact Info Panel

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in `lms-contact.html.twig` reading from `lms_theme.settings`
- **Plugin / Module:** `lms_course` — `PageController::contact()` → `getPublicPageSettings()`
- **Source content type / Entity:** `lms_theme.settings` (contact_address, contact_email, contact_phone, social_links)
- **Backed by fields / config keys:** `lms_theme.settings` contact keys; `system.site` for site name
- **Editable source:** `lms_theme.settings`
- **Editor role / permission:** `administrator` / `administer themes`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A
- **Translation source:** Config translation
- **Edit smoke:** Update contact_email in theme settings → reload `/contact` → email updates
- **View mode / Projection:** Info cards (address, email, phone, hours)
- **Region / Placement:** Main content area
- **Notes:** `PageController::getPublicPageSettings()` reads shared branding/contact settings. If not configured, shows placeholders
- **How to implement (EN):** Read contact settings from lms_theme.settings; pass to template variables; render contact info cards
- **How to implement (VI):** Đọc cài đặt liên hệ từ lms_theme.settings; truyền vào biến template; render thẻ thông tin liên hệ
- **Solution Architect Request:** —

### 2. Contact Form (Target-State)

- **Scope:** Page-specific
- **Implementation mechanism:** Not yet implemented — no contact form exists in `PageController::contact()`
- **Plugin / Module:** N/A (planned)
- **Source content type / Entity:** N/A
- **Backed by fields / config keys:** N/A
- **Editable source:** N/A
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Web form
- **Region / Placement:** Right column of contact page
- **Notes:** Prototype may show a contact form. Current implementation is display-only (no form). To add: integrate Drupal `contact` module or `webform` contrib, or build a custom FormBase
- **How to implement (EN):** Add Drupal `contact` module; configure site-mail contact form; render in `lms-contact.html.twig` via `drupal_get_form()` or embed entity form
- **How to implement (VI):** Cài module `contact` của Drupal; cấu hình form liên hệ; render trong `lms-contact.html.twig` qua `drupal_get_form()` hoặc nhúng entity form
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| About Hero | Headline, subheadline, hero image | `lms_theme.settings` | about_headline, about_subheadline | `/admin/appearance/settings/lms_theme` | administrator | N/A | Config translation | config:lms_theme.settings | Update setting → reload |
| Stats Bar | Courses count, learner count, cert count | DB computed + lms_theme.settings overrides | stat_courses, stat_learners (or live query) | `/admin/appearance/settings/lms_theme` | administrator | N/A | EN | — | — |
| Mission / Vision | Mission text, vision text | `lms_theme.settings` | about_mission, about_vision | `/admin/appearance/settings/lms_theme` | administrator | N/A | Config translation | config:lms_theme.settings | Update setting → reload |
| Team Cards | Name, title, bio, photo | Hardcoded PHP array | — (hardcoded) | Source code only | Developer | N/A | N/A | N/A | Code deploy required |
| Timeline | Milestone items | Static template | — | Template edit | Developer | N/A | N/A | N/A | Code deploy |
| Values / CTA | Value descriptions, CTA | Static template | — | Template edit | Developer | N/A | N/A | N/A | Code deploy |
| Contact Info | Address, email, phone, hours | `lms_theme.settings` | contact_address, contact_email, contact_phone | `/admin/appearance/settings/lms_theme` | administrator | N/A | Config translation | config:lms_theme.settings | Update setting → reload |
| Contact Form | Form fields, submit | Not implemented | — | N/A | N/A | N/A | N/A | N/A | Not implemented |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Team member names/titles/bios ("John Smith, …") | Hardcoded placeholder in PageController::about() | `PageController.php` (known gap) | Replace with user entity query (role=instructor) before production |
| Milestone entries | Static template content | `lms-about.html.twig` | Move to theme settings or node content type when needed |
| Values descriptions | Static template content | `lms-about.html.twig` | Move to theme settings when editorial control needed |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| About Hero | DONE | — | Copy from lms_theme.settings |
| Stats Bar | DONE | — | Follows getHomepageStats() pattern |
| Mission / Vision | DONE | — | Copy from lms_theme.settings |
| Team Cards | DONE (stub data) | — | Hardcoded placeholder names — not production-ready |
| Timeline | DONE (static) | — | Static template content |
| Values / CTA | DONE (static) | — | Static template content |
| Contact Info | DONE | — | From lms_theme.settings via getPublicPageSettings() |
| Contact Form | PLANNED | — | No form module integration; display-only |
