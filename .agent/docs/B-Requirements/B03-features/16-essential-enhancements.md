---
id: F-016
name: Essential Enhancements
priority: Must
status: APPROVED
parent-requirements: [BR-001]
owning_modules: [lms_wishlist, lms_bundle, lms_note, lms_order, lms_notification, lms_zoom]
primary_source: C05
story_anchors: []
---

# F-016 — Essential Enhancements

## User story

As a **learner or platform owner**, I want core convenience features — wishlist, course bundles, lesson notes, and abandoned-cart recovery — so that the platform feels complete and drives enrollment and retention.

## Description

This feature file covers enhancements that are either fully implemented as-built or partially implemented, and that do not fit cleanly into the larger feature areas (assessment, commerce, progress, communications). The enhancements addressed here are:

1. **Wishlist** (`lms_wishlist`) — as-built: entity, routes, toggle API, mobile integration.
2. **Course bundles** (`lms_bundle`) — as-built: entity, pricing, checkout integration, homepage section.
3. **Lesson notes** (`lms_note`) — as-built: entity, CRUD routes, mobile API.
4. **Abandoned cart recovery** — target-state: no cron job or reminder email logic exists in `lms_order`.
5. **Course free preview** — target-state: no `field_is_preview` field exists on `lms_lesson`; lesson access is gated by enrollment check only.
6. **Social login** — partial stub: `social_auth` contrib is installed and configured in code, but `social_auth.settings.yml` shows no active providers; the `views.view.social_auth_profiles` view exists but no provider modules (google_auth, facebook_auth) appear to be configured. Mobile app has Google, Facebook, Apple, and Microsoft (MSAL) social auth wired.
7. **Calendar integration / iCal** — target-state: no iCal endpoint exists; Zoom lesson schedule data is available but not exported.
8. **Instructor revenue dashboard** — target-state: `lms_analytics` has per-course revenue queries but no instructor-scoped view.
9. **Email drip campaigns** — target-state: no `lms_email_campaign` module or drip logic in `lms_notification`.

## In scope

- `lms_wishlist` entity (user + course, created timestamp) — as-built
- Wishlist toggle endpoints (`/api/lms/wishlist/toggle`, `/api/lms/wishlist/status`, `/api/v1/wishlist`) — as-built
- `lms_bundle` entity (name, description, thumbnail, included courses, bundle price) — as-built
- Bundle checkout integration (purchase → auto-enroll all included courses) — as-built
- `lms_note` entity (user + lesson + text + timestamps) — as-built; CRUD routes, mobile API (`/api/lms/note/get`, `save`, `delete`)
- Abandoned cart recovery design (requirements only — no implementation)
- Course free preview design (requirements only — no implementation)
- Social login requirements (contrib `social_auth` is present; full provider config is outstanding)
- Calendar / iCal export requirements
- Instructor revenue dashboard requirements
- Email drip campaign requirements

## Out of scope

- `lms_rating`, `lms_forum`, `lms_notification` (covered in their own feature files)
- H5P interactive content (separate integration)
- Payment gateway implementation (F-004 commerce area)
- Dark mode toggle (F-013 EG-05)

## Acceptance summary

Wishlist: a logged-in student can toggle a course on/off their wishlist via the heart icon on course cards; the wishlist list page reflects the change immediately. Bundles: an admin can create a `lms_bundle` with multiple courses and a discounted price; a student purchasing the bundle is auto-enrolled in all included courses. Notes: a student can create, edit, and delete notes on a lesson; notes persist across sessions. Full Given/When/Then lives in `B05-acceptance-criteria/F-016.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Bundle name, description, price, included courses | Training manager | manager, administrator | `/manage/bundles` (entity CRUD) | `lms_bundle` entity |
| Wishlist | Learner | student (self) | Course cards / wishlist page | `lms_wishlist` entity, user-owned |
| Lesson notes | Learner | student (self) | Lesson view sidebar / `/my-notes` | `lms_note` entity, user+lesson scoped |
| Abandoned cart email template | Marketing | administrator | (planned — not implemented) | target-state |
| Email drip templates | Marketing | administrator | (planned — not implemented) | target-state |

## Implemented enhancements (as-built)

### 1. Wishlist (`lms_wishlist`)

**Status: as-built.**

The `lms_wishlist` module provides a lightweight entity (user_id, course_id, created timestamp) and three API endpoints serving both the web frontend and the Flutter mobile app.

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/lms/wishlist/toggle` | POST | Add or remove a course from the user's wishlist |
| `/api/lms/wishlist/status` | POST | Batch wishlist status check for a list of course IDs |
| `/api/v1/wishlist` | GET | Full wishlist for the current user |

Mobile integration: `api/lms/wishlist/toggle` and `api/lms/wishlist/status` are called from the Flutter app's course card and course detail screens.

Anonymous user behavior: prompts login (standard Drupal auth redirect); no wishlist state saved for anonymous.

### 2. Course bundles (`lms_bundle`)

**Status: as-built.**

The `lms_bundle` module provides a `lms_bundle` content entity managed within `lms_order`. A bundle groups multiple `lms_course` entities under a single discounted price.

Key entity fields: `name`, `field_description`, `field_thumbnail` (image), `field_courses` (entity_reference to lms_course, multi-value), `field_price` (decimal bundle price), `field_original_price` (computed or set — original sum for savings display).

Checkout behavior: purchasing a bundle via `lms_order` auto-enrolls the buyer in all courses referenced by `field_courses`.

Homepage: a "Popular Bundles" section is available as a toggleable homepage section in the `lms_theme` settings (via `homepage_style` section visibility toggles).

Admin CRUD: `/manage/bundles` (collection), `/manage/bundles/add`, `/manage/bundles/{id}/edit`.

### 3. Lesson notes (`lms_note`)

**Status: as-built.**

The `lms_note` module provides a `lms_note` content entity (user + lesson + text + created/changed timestamps) with full CRUD routes and mobile API support.

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/lms/note/get` | GET | Get note for current user + lesson |
| `/api/lms/note/save` | POST | Create or update note |
| `/api/lms/note/delete` | DELETE | Delete note |

Web: notes are displayed in a side panel on the lesson view page (toggle show/hide). All-notes view: `/my-notes` (lists notes by course/lesson).

Mobile: `lms_note` endpoints are called from `LessonViewScreen` in the Flutter app.

## Target-state enhancements (not yet implemented)

### 4. Abandoned cart recovery

**Status: target-state.**

No cron job, reminder email, or `field_reminder_sent` field exists in `lms_order`. The `lms_order` entity has a `field_status` field that includes a `cart` value — the structural hook is there, but the recovery logic is absent.

Planned requirements:
- Track cart state when a user adds a course but does not complete payment (`field_status = cart`)
- Send reminder emails at +1h, +24h, +72h after cart abandonment
- Optional discount coupon in the third reminder
- `field_reminder_sent` flag on `lms_order` to prevent duplicate sends
- Admin stats: carts abandoned, recovered, recovery rate
- Admin toggle to enable/disable abandoned cart emails

Implementation path: cron job in `lms_order`; no new entity required beyond `field_reminder_sent` field addition.

### 5. Course free preview / preview lessons

**Status: target-state.**

No `field_is_preview` boolean field exists on `lms_lesson`. Lesson access is currently gated by enrollment check only (no concept of a "preview lesson" accessible to unenrolled users).

Planned requirements:
- `field_is_preview` boolean on `lms_lesson`
- Anonymous and unenrolled users can view preview lessons
- Non-preview lessons display a lock indicator and "Enroll to access" call-to-action
- Course detail page shows "X Free Lessons" badge
- Instructor can set the number of free preview lessons per course

Implementation path: one new field on `lms_lesson`, update to lesson access controller.

### 6. Social login (web)

**Status: partial stub — infrastructure present, providers not configured.**

The `social_auth` contrib module is installed (config: `social_auth.settings.yml`, `views.view.social_auth_profiles`). However:
- `social_auth.settings.yml` shows no active auth providers (`providers: []`)
- No `social_auth_google`, `social_auth_facebook`, or similar provider sub-modules appear configured
- Post-login redirect is `/user`

Mobile app has four social auth providers fully wired: Google Sign-In, Facebook (`flutter_facebook_auth`), Apple (iOS), Microsoft MSAL. Backend endpoint: `/api/v1/auth/social-register` (also legacy `lms-social-auth`).

Planned web requirements for full implementation:
- Google login (OAuth2 via `social_auth_google`)
- Facebook login (`social_auth_facebook`)
- Account linking (social account ↔ existing email account)
- Auto-pull avatar and name from social profile
- Social login buttons on login and register pages

### 7. Calendar integration / iCal export

**Status: target-state.**

No iCal endpoint exists. `lms_zoom` stores lesson schedule data in `field_schedule_time` on `lms_lesson`, which is available as a foundation.

Planned requirements:
- `/my-calendar.ics` iCal feed endpoint (auth required) with Zoom sessions and quiz deadlines
- "Add to Calendar" button on Zoom session join page
- Optional Google/Apple Calendar sync
- Email or in-app reminder 1 hour before live session

Implementation path: iCal feed in `lms_zoom` or `lms_base` module; no new entity required.

### 8. Instructor revenue dashboard

**Status: target-state.**

`lms_analytics` already has per-course revenue queries (`CourseReportManager::getCourseStats()` includes revenue per course), but the report is admin-only (`administer lms_course`) and shows all instructors' data to the admin.

Planned requirements:
- Instructor-scoped revenue view: earnings total, this month, per-course breakdown
- Student analytics: total students, new enrollments, completion rate (per instructor's courses)
- Payout history (if revenue sharing is enabled — currently not implemented)
- Date-range filter: 7d, 30d, 90d, custom
- CSV export

Implementation path: new controller/view in `lms_analytics` with `instructor` role scope filter on `field_instructor` (course owner). No new entity required.

### 9. Email drip campaigns

**Status: target-state.**

No `lms_email_campaign` module exists. `lms_notification` provides in-app channel only; email and push channels are stubs. SMTP is configured but disabled (`smtp.settings.yml`: `enabled: false`).

Planned requirements:
- Welcome series (3 emails for new users at registration → Day 1 → Day 3)
- Inactivity nudge (after 7/14/30 days without login)
- Course completion congratulation + next-course suggestion
- Membership pre-expiry reminder (7 days before)
- WYSIWYG email template editor (admin)
- Cron-based batch send; configurable send time
- One-click unsubscribe (CAN-SPAM compliance)

Implementation path: new module `lms_email_campaign` or extension of `lms_notification`. SMTP must be enabled and configured before any email drip can send.

## Implementation status summary

| Enhancement | Status | Module | Notes |
|---|---|---|---|
| Wishlist | as-built | `lms_wishlist` | Entity + 3 API endpoints + mobile integration |
| Course bundles | as-built | `lms_bundle` | Entity + checkout integration + homepage section |
| Lesson notes | as-built | `lms_note` | Entity + CRUD routes + mobile API |
| Abandoned cart recovery | target-state | `lms_order` | No cron/email logic; `field_status: cart` hook available |
| Course free preview | target-state | `lms_lesson` | No `field_is_preview` field; no access-check update |
| Social login (web) | partial stub | `social_auth` | Module installed; no providers configured |
| Calendar / iCal export | target-state | `lms_zoom` / `lms_base` | Schedule data available; no iCal endpoint |
| Instructor revenue dashboard | target-state | `lms_analytics` | Admin report exists; no instructor-scoped view |
| Email drip campaigns | target-state | (none) | SMTP disabled; no campaign module |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-004 | Bundle checkout triggers enrollment via order flow; payment gateway required |
| Feature | F-013 | Email drip (EG-02) and calendar integration (EG-04) are also listed as F-013 suggestions |
| Workflow | WF-001 | Wishlist → enrollment → learning is the canonical student journey |
| Workflow | WF-004 | Abandoned cart recovery operates within order management workflow |

## Open questions

- [ ] Abandoned cart recovery: should the third reminder include a programmatic coupon, or is this a manual process?
- [ ] Social login (web): which providers should be prioritized — Google only, or Google + Facebook simultaneously?
- [ ] SMTP must be configured and enabled before abandoned cart emails or drip campaigns can function. Is there a target SMTP provider?
- [ ] Instructor revenue dashboard: does "this month" mean calendar month or rolling 30 days?
- [ ] Calendar iCal: should the feed be public (shareable link with token) or always auth-gated?
