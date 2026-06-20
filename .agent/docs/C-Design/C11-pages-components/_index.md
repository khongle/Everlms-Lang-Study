---
page: _index
mode: as-built
owner_module: lms_course
route_group: all
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Pages & Components: Catalogue

> Master index of every page spec across all route groups. Cross-links to per-page spec files. Global chrome (header/nav/footer) is documented once in [`_common.md`](_common.md).
> Route counts verified against facts base: 137 routes total (19 Public + 21 Learn/App + 47 Workspace/Manage + 17 Drupal Admin + 31 API + 2 Utility).

---

## Route Groups

| Group | URL prefix | Routes | Auth | Primary audience |
|---|---|---|---|---|
| [Public](#a--public-group) | `/*` (no prefix) | 19 | No | Anonymous + authenticated |
| [Learn / App](#b--learnapp-group) | `/dashboard`, `/my-*`, `/learn/*`, `/quiz/*`, `/notifications`, `/profile`, `/zoom/*`, `/forum/*` | 21 | `_user_is_logged_in` | Authenticated learner |
| [Workspace / Manage](#c--workspacemanage-group) | `/workspace/*`, `/manage/*` | 47 | `administer lms_course` or entity access | Instructor, admin |
| [Drupal /admin](#d--drupal-admin-group) | `/admin/*` | 17 | `administer site configuration` / `administer lms_*` | Admin, super-admin |
| [API](#e--api-routes) | `/api/lms/*`, `/api/courses/*`, `/api/v1/*` | 31 | Varies (see C06-apis) | Web AJAX + mobile Flutter app |
| [Utility](#f--utility-routes) | `/app/*` | 2 | `switch users` | Admin impersonation |

API routes are documented in [`../C06-apis/`](../C06-apis/) — no per-page C11 spec.
Drupal `/admin/*` routes use Claro admin theme (stock vendor UI); most have no custom C11 spec except where custom controllers exist.

---

## A — Public Group

*Access: `access content` or `_access: TRUE`. Anonymous-cacheable unless noted.*

| # | URL | Route name | Title | Owner module | C11 spec | Mode |
|---|---|---|---|---|---|---|
| 1 | `/homepage` | `lms_course.homepage` | EverLMS — Transform Your Future | `lms_course` | (planned, C11 not yet written) | — |
| 2 | `/courses` | `lms_course.catalog` | Browse Courses | `lms_course` | (planned) | — |
| 3 | `/courses/{lms_course}` | `lms_course.detail` | Course detail | `lms_course` | (planned) | — |
| 4 | `/courses/{lms_course}/enroll-free` | `lms_course.enroll_free` | Free enroll (redirect) | `lms_course` | (planned) | — |
| 5 | `/categories` | `lms_course.category` | Categories | `lms_course` | (planned) | — |
| 6 | `/categories/{taxonomy_term}` | `lms_course.category_detail` | Category detail | `lms_course` | (planned) | — |
| 7 | `/glossary` | `lms_course.glossary` | Glossary | `lms_course` | (planned) | — |
| 8 | `/about` | `lms_course.about` | About Us | `lms_course` | (planned) | — |
| 9 | `/contact` | `lms_course.contact` | Contact Us | `lms_course` | (planned) | — |
| 10 | `/pricing` | `lms_course.pricing` | Pricing | `lms_course` | (planned) | — |
| 11 | `/instructor/{user}` | `lms_course.instructor_profile` | Instructor Profile | `lms_course` | (planned) | — |
| 12 | `/news` | `lms_blog.frontend.collection` | Latest News & Insights | `lms_blog` | (planned) | — |
| 13 | `/news/{node}` | `lms_blog.frontend.canonical` | Article detail | `lms_blog` | (planned) | — |
| 14 | `/certificate/verify` | `lms_certificate.verify` | Verify Certificate | `lms_certificate` | (planned) | — |
| 15 | `/checkout/{course_id}` | `lms_order.checkout` | Checkout | `lms_order` | (planned) | — |
| 16 | `/checkout/membership/{plan_id}` | `lms_order.checkout_membership` | Checkout Membership | `lms_order` | (planned) | — |
| 17 | `/checkout/bank-transfer/{lms_order}` | `lms_order.bank_transfer` | Payment Instructions | `lms_order` | (planned) | — |
| 18 | `/forum` | `lms_forum.index` | Community | `lms_forum` | (planned) | — |
| 19 | `/forum/{lms_forum_topic}` | `entity.lms_forum_topic.canonical` (overridden) | Forum topic | `lms_forum` | (planned) | — |

**Notes:**
- `/homepage` style is switchable: 15 skins from `lms_theme/templates/home/styles/`. See [`../C09-skins.md`](../C09-skins.md).
- `/checkout/*` rendered in `lms_theme` (not admin theme); Stripe PaymentIntent created at form BUILD time — known security risk.
- `/forum/{lms_forum_topic}` canonical controller overridden by `RouteSubscriber` to `LmsForumController::viewTopic`.
- `lms_blog` is dual-track: public reads core `node:blog`; `/manage/blog` admin CRUD uses `lms_article` entity.

---

## B — Learn / App Group

*Access: `_user_is_logged_in: TRUE`. Per-user cache unless noted.*

| # | URL | Route name | Title | Owner module | C11 spec | Mode |
|---|---|---|---|---|---|---|
| 1 | `/dashboard` | `lms_progress.dashboard` | Dashboard | `lms_progress` | (planned) | — |
| 2 | `/my-courses` | `lms_progress.my_courses` | My Courses | `lms_progress` | (planned) | — |
| 3 | `/learn/{lms_course}/{lms_lesson}` | `lms_progress.lesson_view` | Lesson View | `lms_progress` | (planned) | — |
| 4 | `/my-certificates` | `lms_progress.certificates` | My Certificates | `lms_progress` | (planned) | — |
| 5 | `/profile` | `lms_course.profile` | My Profile | `lms_progress` | (planned) | — |
| 6 | `/my-membership` | `lms_membership.my_membership` | My Membership | `lms_membership` | (planned) | — |
| 7 | `/my-membership/{lms_user_membership}/cancel` | `lms_membership.cancel_membership` | Cancel membership (redirect) | `lms_membership` | (planned) | — |
| 8 | `/my-notes` | `lms_note.my_notes` | My Notes | `lms_note` | (planned) | — |
| 9 | `/my-wishlist` | `lms_wishlist.my_wishlist` | My Wishlist | `lms_wishlist` | (planned) | — |
| 10 | `/my-quiz-results` | `lms_quiz.my_results` | My Quiz Results | `lms_quiz` | (planned) | — |
| 11 | `/notifications` | `lms_notification.list` | Notifications | `lms_notification` | (planned) | — |
| 12 | `/quiz/{lms_quiz}/take` | `lms_quiz.take` | Take Quiz | `lms_quiz` | (planned) | — |
| 13 | `/quiz/{lms_quiz}/submit` | `lms_quiz.submit` | Submit Quiz (POST) | `lms_quiz` | (planned) | — |
| 14 | `/quiz-result/{lms_quiz_result}` | `lms_quiz.result` | Quiz Result | `lms_quiz` | (planned) | — |
| 15 | `/zoom/join/{meeting_id}` | `lms_zoom.join_meeting` | Joining Class... | `lms_zoom` | (planned) | — |
| 16 | `/forum/new` | `lms_forum.topic.add` | New Topic | `lms_forum` | (planned) | — |
| 17 | `/forum/{lms_forum_topic}/reply` | `lms_forum.reply.add` | Reply (POST) | `lms_forum` | (planned) | — |
| 18 | `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | `lms_forum.reply.best_answer` | Mark Best Answer | `lms_forum` | (planned) | — |
| 19 | `/forum/reply/{lms_forum_reply}/edit` | `lms_forum.reply.edit` | Edit Reply | `lms_forum` | (planned) | — |
| 20 | `/forum/reply/{lms_forum_reply}/delete` | `entity.lms_forum_reply.delete_form` | Delete Reply | `lms_forum` | (planned) | — |
| 21 | `/manage/settings/notifications` | `lms_notification.preferences` | Notification Preferences | `lms_notification` | [manage/settings-integrations.md](manage/settings-integrations.md) | as-built |

**Notes:**
- `/zoom/join/{meeting_id}` uses theme suggestions to render a full-screen no-chrome page (`html--lms-zoom-web-player`, `page--lms-zoom-web-player`).
- `/manage/settings/notifications` is gated on `logged_in` (not admin permission) — conceptually learner-group despite `/manage/` prefix.
- `lms_forum` `ForumController::viewTopic` has `throw new \Exception("HARD DEBUG!")` — dead code but dangerous. `LmsForumController::viewTopic` has left-in debug messenger call.

---

## C — Workspace / Manage Group

*Access: `administer lms_course` or entity-level access. All `_admin_route: TRUE`.*

### Workspace Hub (lms_course — AdminController)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 1 | `/workspace` | `lms_course.admin_dashboard` | Admin Dashboard | (planned) | — |
| 2 | `/workspace/courses` | `lms_course.admin_courses` | Manage Courses | (planned) | — |
| 3 | `/workspace/courses/create` | `lms_course.admin_course_create` | Create New Course | (planned) | — |
| 4 | `/workspace/courses/{lms_course}/edit` | `lms_course.admin.course.edit` | Edit Course | (planned) | — |
| 5 | `/workspace/quizzes` | `lms_course.admin_quizzes` | Manage Quizzes | (planned) | — |
| 6 | `/workspace/quizzes/create` | `lms_course.admin_quiz.create` | Create Quiz | (planned) | — |
| 7 | `/workspace/quizzes/{lms_quiz}/edit` | `lms_course.admin_quiz.edit` | Edit Quiz | (planned) | — |
| 8 | `/workspace/orders` | `lms_course.admin_orders` | Manage Orders | (planned) | — |
| 9 | `/workspace/orders/{lms_order}/edit` | `lms_course.admin_order.edit` | Edit Order | (planned) | — |
| 10 | `/workspace/users` | `lms_course.admin_users` | Manage Users | (planned) | — |
| 11 | `/workspace/users/create` | `lms_course.admin.user.create` | Create User | (planned) | — |
| 12 | `/workspace/users/{user}/admin-edit` | `lms_course.admin.user.edit` | Edit User | (planned) | — |
| 13 | `/workspace/users/bulk` | `lms_course.admin.users.bulk` | Bulk user action (POST) | (planned) | — |
| 14 | `/workspace/reports` | `lms_course.admin_reports` | Reports & Analytics | (planned) | — |
| 15 | `/workspace/chapters/{lms_chapter}/admin-edit` | `lms_course.admin.chapter.edit` | Edit Chapter | (planned) | — |
| 16 | `/workspace/lessons/{lms_lesson}/admin-edit` | `lms_course.admin.lesson.edit` | Edit Lesson | (planned) | — |

### Workspace Content (lms_course — WorkspaceContentController)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 17 | `/workspace/content/{content_type}` | `lms_course.workspace_content.list` | Manage Content | (planned) | — |
| 18 | `/workspace/content/{content_type}/add` | `lms_course.workspace_content.add` | Create Content | (planned) | — |
| 19 | `/workspace/content/{content_type}/{node}/edit` | `lms_course.workspace_content.edit` | Edit Content | (planned) | — |
| 20 | `/workspace/content/{content_type}/{node}/view` | `lms_course.workspace_content.view` | View Content | (planned) | — |
| 21 | `/workspace/content/bulk` | `lms_course.workspace_content.bulk` | Bulk content action (POST) | (planned) | — |
| 22 | `/workspace/content/faqs/add` | `lms_course.workspace_faq.add` | Create FAQ | (planned) | — |
| 23 | `/workspace/content/faqs/{lms_faq}/edit` | `lms_course.workspace_faq.edit` | Edit FAQ | (planned) | — |
| 24 | `/workspace/content/faqs/{lms_faq}/view` | `lms_course.workspace_faq.view` | View FAQ | (planned) | — |

### Certificates (lms_certificate)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 25 | `/workspace/certificates` | `lms_certificate.admin_dashboard` | Certificate Management | (planned) | — |
| 26 | `/workspace/certificates/{lms_user_certificate}/regenerate` | `lms_certificate.regenerate` | Regenerate Certificate PDF | (planned) | — |
| 27 | `/workspace/certificate-templates` | `lms_certificate.admin_templates` | Certificate Templates | (planned) | — |
| 28 | `/workspace/certificate-templates/add` | `lms_certificate.admin_template.add` | Add Certificate Template | (planned) | — |
| 29 | `/workspace/certificate-templates/{lms_certificate_template}/edit` | `lms_certificate.admin_template.edit` | Edit Certificate Template | (planned) | — |
| 30 | `/workspace/certificate-templates/{lms_certificate_template}/preview` | `lms_certificate.admin_template.preview` | Preview Certificate Template | (planned) | — |

### Membership (lms_membership)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 31 | `/workspace/pricing` | `lms_membership.admin_pricing` | Membership Pricing | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |
| 32 | `/workspace/pricing/add` | `lms_membership.admin_pricing.add` | Add Pricing Plan | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |
| 33 | `/workspace/pricing/{lms_membership_plan}/edit` | `lms_membership.admin_pricing.edit` | Edit Pricing Plan | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |
| 34 | `/workspace/pricing/{lms_membership_plan}/toggle` | `lms_membership.admin_pricing.toggle` | Toggle Plan Status | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |
| 35 | `/workspace/user-memberships` | `lms_membership.admin_user_memberships` | User Memberships | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |
| 36 | `/workspace/user-memberships/add` | `lms_membership.admin_user_memberships.add` | Add User Membership | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |
| 37 | `/workspace/user-memberships/{lms_user_membership}/edit` | `lms_membership.admin_user_memberships.edit` | Edit User Membership | [manage/manage-memberships.md](manage/manage-memberships.md) | as-built |

### Ratings (lms_rating)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 38 | `/workspace/ratings` | `lms_rating.admin_list` | Manage Reviews | (planned) | — |
| 39 | `/workspace/ratings/{lms_rating}/{action}` | `lms_rating.admin_action` | Execute rating action | (planned) | — |

### Content Tools (lms_content_tools)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 40 | `/workspace/content-tools` | `lms_content_tools.dashboard` | Content Tools | (planned) | — |
| 41 | `/workspace/content-tools/clean` | `lms_content_tools.clean` | Clean Content | (planned) | — |
| 42 | `/workspace/content-tools/seed-input` | `lms_content_tools.seed_input` | Seed Input | (planned) | — |

### Blog Admin (lms_blog)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 43 | `/manage/blog` | `entity.lms_article.collection` | Blog Articles | (planned) | — |
| 44 | `/manage/blog/add` | `entity.lms_article.add_form` | Add Article | (planned) | — |
| 45 | `/manage/blog/{lms_article}/edit` | `entity.lms_article.edit_form` | Edit Article | (planned) | — |
| 46 | `/manage/blog/{lms_article}/delete` | `entity.lms_article.delete_form` | Delete Article | (planned) | — |

### AI Course Builder (lms_ai)

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 47 | `/manage/courses/ai-builder` | `lms_ai.course_builder_form` | Build Course with AI | [manage/ai-course-builder.md](manage/ai-course-builder.md) | as-built |

**Workspace total: 47 routes**

---

## D — Drupal /admin Group

*Access: `administer site configuration` or specific `administer lms_*` permissions. Uses Claro admin theme.*

| # | URL | Route name | Title | C11 spec | Mode |
|---|---|---|---|---|---|
| 1 | `/admin/config/lms/ai` | `lms_ai.settings` | AI Platform Settings | [manage/settings-integrations.md](manage/settings-integrations.md) | as-built |
| 2 | `/admin/everlms-ai-engine` | `lms_ai.engine_users_form` | EverLMS AI Engine | (planned) | — |
| 3 | `/admin/everlms-ai-engine/preview-users-json` | `lms_ai.preview_users_json` | Preview AI JSON | (planned) | — |
| 4 | `/admin/lms/reports/courses` | `lms_analytics.course_report` | Course Analytics & Report | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 5 | `/admin/lms/reports/courses/export` | `lms_analytics.course_report_export` | CSV export | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 6 | `/admin/lms/reports/users` | `lms_analytics.user_report` | User Analytics & Report | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 7 | `/admin/lms/reports/users/export` | `lms_analytics.user_report_export` | CSV export | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 8 | `/admin/lms/reports/quizzes` | `lms_analytics.quiz_report_list` | Quiz Analytics & Report | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 9 | `/admin/lms/reports/quizzes/{quiz_id}` | `lms_analytics.quiz_report_detail` | Quiz Item Analysis | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 10 | `/admin/lms/reports/quizzes/{quiz_id}/export` | `lms_analytics.quiz_report_export` | CSV export | [manage/analytics-dashboard.md](manage/analytics-dashboard.md) | as-built |
| 11 | `/admin/lms/certificates/{lms_user_certificate}/revoke` | `lms_certificate.revoke` | Revoke Certificate | (planned) | — |
| 12 | `/admin/lms/notifications/broadcast` | `lms_notification.broadcast` | Broadcast Announcement | [manage/manage-notifications-send.md](manage/manage-notifications-send.md) | as-built |
| 13 | `/admin/config/everlms/payment` | `lms_payment.settings` | Payment Gateway Settings | [manage/settings-integrations.md](manage/settings-integrations.md) | as-built |
| 14 | `/admin/config/lms/zoom` | `lms_zoom.settings` | Zoom Configuration | [manage/settings-integrations.md](manage/settings-integrations.md) | as-built |
| 15 | `/admin/switch-user` | `open_switcher.switch_form` | Switch User | (planned) | — |
| 16 | `/admin/switch-user/{user}` | `open_switcher.switch_to` | Switch To (redirect) | (planned) | — |
| 17 | `/admin/switch-back` | `open_switcher.switch_back` | Switch Back (redirect) | (planned) | — |

---

## E — API Routes

*Documented in [`../C06-apis/`](../C06-apis/) — no per-page C11 specs.*

| Prefix | Count | Owner | Auth |
|---|---|---|---|
| `/api/courses/*`, `/api/lms/*` (web AJAX) | 9 | lms_course, lms_progress, lms_note, lms_notification, lms_wishlist, lms_zoom | Varies (see C06) |
| `/api/v1/*` (mobile REST) | 22 | lms_mobile_api | Custom bearer token (keyvalue.expirable + OTP); not OAuth2/JWT |

---

## F — Utility Routes

| # | URL | Route name | Purpose | Owner | C11 spec |
|---|---|---|---|---|---|
| 1 | `/app/switch-user` | `open_switcher.frontend` | Frontend user impersonation picker | `open_switcher` | (planned) |
| 2 | `/app/switch-user/{user}` | `open_switcher.frontend_switch` | Impersonate target user (redirect) | `open_switcher` | (planned) |

---

## C11 Spec Coverage Summary

| Group | Total routes | C11 specs written | Coverage |
|---|---|---|---|
| Public | 19 | 0 | 0% |
| Learn / App | 21 | 1 (settings/notifications) | 5% |
| Workspace / Manage | 47 | 7 (ai-builder, memberships ×6) | 15% |
| Drupal /admin | 17 | 10 (analytics ×6, settings ×3, broadcast) | 59% |
| API | 31 | 0 (see C06-apis) | N/A |
| Utility | 2 | 0 | 0% |
| **Total** | **137** | **18** | **13%** |

> Pages marked `(planned)` need C11 specs written in future sessions. Priority order (by risk/business impact):
> 1. `/workspace` hub + course CRUD (lms_course AdminController — largest surface)
> 2. `/learn/{course}/{lesson}` (lesson view — core learning experience)
> 3. `/checkout/*` (payment surface — security-critical)
> 4. `/quiz/{id}/take` + result (assessment surface)
> 5. `/admin/everlms-ai-engine` (AiEngineUserForm — 2361-line complex wizard)

---

## Known Issues Affecting Pages

| Issue | Page(s) affected | Severity |
|---|---|---|
| `lms_forum` `ForumController::viewTopic` has `throw new \Exception("HARD DEBUG!")` — dead code but dangerous | `/forum/{id}` | HIGH — would 500 if routed |
| `lms_rating` missing 3 routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) | `/workspace/ratings` | HIGH — RouteNotFoundException |
| `lms_zoom` `generateSignature()` references wrong service ID | `/zoom/join/{meeting_id}` | MEDIUM — runtime error |
| Stripe PaymentIntent created at form BUILD (one per page load) | `/checkout/*` | MEDIUM — waste + potential security |
| No webhook endpoints for any payment gateway | `/checkout/*` | HIGH — async payments unconfirmed |
| `lms_notification` email + push channels are stubs | `/admin/lms/notifications/broadcast`, `/manage/settings/notifications` | MEDIUM — email/push not delivered |
| `lms_paypal` module is dead code (wrong interface, no annotation) | `/checkout/*` | LOW — silent failure |
| `lms_badge` has zero award logic | N/A (no UI page) | LOW — entity only |
