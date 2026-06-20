# D07 — Template to Controller Map

**Purpose:** All Twig templates in custom modules and themes → the controller method or hook that renders them.

**Last verified:** 2026-06-20  
**Source:** `web/modules/custom/*/templates/`, `web/themes/custom/lms_theme/templates/`

**Template count:** ~62 module-level templates + 16 homepage style variants (theme-level)

---

## Legend

| Column | Meaning |
|--------|---------|
| Template File | Path relative to module root (e.g. `lms_course/templates/lms-homepage.html.twig`) |
| Theme Hook | `hook_theme()` key; registered in the module's `.module` file |
| Rendered By | Controller method, Form class, or Block plugin that returns this theme as a render array |
| Route | The route name whose response includes this template |
| Notes | Architecture details, status flags |

Status tags: **as-built** / **stub** / **dead-code** / **bug** — see [README.md](README.md).

---

## lms_analytics — `/templates/`

Registered by: `lms_analytics.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-analytics-course-report.html.twig` | `lms_analytics_course_report` | `CourseReportController::build` | `lms_analytics.course_report` |
| `lms-analytics-user-report.html.twig` | `lms_analytics_user_report` | `UserReportController::build` | `lms_analytics.user_report` |
| `lms-analytics-quiz-report-list.html.twig` | `lms_analytics_quiz_report_list` | `QuizReportController::buildList` | `lms_analytics.quiz_report_list` |
| `lms-analytics-quiz-report-detail.html.twig` | `lms_analytics_quiz_report_detail` | `QuizReportController::buildDetail` | `lms_analytics.quiz_report_detail` |

---

## lms_blog — `lms_blog/templates/`

Registered by: `lms_blog.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-blog-list.html.twig` | `lms_blog_list` | `LmsBlogController::index` | `lms_blog.frontend.collection` (`/news`) |
| `lms-blog-single.html.twig` | `lms_blog_single` | `LmsBlogController::view` | `lms_blog.frontend.canonical` (`/news/{node}`) |

**Note:** Both templates display `node:blog` content (core node entities), not `lms_article`. The `lms_article` entity backs only the `/manage/blog` admin CRUD (entity forms, no custom template).

---

## lms_certificate — `lms_certificate/templates/`

Registered by: `lms_certificate.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route / Context |
|---------------|------------|-------------|-----------------|
| `certificate-pdf.html.twig` | `certificate_pdf` | `CertificateGenerator::generate()` (service) and `CertificateAdminController::regenerateCertificate` | PDF generation; also `CertificateAdminController::templatePreview` (inline preview) |
| `certificate-verification-result.html.twig` | `certificate_verification_result` | `Form\VerificationForm` (rebuild output) | `lms_certificate.verify` (`/certificate/verify`) |
| `lms-admin-certificates.html.twig` | `lms_admin_certificates` | `CertificateAdminController::adminDashboard` | `lms_certificate.admin_dashboard` (`/workspace/certificates`) |
| `lms-admin-certificate-templates.html.twig` | `lms_admin_certificate_templates` | `CertificateAdminController::adminTemplates` | `lms_certificate.admin_templates` (`/workspace/certificate-templates`) |

---

## lms_course — `lms_course/templates/`

Registered by: `lms_course.module hook_theme()` (30+ hooks)

### Public learner templates

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-homepage.html.twig` | `lms_homepage` | `CoursePublicController::homepage` | `lms_course.homepage` (`/homepage`) |
| `lms-catalog.html.twig` | `lms_catalog` | `CoursePublicController::catalog` | `lms_course.catalog` (`/courses`) |
| `lms-course-detail.html.twig` | `lms_course_detail` | `CoursePublicController::detail` | `lms_course.detail` (`/courses/{id}`) |
| `lms-course-card.html.twig` | `lms_course_card` | Sub-component used inside catalog, homepage, my-courses | (included by parent templates) |
| `lms-categories.html.twig` | `lms_categories` | `CoursePublicController::categories` | `lms_course.category` (`/categories`) |
| `lms-glossary.html.twig` | `lms_glossary` | `CoursePublicController::glossary` | `lms_course.glossary` (`/glossary`) |
| `lms-instructor-profile.html.twig` | `lms_instructor_profile` | `CoursePublicController::instructorProfile` | `lms_course.instructor_profile` (`/instructor/{user}`) |
| `lms-about.html.twig` | `lms_about` | `PageController::about` | `lms_course.about` (`/about`) |
| `lms-contact.html.twig` | `lms_contact` | `PageController::contact` | `lms_course.contact` (`/contact`) |
| `lms-pricing.html.twig` | `lms_pricing` | `PageController::pricing` | `lms_course.pricing` (`/pricing`) |
| `lms-profile.html.twig` | `lms_profile` | `UserPortalController::profile` *(lms_progress controller)* | `lms_course.profile` (`/profile`) |

### Workspace admin templates

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-admin-dashboard.html.twig` | `lms_admin_dashboard` | `AdminController::dashboard` | `lms_course.admin_dashboard` (`/workspace`) |
| `lms-admin-courses.html.twig` | `lms_admin_courses` | `AdminController::courseList` | `lms_course.admin_courses` (`/workspace/courses`) |
| `lms-admin-courses-table.html.twig` | `lms_admin_courses_table` | `AdminController::courseList` (AJAX/HTMX sub-render) | (AJAX partial; no standalone route) |
| `lms-admin-course-create.html.twig` | `lms_admin_course_create` | `AdminController::courseCreateWizard` | `lms_course.admin_course_create` (`/workspace/courses/create`) |
| `lms-admin-quizzes.html.twig` | `lms_admin_quizzes` | `AdminController::quizList` | `lms_course.admin_quizzes` (`/workspace/quizzes`) |
| `lms-admin-orders.html.twig` | `lms_admin_orders` | `AdminController::orderList` | `lms_course.admin_orders` (`/workspace/orders`) |
| `lms-admin-users.html.twig` | `lms_admin_users` | `AdminController::userList` | `lms_course.admin_users` (`/workspace/users`) |
| `lms-admin-users-table.html.twig` | `lms_admin_users_table` | `AdminController::userList` (AJAX sub-render) | (AJAX partial; no standalone route) |
| `lms-admin-user-form.html.twig` | `lms_admin_user_form` | `AdminController::userEditForm`, `AdminController::userCreateForm` | `lms_course.admin.user.edit`, `lms_course.admin.user.create` |
| `lms-admin-reports.html.twig` | `lms_admin_reports_v2` | `AdminController::reports` | `lms_course.admin_reports` (`/workspace/reports`) |
| `lms-admin-content.html.twig` | `lms_admin_content` | `WorkspaceContentController::contentList` | `lms_course.workspace_content.list` |
| `lms-admin-content-table.html.twig` | `lms_admin_content_table` | `WorkspaceContentController::contentList` (AJAX sub-render) | (AJAX partial) |
| `lms-admin-entity-form.html.twig` | `lms_admin_entity_form` | Multiple: `contentCreateForm`, `contentEditForm`, `faqCreateForm`, `faqEditForm`, `orderEditForm`, `quizCreateForm`, `quizEditForm`, `courseEditForm`; also used by `MembershipAdminController::pricingAddForm`, `CertificateAdminController::templateAddForm` | Various `/workspace/*` edit/add routes |
| `lms-admin-entity-view.html.twig` | `lms_admin_entity_view` | `WorkspaceContentController::contentView`, `WorkspaceContentController::faqView` | `lms_course.workspace_content.view`, `lms_course.workspace_faq.view` |
| `lms-admin-ratings.html.twig` | `lms_admin_ratings` | `RatingManagerController::list` *(lms_rating controller)* | `lms_rating.admin_list` (`/workspace/ratings`) |
| `lms-admin-sidebar.html.twig` | *(no dedicated hook — shared partial)* | Sub-include inside other admin templates | (included; no standalone route) |

---

## lms_forum — `lms_forum/templates/`

Registered by: `lms_forum.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-forum-index.html.twig` | `lms_forum_index` | `LmsForumController::index` | `lms_forum.index` (`/forum`) |
| `lms-forum-topic.html.twig` | `lms_forum_topic` | `LmsForumController::viewTopic` | `entity.lms_forum_topic.canonical` (`/forum/{id}`) overridden via RouteSubscriber |
| `lms-forum-new-topic-page.html.twig` | `lms_forum_new_topic_page` | `LmsForumController::addTopic` | `lms_forum.topic.add` (`/forum/new`) |
| `lms-forum-edit-reply-page.html.twig` | `lms_forum_edit_reply_page` | `LmsForumController::editReply` | `lms_forum.reply.edit` (`/forum/reply/{id}/edit`) |
| `lms-forum-topic-form.html.twig` | *(no dedicated hook; may be partial or twig override)* | Sub-include in new-topic-page template | (included; no standalone hook) |

---

## lms_membership — `lms_membership/templates/`

Registered by: `lms_membership.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-my-membership.html.twig` | `lms_my_membership` | `MembershipController::myMembership` | `lms_membership.my_membership` (`/my-membership`) |
| `lms-admin-pricing.html.twig` | `lms_admin_pricing` | `MembershipAdminController::pricingList` | `lms_membership.admin_pricing` (`/workspace/pricing`) |
| `lms-admin-user-memberships.html.twig` | `lms_admin_user_memberships` | `MembershipAdminController::userMembershipsList` | `lms_membership.admin_user_memberships` (`/workspace/user-memberships`) |

---

## lms_note — `lms_note/templates/`

Registered by: `lms_note.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-my-notes.html.twig` | `lms_my_notes` | `NoteController::myNotes` | `lms_note.my_notes` (`/my-notes`) |

---

## lms_notification — `lms_notification/templates/`

Registered by: `lms_notification.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route / Context |
|---------------|------------|-------------|-----------------|
| `lms-notifications.html.twig` | `lms_notifications` | `NotificationController::listNotifications` | `lms_notification.list` (`/notifications`) |
| `lms-notification-bell.html.twig` | `lms_notification_bell` | `NotificationBellBlock::build` (Block plugin) | Rendered in site header via block placement; no dedicated route |

---

## lms_order — `lms_order/templates/`

Registered by: `lms_order.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-checkout.html.twig` | `lms_checkout` | `CheckoutController::checkout`, `CheckoutController::checkoutMembership` | `lms_order.checkout`, `lms_order.checkout_membership` |
| `lms-bank-transfer.html.twig` | `lms_bank_transfer` | `CheckoutController::bankTransferInstruction` | `lms_order.bank_transfer` (`/checkout/bank-transfer/{order}`) |

---

## lms_progress — `lms_progress/templates/`

Registered by: `lms_progress.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-dashboard.html.twig` | `lms_dashboard` | `UserPortalController::dashboard` | `lms_progress.dashboard` (`/dashboard`) |
| `lms-my-courses.html.twig` | `lms_my_courses` | `UserPortalController::myCourses` | `lms_progress.my_courses` (`/my-courses`) |
| `lms-lesson-view.html.twig` | `lms_lesson_view` | `UserPortalController::lessonView` | `lms_progress.lesson_view` (`/learn/{course}/{lesson}`) |
| `lms-my-certificates.html.twig` | `lms_my_certificates` | `UserPortalController::myCertificates` | `lms_progress.certificates` (`/my-certificates`) |

---

## lms_quiz — `lms_quiz/templates/`

Registered by: `lms_quiz.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-quiz-take.html.twig` | `lms_quiz_take` | `QuizController::takeQuiz` | `lms_quiz.take` (`/quiz/{id}/take`) |
| `lms-quiz-result.html.twig` | `lms_quiz_result` | `QuizController::viewResult` | `lms_quiz.result` (`/quiz-result/{result}`) |
| `lms-my-quiz-results.html.twig` | `lms_my_quiz_results` | `QuizController::myResults` | `lms_quiz.my_results` (`/my-quiz-results`) |

**Note:** `lms-quiz-result.html.twig` hardcodes `score >= 70` as pass threshold fallback, independent of `quiz.field_threshold`. This is a bug.

---

## lms_wishlist — `lms_wishlist/templates/`

Registered by: `lms_wishlist.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `lms-my-wishlist.html.twig` | `lms_my_wishlist` | `WishlistController::myWishlist` | `lms_wishlist.my_wishlist` (`/my-wishlist`) |

---

## lms_zoom — `lms_zoom/templates/`

Registered by: `lms_zoom.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route / Context |
|---------------|------------|-------------|-----------------|
| `lms-zoom-web-player.html.twig` | `lms_zoom_web_player` | `ZoomController::joinMeeting` | `lms_zoom.join_meeting` (`/zoom/join/{meeting_id}`) |
| `html--lms-zoom-web-player.html.twig` | `html__lms_zoom_web_player` | Theme suggestion via `hook_theme_suggestions_html_alter` | Injected when route = `lms_zoom.join_meeting`; strips full HTML chrome |
| `page--lms-zoom-web-player.html.twig` | `page__lms_zoom_web_player` | Theme suggestion via `hook_theme_suggestions_page_alter` | Same route; minimal page wrapper for full-screen WebSDK |
| `lms-zoom-join-block.html.twig` | `lms_zoom_join_block` | `ZoomJoinBlock::build` (Block plugin) | Rendered in lesson view sidebar via block placement |

---

## open_switcher — `open_switcher/templates/`

Registered by: `open_switcher.module hook_theme()`

| Template File | Theme Hook | Rendered By | Route |
|---------------|------------|-------------|-------|
| `open-switcher-page.html.twig` | `open_switcher_page` | `SwitchFrontendController::page` | `open_switcher.frontend` (`/app/switch-user`) |

---

## lms_theme (custom theme) — Homepage Style Variants

**Theme path:** `web/themes/custom/lms_theme/templates/home/styles/`

These are style partials conditionally included by `lms-homepage.html.twig` based on the `homepage_style` variable passed by `CoursePublicController::homepage()`. The `lms_base` module stores the current style setting; `CoursePublicController::homepage` reads it via `getHomepageSettings()`.

| Template File | Style Name | Notes |
|---------------|------------|-------|
| `lms-homepage.html.twig` *(theme root)* | Fallback / base | Theme-level override of `lms_homepage` hook; includes correct style partial |
| `home/styles/style-1-glass.html.twig` | glassmorphism | Default style; dark glassmorphism hero |
| `home/styles/style-2-minimal.html.twig` | minimalist | Clean minimal layout |
| `home/styles/style-4-corporate.html.twig` | corporate | Enterprise/corporate layout |
| `home/styles/style-academy.html.twig` | academy | Education-focused layout |
| `home/styles/style-community.html.twig` | community | Social/community layout |
| `home/styles/style-creative.html.twig` | creative | Design/creative layout |
| `home/styles/style-editorial.html.twig` | editorial | Content/editorial layout |
| `home/styles/style-government.html.twig` | government | Gov/public-sector layout |
| `home/styles/style-gradient.html.twig` | gradient | Bold gradient layout |
| `home/styles/style-luxury.html.twig` | luxury | Premium/luxury layout |
| `home/styles/style-medical.html.twig` | medical | Healthcare layout |
| `home/styles/style-nature.html.twig` | nature | Earth-tones/nature layout |
| `home/styles/style-neon.html.twig` | neon | Neon/dark layout |
| `home/styles/style-playful.html.twig` | playful | Fun/colorful layout |
| `home/styles/style-retro.html.twig` | retro | Retro/vintage layout |

**Total style variants: 15** (plus 1 base). The `homepage_style` default is `'glassmorphism'`.

---

## lms_theme / lms_classic — Layout & System Templates

**Theme path:** `web/themes/custom/lms_theme/templates/` and `web/themes/custom/lms_classic/templates/`

These are standard Drupal theme template overrides, not tied to a specific custom controller.

| Template | Usage |
|----------|-------|
| `layout/html.html.twig` | Full HTML wrapper (outer `<html>` tag) |
| `page/page.html.twig` | Generic page layout (header, main, footer regions) |
| `page/page--user--login.html.twig` | Login page layout override |
| `page/page--user--register.html.twig` | Register page layout override |
| `page/page--user--password.html.twig` | Password reset page layout override |
| `user/user-login-form.html.twig` | Login form widget override |
| `user/user-register-form.html.twig` | Registration form widget override |
| `form/lms-course-manager-edit-form.html.twig` | Course entity edit form override in theme context |
| `navigation/breadcrumb.html.twig` | Breadcrumb block override |
| `navigation/pager.html.twig` | Pager override |

---

## Modules with NO templates

The following modules have no `templates/` directory — they are pure entity/service/API modules or their output is JSON:  
lms_badge, lms_base, lms_bundle, lms_chapter, lms_content_tools, lms_faq, lms_lesson, lms_mobile_api, lms_payment, lms_paypal, lms_question, lms_rating *(no .module file, no hook_theme)*, lms_stripe

**lms_rating note:** `RatingManagerController::list()` uses `#theme: lms_admin_ratings` — this hook is registered in `lms_course.module hook_theme()`, not in lms_rating itself. The template file lives in `lms_course/templates/lms-admin-ratings.html.twig`.

---

## Cross-references

- Controller source files: [per-module-files.md](per-module-files.md)
- Route → controller: [route-to-controller.md](route-to-controller.md)
- Page/component designs: [C11-pages-components/](../../C-Design/C11-pages-components/)
- Skin/homepage style system: [C09-skins.md](../../C-Design/C09-skins.md)
