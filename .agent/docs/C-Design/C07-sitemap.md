# C07 — Sitemap

> Complete page/URL map for EverLMS, grounded in the 137 custom routes defined across 28 modules.
> Organized by surface: Public (19), Learn/App (21), Workspace/Manage (47), Drupal /admin (17), API (31), Utility (2).
> **documentation-mode: as-built** (all routes confirmed in routing.yml files as of 2026-06-20).

---

## Route Group Overview

| Route Group | Prefix(es) | Routes | Auth | Primary Roles |
|-------------|-----------|--------|------|---------------|
| [Public](#public-group) | `/*` (no prefix) | 19 | No | anonymous, authenticated |
| [Learn / App](#learnapp-group) | `/dashboard`, `/my-*`, `/learn/*`, `/quiz/*`, `/notifications`, `/profile`, `/zoom/*`, `/forum/*` | 21 | Yes — `_user_is_logged_in` | authenticated learner |
| [Workspace / Manage](#workspacemanage-group) | `/workspace/*`, `/manage/*` | 47 | Yes — `administer lms_course` or entity access | instructor, admin |
| [Drupal /admin](#drupal-admin-group) | `/admin/*` | 17 | Yes — `administer site configuration` / `administer lms_*` | admin, super-admin |
| [API](#api-group) | `/api/lms/*`, `/api/courses/*`, `/api/v1/*` | 31 | Mixed (token or logged_in at controller) | mobile clients, in-page AJAX |
| [Utility](#utility-group) | `/app/*` | 2 | Yes — `switch users` permission | admin (impersonation) |

**Total custom routes: 137**

---

## Public Group

> Anonymous-accessible pages. Rendered by `lms_theme` (Bootstrap 5.3.3, glassmorphism default). Page-cached, SEO-targeted.

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/homepage` | `lms_course.homepage` | `CoursePublicController::homepage` | EverLMS — Transform Your Future | `access content` | GET |
| `/courses` | `lms_course.catalog` | `CoursePublicController::catalog` | Browse Courses | `access content` | GET |
| `/courses/{lms_course}` | `lms_course.detail` | `CoursePublicController::detail` | _(entity title)_ | `access content` | GET |
| `/courses/{lms_course}/enroll-free` | `lms_course.enroll_free` | `CourseEnrollmentController::enrollFree` | — | `access content` | GET |
| `/categories` | `lms_course.category` | `CoursePublicController::categories` | Categories | `access content` | GET |
| `/categories/{taxonomy_term}` | `lms_course.category_detail` | `CoursePublicController::categoryDetail` | _(term name)_ | `access content` | GET |
| `/glossary` | `lms_course.glossary` | `CoursePublicController::glossary` | Glossary | `access content` | GET |
| `/about` | `lms_course.about` | `PageController::about` | About Us | `access content` | GET |
| `/contact` | `lms_course.contact` | `PageController::contact` | Contact Us | `access content` | GET |
| `/pricing` | `lms_course.pricing` | `PageController::pricing` | Pricing | `access content` | GET |
| `/instructor/{user}` | `lms_course.instructor_profile` | `CoursePublicController::instructorProfile` | Instructor Profile | `access content` | GET |
| `/news` | `lms_blog.frontend.collection` | `LmsBlogController::index` | Latest News & Insights | `_access: TRUE` | GET |
| `/news/{node}` | `lms_blog.frontend.canonical` | `LmsBlogController::view` | _(title_callback)_ | `_access: TRUE` | GET |
| `/certificate/verify` | `lms_certificate.verify` | `Form\VerificationForm` | Verify Certificate | `_access: TRUE` | GET/POST |
| `/checkout/{course_id}` | `lms_order.checkout` | `CheckoutController::checkout` | Checkout | `_access: TRUE` | GET/POST |
| `/checkout/membership/{plan_id}` | `lms_order.checkout_membership` | `CheckoutController::checkoutMembership` | Checkout Membership | `_access: TRUE` | GET/POST |
| `/checkout/bank-transfer/{lms_order}` | `lms_order.bank_transfer` | `CheckoutController::bankTransferInstruction` | Payment Instructions | `access content` | GET |
| `/forum` | `lms_forum.index` | `LmsForumController::index` | Community | `access content` | GET |
| `/forum/{lms_forum_topic}` | `entity.lms_forum_topic.canonical` _(overridden by RouteSubscriber)_ | `LmsForumController::viewTopic` | _(topic title)_ | `access content` | GET |

> **Note on `/news`:** `lms_blog` is dual-track. The public `/news` and `/news/{node}` routes read core `node:blog` entities. The `lms_article` entity backs `/manage/blog` admin CRUD only. See [C05-modules/lms_blog.md](C05-modules/lms_blog.md).

> **Note on `/forum/{lms_forum_topic}`:** The entity canonical URL is overridden in `lms_forum/src/Routing/RouteSubscriber.php` to inject `LmsForumController::viewTopic` as the controller. The earlier `ForumController::viewTopic` method contains a live bug (`"HARD DEBUG!"` exception) — it is unreachable via routing but present in the codebase. See [E02-issues/](../E02-issues/).

> **Note on `/homepage`:** The homepage style is switchable. `CoursePublicController::homepage` reads the `homepage_style` setting (stored in `lms_base`) and passes it to `lms-homepage.html.twig`, which includes one of 15 style-variant partials from `lms_theme/templates/home/styles/`. See [C09-skins.md](C09-skins.md).

### Navigation Flow — Public

```
[Homepage /homepage]
    |
    +-- [Course Catalog /courses]
    |       +-- [Course Detail /courses/{id}]
    |               +-- [Enroll Free /courses/{id}/enroll-free]
    |               +-- [Checkout /checkout/{id}]
    |                       +-- [Bank Transfer /checkout/bank-transfer/{order}]
    |
    +-- [Categories /categories]
    |       +-- [Category Detail /categories/{term}]
    |
    +-- [News /news]
    |       +-- [News Article /news/{node}]
    |
    +-- [Instructor Profile /instructor/{user}]
    +-- [Pricing /pricing]
    |       +-- [Checkout Membership /checkout/membership/{plan_id}]
    |
    +-- [Forum /forum]
    |       +-- [Topic /forum/{id}]
    |
    +-- [Glossary /glossary]
    +-- [About /about]
    +-- [Contact /contact]
    +-- [Verify Certificate /certificate/verify]
```

### Primary Navigation (Public Header)

Derived from Drupal menu configuration and page structure:

| Nav Item | URL | Notes |
|----------|-----|-------|
| Home | `/homepage` | Marketing homepage |
| Courses | `/courses` | Course catalog |
| Categories | `/categories` | Taxonomy browse |
| News | `/news` | Blog/news listing |
| Pricing | `/pricing` | Membership pricing |
| About | `/about` | Static page |
| Contact | `/contact` | Static page |
| Login | `/user/login` | Drupal core route |
| Register | `/user/register` | Drupal core route |

---

## Learn/App Group

> Authenticated learner experience. Access gate: `_user_is_logged_in: 'TRUE'`. Delivered via `lms_theme` with learner-specific blocks.

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/dashboard` | `lms_progress.dashboard` | `UserPortalController::dashboard` | Dashboard | logged_in | GET |
| `/my-courses` | `lms_progress.my_courses` | `UserPortalController::myCourses` | My Courses | logged_in | GET |
| `/learn/{lms_course}/{lms_lesson}` | `lms_progress.lesson_view` | `UserPortalController::lessonView` | _(lesson title)_ | logged_in | GET |
| `/my-certificates` | `lms_progress.certificates` | `UserPortalController::myCertificates` | My Certificates | logged_in | GET |
| `/profile` | `lms_course.profile` | `UserPortalController::profile` | My Profile | logged_in | GET |
| `/my-membership` | `lms_membership.my_membership` | `MembershipController::myMembership` | My Membership | logged_in | GET |
| `/my-membership/{lms_user_membership}/cancel` | `lms_membership.cancel_membership` | `MembershipController::cancelMembership` | — | logged_in | GET |
| `/my-notes` | `lms_note.my_notes` | `NoteController::myNotes` | My Notes | logged_in | GET |
| `/my-wishlist` | `lms_wishlist.my_wishlist` | `WishlistController::myWishlist` | My Wishlist | logged_in | GET |
| `/my-quiz-results` | `lms_quiz.my_results` | `QuizController::myResults` | My Quiz Results | logged_in | GET |
| `/notifications` | `lms_notification.list` | `NotificationController::listNotifications` | Notifications | logged_in | GET |
| `/quiz/{lms_quiz}/take` | `lms_quiz.take` | `QuizController::takeQuiz` | Take Quiz | logged_in | GET |
| `/quiz/{lms_quiz}/submit` | `lms_quiz.submit` | `QuizController::submitQuiz` | Submit Quiz | logged_in | POST |
| `/quiz-result/{lms_quiz_result}` | `lms_quiz.result` | `QuizController::viewResult` | Quiz Result | logged_in | GET |
| `/zoom/join/{meeting_id}` | `lms_zoom.join_meeting` | `ZoomController::joinMeeting` | Joining Class... | logged_in | GET |
| `/forum/new` | `lms_forum.topic.add` | `LmsForumController::addTopic` | New Topic | `entity_create_access:lms_forum_topic` | GET |
| `/forum/{lms_forum_topic}/reply` | `lms_forum.reply.add` | `LmsForumController::reply` | — | `entity_create_access:lms_forum_reply` | POST |
| `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | `lms_forum.reply.best_answer` | `LmsForumController::markBestAnswer` | — | `access content` | GET |
| `/forum/reply/{lms_forum_reply}/edit` | `lms_forum.reply.edit` | `LmsForumController::editReply` | Edit Reply | `entity_access:lms_forum_reply.update` | GET/POST |
| `/forum/reply/{lms_forum_reply}/delete` | `entity.lms_forum_reply.delete_form` | _(entity form)_ | Delete Reply | `entity_access:lms_forum_reply.delete` | GET/POST |
| `/manage/settings/notifications` | `lms_notification.preferences` | `Form\NotificationPreferencesForm` | Notification Preferences | logged_in | GET/POST |

> **Note on `/zoom/join/{meeting_id}`:** This route triggers theme-suggestion hooks (`html__lms_zoom_web_player`, `page__lms_zoom_web_player`) that strip the normal header/footer and render a full-screen Zoom WebSDK page. The `ZoomController::generateSignature` method references a wrong service ID — a known bug. See [E02-issues/](../E02-issues/).

> **Note on `/manage/settings/notifications`:** Although under `/manage/`, this route is gated on `logged_in` (not `administer lms_course`), placing it conceptually in the learner group as a per-user preferences page.

> **Note on `lms_rating`:** Three runtime routes for rating actions are missing from `lms_rating.routing.yml` but referenced in `RatingListBuilder` — a known bug. See [E02-issues/](../E02-issues/).

### Navigation Flow — Learn/App

```
Login --> [Dashboard /dashboard]
    |
    +-- [My Courses /my-courses]
    |       +-- [Lesson View /learn/{course}/{lesson}]
    |               +-- [Quiz Take /quiz/{id}/take]
    |               |       +-- [Submit POST /quiz/{id}/submit]
    |               |       +-- [Quiz Result /quiz-result/{result}]
    |               |
    |               +-- [Zoom Join /zoom/join/{meeting_id}]
    |
    +-- [My Certificates /my-certificates]
    +-- [My Quiz Results /my-quiz-results]
    +-- [My Notes /my-notes]
    +-- [My Wishlist /my-wishlist]
    +-- [My Membership /my-membership]
    |       +-- [Cancel /my-membership/{id}/cancel]
    |
    +-- [Notifications /notifications]
    +-- [Profile /profile]
    +-- [Forum /forum]  (shared with Public)
    |       +-- [New Topic /forum/new]
    |       +-- [Reply /forum/{id}/reply]
    |       +-- [Edit Reply /forum/reply/{id}/edit]
    |       +-- [Mark Best Answer /forum/{id}/reply/{reply}/best]
    |
    +-- [Notification Preferences /manage/settings/notifications]
```

### Learner Account Menu

| Nav Item | URL | Notes |
|----------|-----|-------|
| Dashboard | `/dashboard` | Progress overview |
| My Courses | `/my-courses` | Enrolled courses |
| My Certificates | `/my-certificates` | Issued certificates |
| My Wishlist | `/my-wishlist` | Saved courses |
| My Notes | `/my-notes` | Lesson notes |
| My Membership | `/my-membership` | Active plan |
| Notifications | `/notifications` | In-app inbox |
| Profile | `/profile` | Account info |
| Switch User | `/app/switch-user` | Admins only (`switch users` perm) |

---

## Workspace/Manage Group

> Custom SPA-like admin UI for instructors and site administrators. Entirely bypasses Drupal Views/core admin. Built with custom templates and HTMX/AJAX partials via `AdminController` in `lms_course`.
> Primary access: `administer lms_course` permission. Sub-sections use entity-level access.

### /workspace — Core Admin Hub (lms_course)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/workspace` | `lms_course.admin_dashboard` | `AdminController::dashboard` | Admin Dashboard | `administer lms_course` | GET |
| `/workspace/courses` | `lms_course.admin_courses` | `AdminController::courseList` | Manage Courses | `administer lms_course` | GET |
| `/workspace/courses/create` | `lms_course.admin_course_create` | `AdminController::courseCreateWizard` | Create New Course | `administer lms_course` | GET |
| `/workspace/courses/{lms_course}/edit` | `lms_course.admin.course.edit` | `AdminController::courseEditForm` | Edit Course | `entity_access:lms_course.update` | GET/POST |
| `/workspace/quizzes` | `lms_course.admin_quizzes` | `AdminController::quizList` | Manage Quizzes | `administer lms_course` | GET |
| `/workspace/quizzes/create` | `lms_course.admin_quiz.create` | `AdminController::quizCreateForm` | Create Quiz | `entity_create_access:lms_quiz` | GET/POST |
| `/workspace/quizzes/{lms_quiz}/edit` | `lms_course.admin_quiz.edit` | `AdminController::quizEditForm` | Edit Quiz | `entity_access:lms_quiz.update` | GET/POST |
| `/workspace/orders` | `lms_course.admin_orders` | `AdminController::orderList` | Manage Orders | `administer lms_course` | GET |
| `/workspace/orders/{lms_order}/edit` | `lms_course.admin_order.edit` | `AdminController::orderEditForm` | Edit Order | `administer lms_course` | GET/POST |
| `/workspace/users` | `lms_course.admin_users` | `AdminController::userList` | Manage Users | `administer lms_course` | GET |
| `/workspace/users/create` | `lms_course.admin.user.create` | `AdminController::userCreateForm` | Create User | `administer lms_course` | GET/POST |
| `/workspace/users/{user}/admin-edit` | `lms_course.admin.user.edit` | `AdminController::userEditForm` | Edit User | `entity_access:user.update` | GET/POST |
| `/workspace/users/bulk` | `lms_course.admin.users.bulk` | `AdminController::bulkUsers` | _(bulk action)_ | `administer lms_course` | POST |
| `/workspace/reports` | `lms_course.admin_reports` | `AdminController::reports` | Reports & Analytics | `administer lms_course` | GET |
| `/workspace/chapters/{lms_chapter}/admin-edit` | `lms_course.admin.chapter.edit` | _(entity form)_ | Edit Chapter | `entity_access:lms_chapter.update` | GET/POST |
| `/workspace/lessons/{lms_lesson}/admin-edit` | `lms_course.admin.lesson.edit` | _(entity form)_ | Edit Lesson | `entity_access:lms_lesson.update` | GET/POST |

### /workspace/content — Generic Content CRUD (lms_course WorkspaceContentController)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/workspace/content/{content_type}` | `lms_course.workspace_content.list` | `WorkspaceContentController::contentList` | Manage Content | `administer lms_course` | GET |
| `/workspace/content/{content_type}/add` | `lms_course.workspace_content.add` | `WorkspaceContentController::contentCreateForm` | Create Content | `administer lms_course` | GET/POST |
| `/workspace/content/{content_type}/{node}/edit` | `lms_course.workspace_content.edit` | `WorkspaceContentController::contentEditForm` | Edit Content | `administer lms_course` | GET/POST |
| `/workspace/content/{content_type}/{node}/view` | `lms_course.workspace_content.view` | `WorkspaceContentController::contentView` | View Content | `administer lms_course` | GET |
| `/workspace/content/bulk` | `lms_course.workspace_content.bulk` | `WorkspaceContentController::bulkAction` | _(bulk action)_ | `administer lms_course` | POST |
| `/workspace/content/faqs/add` | `lms_course.workspace_faq.add` | `WorkspaceContentController::faqCreateForm` | Create FAQ | `administer lms_course` | GET/POST |
| `/workspace/content/faqs/{lms_faq}/edit` | `lms_course.workspace_faq.edit` | `WorkspaceContentController::faqEditForm` | Edit FAQ | `administer lms_course` | GET/POST |
| `/workspace/content/faqs/{lms_faq}/view` | `lms_course.workspace_faq.view` | `WorkspaceContentController::faqView` | View FAQ | `administer lms_course` | GET |

### /workspace/certificates — Certificate Management (lms_certificate)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/workspace/certificates` | `lms_certificate.admin_dashboard` | `CertificateAdminController::adminDashboard` | Certificate Management | `administer lms_course` | GET |
| `/workspace/certificates/{lms_user_certificate}/regenerate` | `lms_certificate.regenerate` | `CertificateAdminController::regenerateCertificate` | Regenerate Certificate PDF | `administer lms_course` | GET |
| `/workspace/certificate-templates` | `lms_certificate.admin_templates` | `CertificateAdminController::adminTemplates` | Certificate Templates | `administer lms_course` | GET |
| `/workspace/certificate-templates/add` | `lms_certificate.admin_template.add` | `CertificateAdminController::templateAddForm` | Add Certificate Template | `administer lms_course` | GET/POST |
| `/workspace/certificate-templates/{lms_certificate_template}/edit` | `lms_certificate.admin_template.edit` | `CertificateAdminController::templateEditForm` | Edit Certificate Template | `administer lms_course` | GET/POST |
| `/workspace/certificate-templates/{lms_certificate_template}/preview` | `lms_certificate.admin_template.preview` | `CertificateAdminController::templatePreview` | Preview Certificate Template | `administer lms_course` | GET |

### /workspace/pricing — Membership Plans (lms_membership)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/workspace/pricing` | `lms_membership.admin_pricing` | `MembershipAdminController::pricingList` | Membership Pricing | `administer lms_course` | GET |
| `/workspace/pricing/add` | `lms_membership.admin_pricing.add` | `MembershipAdminController::pricingAddForm` | Add Pricing Plan | `administer lms_course` | GET/POST |
| `/workspace/pricing/{lms_membership_plan}/edit` | `lms_membership.admin_pricing.edit` | `MembershipAdminController::pricingEditForm` | Edit Pricing Plan | `administer lms_course` | GET/POST |
| `/workspace/pricing/{lms_membership_plan}/toggle` | `lms_membership.admin_pricing.toggle` | `MembershipAdminController::toggleStatus` | Toggle Plan Status | `administer lms_course` | GET |
| `/workspace/user-memberships` | `lms_membership.admin_user_memberships` | `MembershipAdminController::userMembershipsList` | User Memberships | `administer lms_course` | GET |
| `/workspace/user-memberships/add` | `lms_membership.admin_user_memberships.add` | `MembershipAdminController::userMembershipAddForm` | Add User Membership | `administer lms_course` | GET/POST |
| `/workspace/user-memberships/{lms_user_membership}/edit` | `lms_membership.admin_user_memberships.edit` | `MembershipAdminController::userMembershipEditForm` | Edit User Membership | `administer lms_course` | GET/POST |

### /workspace/ratings — Review Moderation (lms_rating)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/workspace/ratings` | `lms_rating.admin_list` | `RatingManagerController::list` | Manage Reviews | `administer lms_course` | GET |
| `/workspace/ratings/{lms_rating}/{action}` | `lms_rating.admin_action` | `RatingManagerController::executeAction` | _(action)_ | `administer lms_course` | GET |

### /workspace/content-tools — Developer / Seed Tools (lms_content_tools)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/workspace/content-tools` | `lms_content_tools.dashboard` | `ContentToolsController::dashboard` | Content Tools | `administer lms content tools` | GET |
| `/workspace/content-tools/clean` | `lms_content_tools.clean` | `Form\CleanContentForm` | Clean Content | `administer lms content tools` | GET/POST |
| `/workspace/content-tools/seed-input` | `lms_content_tools.seed_input` | `Form\SeedInputForm` | Seed Input | `administer lms content tools` | GET/POST |

### /manage/blog — Blog Article CRUD (lms_blog)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/manage/blog` | `entity.lms_article.collection` | _(entity list)_ | Blog Articles | `administer lms_blog` | GET |
| `/manage/blog/add` | `entity.lms_article.add_form` | _(entity form)_ | Add Article | `entity_create_access:lms_article` | GET/POST |
| `/manage/blog/{lms_article}/edit` | `entity.lms_article.edit_form` | _(entity form)_ | Edit Article | `entity_access:lms_article.update` | GET/POST |
| `/manage/blog/{lms_article}/delete` | `entity.lms_article.delete_form` | _(entity form)_ | Delete Article | `entity_access:lms_article.delete` | GET/POST |

### /manage/courses — AI Course Builder (lms_ai)

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/manage/courses/ai-builder` | `lms_ai.course_builder_form` | `Form\CourseBuilderForm` | Build Course with AI | `administer lms_course` | GET/POST |

### Navigation Flow — Workspace

```
[Workspace /workspace]
    |
    +-- [Courses /workspace/courses]
    |       +-- [Create /workspace/courses/create]
    |       |       +-- [AI Builder /manage/courses/ai-builder]
    |       +-- [Edit /workspace/courses/{id}/edit]
    |       +-- [Edit Chapter /workspace/chapters/{id}/admin-edit]
    |       +-- [Edit Lesson /workspace/lessons/{id}/admin-edit]
    |
    +-- [Quizzes /workspace/quizzes]
    |       +-- [Create /workspace/quizzes/create]
    |       +-- [Edit /workspace/quizzes/{id}/edit]
    |
    +-- [Orders /workspace/orders]
    |       +-- [Edit /workspace/orders/{id}/edit]
    |
    +-- [Users /workspace/users]
    |       +-- [Create /workspace/users/create]
    |       +-- [Edit /workspace/users/{id}/admin-edit]
    |       +-- [Bulk /workspace/users/bulk]
    |
    +-- [Reports /workspace/reports]
    |
    +-- [Certificates /workspace/certificates]
    |       +-- [Regenerate /workspace/certificates/{id}/regenerate]
    |       +-- [Templates /workspace/certificate-templates]
    |               +-- [Add /workspace/certificate-templates/add]
    |               +-- [Edit /workspace/certificate-templates/{id}/edit]
    |               +-- [Preview /workspace/certificate-templates/{id}/preview]
    |
    +-- [Pricing /workspace/pricing]
    |       +-- [Add Plan /workspace/pricing/add]
    |       +-- [Edit Plan /workspace/pricing/{id}/edit]
    |       +-- [Toggle /workspace/pricing/{id}/toggle]
    |
    +-- [User Memberships /workspace/user-memberships]
    |       +-- [Add /workspace/user-memberships/add]
    |       +-- [Edit /workspace/user-memberships/{id}/edit]
    |
    +-- [Reviews /workspace/ratings]
    |       +-- [Action /workspace/ratings/{id}/{action}]
    |
    +-- [Content /workspace/content/{type}]
    |       +-- [Add /workspace/content/{type}/add]
    |       +-- [Edit /workspace/content/{type}/{node}/edit]
    |       +-- [View /workspace/content/{type}/{node}/view]
    |       +-- [FAQs /workspace/content/faqs/add]
    |
    +-- [Content Tools /workspace/content-tools]
    |       +-- [Clean /workspace/content-tools/clean]
    |       +-- [Seed /workspace/content-tools/seed-input]
    |
    +-- [Blog /manage/blog]  (separate prefix, same role)
            +-- [Add /manage/blog/add]
            +-- [Edit /manage/blog/{id}/edit]
            +-- [Delete /manage/blog/{id}/delete]
```

### Workspace Sidebar Navigation

The workspace UI renders a shared `lms-admin-sidebar.html.twig` partial included in all workspace templates. Sidebar items:

| Section | URL | Route |
|---------|-----|-------|
| Dashboard | `/workspace` | `lms_course.admin_dashboard` |
| Courses | `/workspace/courses` | `lms_course.admin_courses` |
| Quizzes | `/workspace/quizzes` | `lms_course.admin_quizzes` |
| Orders | `/workspace/orders` | `lms_course.admin_orders` |
| Users | `/workspace/users` | `lms_course.admin_users` |
| Reports | `/workspace/reports` | `lms_course.admin_reports` |
| Pricing Plans | `/workspace/pricing` | `lms_membership.admin_pricing` |
| User Memberships | `/workspace/user-memberships` | `lms_membership.admin_user_memberships` |
| Certificates | `/workspace/certificates` | `lms_certificate.admin_dashboard` |
| Certificate Templates | `/workspace/certificate-templates` | `lms_certificate.admin_templates` |
| Reviews | `/workspace/ratings` | `lms_rating.admin_list` |
| Content Tools | `/workspace/content-tools` | `lms_content_tools.dashboard` |
| Blog Articles | `/manage/blog` | `entity.lms_article.collection` |
| AI Course Builder | `/manage/courses/ai-builder` | `lms_ai.course_builder_form` |

---

## Drupal /admin Group

> Drupal core admin area, extended by EverLMS custom modules. Served by the `claro` admin theme. Access: `administer site configuration` or specific `administer lms_*` permissions.

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/admin/config/lms/ai` | `lms_ai.settings` | `Form\AiSettingsForm` | AI Platform Settings | `administer site configuration` | GET/POST |
| `/admin/everlms-ai-engine` | `lms_ai.engine_users_form` | `Form\AiEngineUserForm` | EverLMS AI Engine | `administer site configuration` | GET/POST |
| `/admin/everlms-ai-engine/preview-users-json` | `lms_ai.preview_users_json` | `AiPreviewController::previewUsersJson` | Preview AI JSON | `administer site configuration` | GET |
| `/admin/lms/reports/courses` | `lms_analytics.course_report` | `CourseReportController::build` | Course Analytics & Report | `administer lms_course` | GET |
| `/admin/lms/reports/courses/export` | `lms_analytics.course_report_export` | `CourseReportController::export` | _(CSV download)_ | `administer lms_course` | GET |
| `/admin/lms/reports/users` | `lms_analytics.user_report` | `UserReportController::build` | User Analytics & Report | `administer users` | GET |
| `/admin/lms/reports/users/export` | `lms_analytics.user_report_export` | `UserReportController::export` | _(CSV download)_ | `administer users` | GET |
| `/admin/lms/reports/quizzes` | `lms_analytics.quiz_report_list` | `QuizReportController::buildList` | Quiz Analytics & Report | `administer lms_quiz` | GET |
| `/admin/lms/reports/quizzes/{quiz_id}` | `lms_analytics.quiz_report_detail` | `QuizReportController::buildDetail` | Quiz Item Analysis | `administer lms_quiz` | GET |
| `/admin/lms/reports/quizzes/{quiz_id}/export` | `lms_analytics.quiz_report_export` | `QuizReportController::exportItemAnalysis` | _(CSV download)_ | `administer lms_quiz` | GET |
| `/admin/lms/certificates/{lms_user_certificate}/revoke` | `lms_certificate.revoke` | `Form\RevokeCertificateForm` | Revoke Certificate | `revoke any certificate` | GET/POST |
| `/admin/lms/notifications/broadcast` | `lms_notification.broadcast` | `Form\BroadcastForm` | Broadcast Announcement | `administer lms_course` | GET/POST |
| `/admin/config/everlms/payment` | `lms_payment.settings` | `Form\PaymentSettingsForm` | Payment Gateway Settings | `administer site configuration` | GET/POST |
| `/admin/config/lms/zoom` | `lms_zoom.settings` | `Form\ZoomSettingsForm` | Zoom Configuration | `administer site configuration` | GET/POST |
| `/admin/switch-user` | `open_switcher.switch_form` | `Form\SwitchUserForm` | Switch User | `switch users` | GET/POST |
| `/admin/switch-user/{user}` | `open_switcher.switch_to` | `SwitchController::switchTo` | — | `switch users` | GET |
| `/admin/switch-back` | `open_switcher.switch_back` | `SwitchController::switchBack` | Switch Back | custom_access | GET |

> **Security note:** AI settings (`/admin/config/lms/ai`), Payment settings (`/admin/config/everlms/payment`), and Zoom settings (`/admin/config/lms/zoom`) store third-party API secrets in Drupal config (plaintext committed YAML). No Key module is used. See security risk in G02-risk-register.md (planned, layer not yet materialized) (planned, layer G — not yet materialized).

### Drupal /admin Menu Links (*.links.menu.yml)

| Module | Menu Item | Parent | URL |
|--------|-----------|--------|-----|
| `lms_ai` | EverLMS AI Engine | `system.admin_config` | `/admin/everlms-ai-engine` |
| `lms_blog` | Blog Articles | `system.admin_content` | `/manage/blog` |
| `lms_content_tools` | Content Tools | `system.admin_structure` | `/workspace/content-tools` |
| `lms_membership` | Membership Plans | `system.admin_structure` | _(entity collection)_ |
| `lms_membership` | User Memberships | `system.admin_structure` | _(entity collection)_ |
| `lms_payment` | Payment Gateway Settings | `system.admin_config` | `/admin/config/everlms/payment` |
| `lms_rating` | Course Ratings | `system.admin_content` | _(entity collection)_ |
| `lms_zoom` | Zoom Integration | `system.admin_config` | `/admin/config/lms/zoom` |
| `open_switcher` | Switch User | `account` (user menu) | `/app/switch-user` |
| `open_switcher` | Switch Back | `account` (user menu) | `/admin/switch-back` |

> Modules with no `*.links.menu.yml`: lms_analytics, lms_badge, lms_base, lms_bundle, lms_certificate, lms_chapter, lms_course, lms_faq, lms_forum, lms_lesson, lms_mobile_api, lms_note, lms_notification, lms_order, lms_paypal, lms_progress, lms_question, lms_quiz, lms_stripe, lms_wishlist, lms_zoom (settings via config menu only).

---

## API Group

> All routes return JSON. No HTML. Full endpoint specs live in [C06-apis/](C06-apis/).
>
> Two sub-families:
> - **Web/Internal API** (`/api/lms/*`, `/api/courses/*`): AJAX endpoints called by the Drupal frontend (lesson player, notes, wishlist, notifications, search, Zoom signature).
> - **Mobile REST API** (`/api/v1/*`): Flutter app backend. Auth via custom bearer token (keyvalue.expirable store + OTP) — NOT OAuth2/JWT despite unused `oauth_*` config keys in the module.

### Web / Internal API (9 endpoints)

| URL | Route Name | Controller | Access | Methods | Module |
|-----|-----------|------------|--------|---------|--------|
| `/api/courses/search` | `lms_course.api_search` | `CoursePublicController::searchApi` | `access content` | GET | lms_course |
| `/api/lms/progress/mark-complete` | `lms_progress.api_mark_complete` | `ProgressApiController::markComplete` | logged_in | POST | lms_progress |
| `/api/lms/note/save` | `lms_note.save` | `NoteController::save` | logged_in | POST | lms_note |
| `/api/lms/note/delete` | `lms_note.delete` | `NoteController::delete` | logged_in | POST | lms_note |
| `/api/lms/note/get` | `lms_note.get` | `NoteController::getNote` | logged_in | GET | lms_note |
| `/api/lms/notifications/unread-count` | `lms_notification.api_unread_count` | `NotificationController::apiUnreadCount` | `_access: TRUE` | GET | lms_notification |
| `/api/lms/wishlist/toggle` | `lms_wishlist.toggle` | `WishlistController::toggle` | logged_in | POST | lms_wishlist |
| `/api/lms/wishlist/status` | `lms_wishlist.status` | `WishlistController::getStatus` | logged_in | GET | lms_wishlist |
| `/api/lms/websdk/signature` | `lms_zoom.generate_signature` | `ZoomController::generateSignature` | logged_in | POST | lms_zoom |

> **Bug:** `ZoomController::generateSignature` references a wrong service ID. See [E02-issues/](../E02-issues/).

### Mobile REST API — Auth (10 endpoints)

| URL | Route Name | Controller | Access | Methods |
|-----|-----------|------------|--------|---------|
| `/api/v1/user/register` | `lms_mobile_api.user.register` | `MobileAuthController::register` | `TRUE` | POST |
| `/api/v1/user/register/verify-otp` | `lms_mobile_api.user.register.verify_otp` | `MobileAuthController::verifyRegisterOtp` | `TRUE` | POST |
| `/api/v1/user/register/resend-otp` | `lms_mobile_api.user.register.resend_otp` | `MobileAuthController::resendRegisterOtp` | `TRUE` | POST |
| `/api/v1/user/forgot-password` | `lms_mobile_api.user.forgot_password` | `MobileAuthController::forgotPassword` | `TRUE` | POST |
| `/api/v1/user/forgot-password/verify-otp` | `lms_mobile_api.user.forgot_password.verify_otp` | `MobileAuthController::verifyForgotPasswordOtp` | `TRUE` | POST |
| `/api/v1/user/reset-password` | `lms_mobile_api.user.reset_password` | `MobileAuthController::resetPassword` | `TRUE` | POST |
| `/api/v1/oauth/token` | `lms_mobile_api.oauth.token` | `MobileAuthController::oauthToken` | `TRUE` | POST |
| `/api/v1/user/profile` | `lms_mobile_api.user.profile` | `MobileAuthController::getProfile` | `TRUE` | GET |
| `/api/v1/user/me` | `lms_mobile_api.user.me` | `MobileAuthController::getUserMe` | `TRUE` | GET |
| `/api/v1/user/change-password` | `lms_mobile_api.user.change_password` | `MobileAuthController::changePassword` | `TRUE` | POST |

### Mobile REST API — Courses & Dashboard (6 endpoints)

| URL | Route Name | Controller | Access | Methods |
|-----|-----------|------------|--------|---------|
| `/api/v1/courses/search` | `lms_mobile_api.courses.search` | `MobileCourseController::searchCourses` | `TRUE` | GET |
| `/api/v1/categories` | `lms_mobile_api.categories` | `MobileCourseController::listCategories` | `TRUE` | GET |
| `/api/v1/courses/{courseId}` | `lms_mobile_api.course.detail` | `MobileCourseController::getCourseDetail` | `TRUE` | GET |
| `/api/v1/courses/{courseId}/enroll-free` | `lms_mobile_api.course.enroll_free` | `MobileCourseController::enrollFree` | `TRUE` | POST |
| `/api/v1/dashboard` | `lms_mobile_api.dashboard` | `MobileCourseController::getDashboard` | `TRUE` | GET |
| `/api/v1/my-courses` | `lms_mobile_api.my_courses` | `MobileCourseController::getMyCourses` | `TRUE` | GET |

### Mobile REST API — Learning (6 endpoints)

| URL | Route Name | Controller | Access | Methods |
|-----|-----------|------------|--------|---------|
| `/api/v1/courses/{courseId}/lessons/{lessonId}` | `lms_mobile_api.lesson.detail` | `MobileLearningController::getLessonDetail` | `TRUE` | GET |
| `/api/v1/quiz/{quizId}` | `lms_mobile_api.quiz.detail` | `MobileLearningController::getQuiz` | `TRUE` | GET |
| `/api/v1/quiz/{quizId}/submit` | `lms_mobile_api.quiz.submit` | `MobileLearningController::submitQuiz` | `TRUE` | POST |
| `/api/v1/quiz/results/{resultId}` | `lms_mobile_api.quiz.result` | `MobileLearningController::getQuizResult` | `TRUE` | GET |
| `/api/v1/quiz/my-results` | `lms_mobile_api.quiz.my_results` | `MobileLearningController::listMyQuizResults` | `TRUE` | GET |
| `/api/v1/progress/mark-complete` | `lms_mobile_api.progress.mark_complete` | `MobileLearningController::markLessonComplete` | `TRUE` | POST |

> **Authentication note:** All `/api/v1/*` routes declare `_access: 'TRUE'` in routing.yml. Authentication is enforced **at the controller level** via bearer token validation inside each mobile controller. Token is issued by `/api/v1/oauth/token` and stored in `keyvalue.expirable`. This is a custom mechanism, not standard OAuth2 or JWT, despite the route name.

> **No webhook endpoints exist anywhere** in the codebase. Stripe webhook signature verification is a TODO, not implemented.

### API Versioning

| Family | Active Version | Prefix | Auth Mechanism |
|--------|---------------|--------|----------------|
| Web/Internal | n/a (unversioned) | `/api/lms/*`, `/api/courses/*` | Drupal session cookie |
| Mobile | v1 | `/api/v1/*` | Custom bearer token (keyvalue.expirable + OTP) |

---

## Utility Group

> Two pages under `/app/` for admin user impersonation. Module: `open_switcher` (reusable utility, not an EverLMS-specific module).

| URL | Route Name | Controller | Title | Access | Methods |
|-----|-----------|------------|-------|--------|---------|
| `/app/switch-user` | `open_switcher.frontend` | `SwitchFrontendController::page` | Switch User | `switch users` | GET |
| `/app/switch-user/{user}` | `open_switcher.frontend_switch` | `SwitchController::switchTo` | — | `switch users` | GET |

> These routes mirror the `/admin/switch-user` functionality but via a frontend-themed page (`open-switcher-page.html.twig`). The `switch users` permission is required. Users with this permission also see "Switch User" and "Switch Back" entries in the Drupal account menu.

---

## Cross-Group Navigation

```
[Public /*] --[login]--> [Learn/App]
                              |
                         [user menu]
                              |
               +--------------+---------------+
               |                              |
        [Learner portal]              [Workspace /workspace]
        /dashboard                    /workspace/courses
        /my-courses                   /workspace/quizzes
        /learn/{course}/{lesson}      /workspace/users
        /quiz/{id}/take               /workspace/orders
        /my-certificates              /workspace/pricing
        ...                           ...
               |                              |
               +-------[API /api/*]-----------+
                              |
                    [/api/lms/* web AJAX]
                    [/api/v1/* mobile REST]

[Drupal /admin/*] -- separate entry point, Drush/admin only
[/app/switch-user] -- admin impersonation (open_switcher)
```

---

## URL Structure Summary

| Pattern | Used For | Example |
|---------|----------|---------|
| `/homepage` | Marketing homepage | `/homepage` |
| `/courses/{entity_id}` | Entity canonical (numeric ID, no slug) | `/courses/42` |
| `/categories/{taxonomy_term}` | Taxonomy term (numeric TID) | `/categories/5` |
| `/news/{node}` | Core node canonical | `/news/17` |
| `/instructor/{user}` | Drupal user entity | `/instructor/8` |
| `/learn/{lms_course}/{lms_lesson}` | Nested entity pair | `/learn/3/14` |
| `/quiz/{lms_quiz}/take` | Entity sub-action | `/quiz/7/take` |
| `/workspace/{resource}` | Custom admin UI | `/workspace/courses` |
| `/workspace/{resource}/{entity}/edit` | Admin entity CRUD | `/workspace/courses/42/edit` |
| `/api/v1/{resource}` | Mobile REST | `/api/v1/courses/search` |
| `/api/lms/{feature}/{action}` | Web AJAX | `/api/lms/note/save` |

> URL aliases via `drupal/pathauto` are configured for core content types. Custom entity routes use Drupal entity IDs (numeric), not human-readable slugs, unless pathauto patterns are configured.

---

## Pages Not Implemented (Draft vs. As-Built Gaps)

The prior draft (`docs-draft/C-Design/C07-sitemap.md`) listed pages that do not exist in the current codebase:

| Draft URL | Status |
|-----------|--------|
| `/` (root) | No route — homepage is at `/homepage` |
| `/faq` | No public FAQ listing route; FAQs managed at `/workspace/content/faqs/*` |
| `/learning-paths`, `/learning-paths/{slug}` | No routes — feature not implemented |
| `/instructors` (listing) | No instructor listing page; only `/instructor/{user}` (profile) |
| `/search` | No standalone search page; search is via `/api/courses/search` JSON API |
| `/orders`, `/orders/{id}` | No learner order history pages |
| `/certificates/{id}` | No individual certificate detail page (only list at `/my-certificates`) |
| `/profile/edit` | No profile edit page; `/profile` is read-only |
| `/badges` | `lms_badge` is near-stub — no learner badge page |
| `/checkout/success`, `/checkout/cancel` | No post-payment redirect pages |
| `/manage/grading` | No essay grading UI — quiz grading is static PHP factory, essay results are `pending_review` status |
| `/manage/coupons` | No coupon feature implemented |
| `/manage/categories` | No category management in workspace — uses Drupal taxonomy admin |
| `/manage/settings/*` (except `/manage/settings/notifications`) | Settings are under `/admin/config/*` not `/manage/settings/*` |

---

*Related docs:*
- *Entities: [../C04-entities/](C04-entities/)*
- *Modules: [../C05-modules/](C05-modules/)*
- *APIs: [../C06-apis/](C06-apis/)*
- *Skins/Homepage styles: [../C09-skins.md](C09-skins.md)*
