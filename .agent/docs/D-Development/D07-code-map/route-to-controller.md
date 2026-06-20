# D07 — Route to Controller Map

**Purpose:** All 137 custom routes → `Controller::method` (or Form class), grouped by surface.

**Last verified:** 2026-06-20  
**Source:** `web/modules/custom/*/src/Controller/`, `*.routing.yml`

---

## Surface groups

| Group | Prefix | Count |
|-------|--------|-------|
| [A — Public (anonymous)](#a--public-routes-anonymous) | `/homepage`, `/courses`, `/categories`, `/news`, `/forum`, `/certificate`, `/checkout` | 19 |
| [B — Learn / App (authenticated learner)](#b--learn--app-routes-authenticated-learner) | `/dashboard`, `/my-*`, `/learn/`, `/quiz/`, `/zoom/`, `/forum/`, `/notifications` | 21 |
| [C — Workspace / Manage (instructor/admin)](#c--workspace--manage-routes) | `/workspace/*`, `/manage/*` | 47 |
| [D — Drupal /admin](#d--drupal-admin-routes) | `/admin/config/lms/*`, `/admin/everlms-*`, `/admin/lms/*`, `/admin/switch-*` | 17 |
| [E — API](#e--api-routes) | `/api/lms/*`, `/api/courses/*`, `/api/v1/*` | 31 |
| [F — Utility](#f--utility-routes) | `/app/switch-user*` | 2 |
| **Total** | | **137** |

---

## A — Public Routes (anonymous)

*Access: `_permission: 'access content'` or `_access: 'TRUE'`*

| Path | Route Name | Controller::method | Module |
|------|------------|--------------------|--------|
| `/homepage` | `lms_course.homepage` | `CoursePublicController::homepage` | lms_course |
| `/courses` | `lms_course.catalog` | `CoursePublicController::catalog` | lms_course |
| `/courses/{lms_course}` | `lms_course.detail` | `CoursePublicController::detail` | lms_course |
| `/courses/{lms_course}/enroll-free` | `lms_course.enroll_free` | `CourseEnrollmentController::enrollFree` | lms_course |
| `/categories` | `lms_course.category` | `CoursePublicController::categories` | lms_course |
| `/categories/{taxonomy_term}` | `lms_course.category_detail` | `CoursePublicController::categoryDetail` | lms_course |
| `/glossary` | `lms_course.glossary` | `CoursePublicController::glossary` | lms_course |
| `/about` | `lms_course.about` | `PageController::about` | lms_course |
| `/contact` | `lms_course.contact` | `PageController::contact` | lms_course |
| `/pricing` | `lms_course.pricing` | `PageController::pricing` | lms_course |
| `/instructor/{user}` | `lms_course.instructor_profile` | `CoursePublicController::instructorProfile` | lms_course |
| `/news` | `lms_blog.frontend.collection` | `LmsBlogController::index` | lms_blog |
| `/news/{node}` | `lms_blog.frontend.canonical` | `LmsBlogController::view` | lms_blog |
| `/certificate/verify` | `lms_certificate.verify` | `Form\VerificationForm` | lms_certificate |
| `/checkout/{course_id}` | `lms_order.checkout` | `CheckoutController::checkout` | lms_order |
| `/checkout/membership/{plan_id}` | `lms_order.checkout_membership` | `CheckoutController::checkoutMembership` | lms_order |
| `/checkout/bank-transfer/{lms_order}` | `lms_order.bank_transfer` | `CheckoutController::bankTransferInstruction` | lms_order |
| `/forum` | `lms_forum.index` | `LmsForumController::index` | lms_forum |
| `/forum/{lms_forum_topic}` | `entity.lms_forum_topic.canonical` *(overridden)* | `LmsForumController::viewTopic` *(via RouteSubscriber)* | lms_forum |

---

## B — Learn / App Routes (authenticated learner)

*Access: `_user_is_logged_in: 'TRUE'`*

| Path | Route Name | Controller::method | Module |
|------|------------|--------------------|--------|
| `/dashboard` | `lms_progress.dashboard` | `UserPortalController::dashboard` | lms_progress |
| `/my-courses` | `lms_progress.my_courses` | `UserPortalController::myCourses` | lms_progress |
| `/learn/{lms_course}/{lms_lesson}` | `lms_progress.lesson_view` | `UserPortalController::lessonView` | lms_progress |
| `/my-certificates` | `lms_progress.certificates` | `UserPortalController::myCertificates` | lms_progress |
| `/profile` | `lms_course.profile` | `UserPortalController::profile` | lms_course / lms_progress |
| `/my-membership` | `lms_membership.my_membership` | `MembershipController::myMembership` | lms_membership |
| `/my-membership/{lms_user_membership}/cancel` | `lms_membership.cancel_membership` | `MembershipController::cancelMembership` | lms_membership |
| `/my-notes` | `lms_note.my_notes` | `NoteController::myNotes` | lms_note |
| `/my-wishlist` | `lms_wishlist.my_wishlist` | `WishlistController::myWishlist` | lms_wishlist |
| `/my-quiz-results` | `lms_quiz.my_results` | `QuizController::myResults` | lms_quiz |
| `/notifications` | `lms_notification.list` | `NotificationController::listNotifications` | lms_notification |
| `/quiz/{lms_quiz}/take` | `lms_quiz.take` | `QuizController::takeQuiz` | lms_quiz |
| `/quiz/{lms_quiz}/submit` | `lms_quiz.submit` | `QuizController::submitQuiz` | lms_quiz |
| `/quiz-result/{lms_quiz_result}` | `lms_quiz.result` | `QuizController::viewResult` | lms_quiz |
| `/zoom/join/{meeting_id}` | `lms_zoom.join_meeting` | `ZoomController::joinMeeting` | lms_zoom |
| `/forum/new` | `lms_forum.topic.add` | `LmsForumController::addTopic` | lms_forum |
| `/forum/{lms_forum_topic}/reply` | `lms_forum.reply.add` | `LmsForumController::reply` | lms_forum |
| `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | `lms_forum.reply.best_answer` | `LmsForumController::markBestAnswer` | lms_forum |
| `/forum/reply/{lms_forum_reply}/edit` | `lms_forum.reply.edit` | `LmsForumController::editReply` | lms_forum |
| `/forum/reply/{lms_forum_reply}/delete` | `entity.lms_forum_reply.delete_form` | `_entity_form: lms_forum_reply.delete` | lms_forum |
| `/manage/settings/notifications` | `lms_notification.preferences` | `Form\NotificationPreferencesForm` | lms_notification |

---

## C — Workspace / Manage Routes

*Access: `_permission: 'administer lms_course'` or entity-level access*

### C.1 — lms_course (AdminController + WorkspaceContentController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/workspace` | `lms_course.admin_dashboard` | `AdminController::dashboard` |
| `/workspace/courses` | `lms_course.admin_courses` | `AdminController::courseList` |
| `/workspace/courses/create` | `lms_course.admin_course_create` | `AdminController::courseCreateWizard` |
| `/workspace/courses/{lms_course}/edit` | `lms_course.admin.course.edit` | `AdminController::courseEditForm` |
| `/workspace/quizzes` | `lms_course.admin_quizzes` | `AdminController::quizList` |
| `/workspace/quizzes/create` | `lms_course.admin_quiz.create` | `AdminController::quizCreateForm` |
| `/workspace/quizzes/{lms_quiz}/edit` | `lms_course.admin_quiz.edit` | `AdminController::quizEditForm` |
| `/workspace/orders` | `lms_course.admin_orders` | `AdminController::orderList` |
| `/workspace/orders/{lms_order}/edit` | `lms_course.admin_order.edit` | `AdminController::orderEditForm` |
| `/workspace/users` | `lms_course.admin_users` | `AdminController::userList` |
| `/workspace/users/create` | `lms_course.admin.user.create` | `AdminController::userCreateForm` |
| `/workspace/users/{user}/admin-edit` | `lms_course.admin.user.edit` | `AdminController::userEditForm` |
| `/workspace/users/bulk` | `lms_course.admin.users.bulk` | `AdminController::bulkUsers` |
| `/workspace/reports` | `lms_course.admin_reports` | `AdminController::reports` |
| `/workspace/chapters/{lms_chapter}/admin-edit` | `lms_course.admin.chapter.edit` | `_entity_form: lms_chapter.admin_edit` |
| `/workspace/lessons/{lms_lesson}/admin-edit` | `lms_course.admin.lesson.edit` | `_entity_form: lms_lesson.admin_edit` |
| `/workspace/content/{content_type}` | `lms_course.workspace_content.list` | `WorkspaceContentController::contentList` |
| `/workspace/content/{content_type}/add` | `lms_course.workspace_content.add` | `WorkspaceContentController::contentCreateForm` |
| `/workspace/content/{content_type}/{node}/edit` | `lms_course.workspace_content.edit` | `WorkspaceContentController::contentEditForm` |
| `/workspace/content/{content_type}/{node}/view` | `lms_course.workspace_content.view` | `WorkspaceContentController::contentView` |
| `/workspace/content/bulk` | `lms_course.workspace_content.bulk` | `WorkspaceContentController::bulkAction` |
| `/workspace/content/faqs/add` | `lms_course.workspace_faq.add` | `WorkspaceContentController::faqCreateForm` |
| `/workspace/content/faqs/{lms_faq}/edit` | `lms_course.workspace_faq.edit` | `WorkspaceContentController::faqEditForm` |
| `/workspace/content/faqs/{lms_faq}/view` | `lms_course.workspace_faq.view` | `WorkspaceContentController::faqView` |

### C.2 — lms_certificate (CertificateAdminController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/workspace/certificates` | `lms_certificate.admin_dashboard` | `CertificateAdminController::adminDashboard` |
| `/workspace/certificates/{lms_user_certificate}/regenerate` | `lms_certificate.regenerate` | `CertificateAdminController::regenerateCertificate` |
| `/workspace/certificate-templates` | `lms_certificate.admin_templates` | `CertificateAdminController::adminTemplates` |
| `/workspace/certificate-templates/add` | `lms_certificate.admin_template.add` | `CertificateAdminController::templateAddForm` |
| `/workspace/certificate-templates/{lms_certificate_template}/edit` | `lms_certificate.admin_template.edit` | `CertificateAdminController::templateEditForm` |
| `/workspace/certificate-templates/{lms_certificate_template}/preview` | `lms_certificate.admin_template.preview` | `CertificateAdminController::templatePreview` |

### C.3 — lms_membership (MembershipAdminController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/workspace/pricing` | `lms_membership.admin_pricing` | `MembershipAdminController::pricingList` |
| `/workspace/pricing/add` | `lms_membership.admin_pricing.add` | `MembershipAdminController::pricingAddForm` |
| `/workspace/pricing/{lms_membership_plan}/edit` | `lms_membership.admin_pricing.edit` | `MembershipAdminController::pricingEditForm` |
| `/workspace/pricing/{lms_membership_plan}/toggle` | `lms_membership.admin_pricing.toggle` | `MembershipAdminController::toggleStatus` |
| `/workspace/user-memberships` | `lms_membership.admin_user_memberships` | `MembershipAdminController::userMembershipsList` |
| `/workspace/user-memberships/add` | `lms_membership.admin_user_memberships.add` | `MembershipAdminController::userMembershipAddForm` |
| `/workspace/user-memberships/{lms_user_membership}/edit` | `lms_membership.admin_user_memberships.edit` | `MembershipAdminController::userMembershipEditForm` |

### C.4 — lms_rating (RatingManagerController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/workspace/ratings` | `lms_rating.admin_list` | `RatingManagerController::list` |
| `/workspace/ratings/{lms_rating}/{action}` | `lms_rating.admin_action` | `RatingManagerController::executeAction` |

### C.5 — lms_content_tools (ContentToolsController + Forms)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/workspace/content-tools` | `lms_content_tools.dashboard` | `ContentToolsController::dashboard` |
| `/workspace/content-tools/clean` | `lms_content_tools.clean` | `Form\CleanContentForm` |
| `/workspace/content-tools/seed-input` | `lms_content_tools.seed_input` | `Form\SeedInputForm` |

### C.6 — lms_blog (entity form routes)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/manage/blog` | `entity.lms_article.collection` | `_entity_list: lms_article` |
| `/manage/blog/add` | `entity.lms_article.add_form` | `_entity_form: lms_article.add` |
| `/manage/blog/{lms_article}/edit` | `entity.lms_article.edit_form` | `_entity_form: lms_article.edit` |
| `/manage/blog/{lms_article}/delete` | `entity.lms_article.delete_form` | `_entity_form: lms_article.delete` |

### C.7 — lms_ai (CourseBuilderForm)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/manage/courses/ai-builder` | `lms_ai.course_builder_form` | `Form\CourseBuilderForm` |

### C.8 — lms_notification (NotificationPreferencesForm — also in Group B)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/manage/settings/notifications` | `lms_notification.preferences` | `Form\NotificationPreferencesForm` |

---

## D — Drupal /admin Routes

*Access: `administer site configuration`, `administer lms_*`, or `administer users`*

| Path | Route Name | Controller::method | Module |
|------|------------|--------------------|----|
| `/admin/config/lms/ai` | `lms_ai.settings` | `Form\AiSettingsForm` | lms_ai |
| `/admin/everlms-ai-engine` | `lms_ai.engine_users_form` | `Form\AiEngineUserForm` | lms_ai |
| `/admin/everlms-ai-engine/preview-users-json` | `lms_ai.preview_users_json` | `AiPreviewController::previewUsersJson` | lms_ai |
| `/admin/lms/reports/courses` | `lms_analytics.course_report` | `CourseReportController::build` | lms_analytics |
| `/admin/lms/reports/courses/export` | `lms_analytics.course_report_export` | `CourseReportController::export` | lms_analytics |
| `/admin/lms/reports/users` | `lms_analytics.user_report` | `UserReportController::build` | lms_analytics |
| `/admin/lms/reports/users/export` | `lms_analytics.user_report_export` | `UserReportController::export` | lms_analytics |
| `/admin/lms/reports/quizzes` | `lms_analytics.quiz_report_list` | `QuizReportController::buildList` | lms_analytics |
| `/admin/lms/reports/quizzes/{quiz_id}` | `lms_analytics.quiz_report_detail` | `QuizReportController::buildDetail` | lms_analytics |
| `/admin/lms/reports/quizzes/{quiz_id}/export` | `lms_analytics.quiz_report_export` | `QuizReportController::exportItemAnalysis` | lms_analytics |
| `/admin/lms/certificates/{lms_user_certificate}/revoke` | `lms_certificate.revoke` | `Form\RevokeCertificateForm` | lms_certificate |
| `/admin/lms/notifications/broadcast` | `lms_notification.broadcast` | `Form\BroadcastForm` | lms_notification |
| `/admin/config/everlms/payment` | `lms_payment.settings` | `Form\PaymentSettingsForm` | lms_payment |
| `/admin/config/lms/zoom` | `lms_zoom.settings` | `Form\ZoomSettingsForm` | lms_zoom |
| `/admin/switch-user` | `open_switcher.switch_form` | `Form\SwitchUserForm` | open_switcher |
| `/admin/switch-user/{user}` | `open_switcher.switch_to` | `SwitchController::switchTo` | open_switcher |
| `/admin/switch-back` | `open_switcher.switch_back` | `SwitchController::switchBack` | open_switcher |

---

## E — API Routes

*All return JSON. Auth enforced per-route or per-controller.*

### E.1 — Web / Internal API (`/api/lms/*`, `/api/courses/*`)

| Path | Route Name | Controller::method | Module |
|------|------------|--------------------|----|
| `/api/courses/search` | `lms_course.api_search` | `CoursePublicController::searchApi` | lms_course |
| `/api/lms/progress/mark-complete` | `lms_progress.api_mark_complete` | `ProgressApiController::markComplete` | lms_progress |
| `/api/lms/note/save` | `lms_note.save` | `NoteController::save` | lms_note |
| `/api/lms/note/delete` | `lms_note.delete` | `NoteController::delete` | lms_note |
| `/api/lms/note/get` | `lms_note.get` | `NoteController::getNote` | lms_note |
| `/api/lms/notifications/unread-count` | `lms_notification.api_unread_count` | `NotificationController::apiUnreadCount` | lms_notification |
| `/api/lms/wishlist/toggle` | `lms_wishlist.toggle` | `WishlistController::toggle` | lms_wishlist |
| `/api/lms/wishlist/status` | `lms_wishlist.status` | `WishlistController::getStatus` | lms_wishlist |
| `/api/lms/websdk/signature` | `lms_zoom.generate_signature` | `ZoomController::generateSignature` | lms_zoom |

### E.2 — Mobile REST API (`/api/v1/*`) — lms_mobile_api

*All routes: `_access: 'TRUE'`; auth enforced inside controller via bearer token in keyvalue store.*

#### Auth endpoints (MobileAuthController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/api/v1/user/register` | `lms_mobile_api.user.register` | `MobileAuthController::register` |
| `/api/v1/user/register/verify-otp` | `lms_mobile_api.user.register.verify_otp` | `MobileAuthController::verifyRegisterOtp` |
| `/api/v1/user/register/resend-otp` | `lms_mobile_api.user.register.resend_otp` | `MobileAuthController::resendRegisterOtp` |
| `/api/v1/user/forgot-password` | `lms_mobile_api.user.forgot_password` | `MobileAuthController::forgotPassword` |
| `/api/v1/user/forgot-password/verify-otp` | `lms_mobile_api.user.forgot_password.verify_otp` | `MobileAuthController::verifyForgotPasswordOtp` |
| `/api/v1/user/reset-password` | `lms_mobile_api.user.reset_password` | `MobileAuthController::resetPassword` |
| `/api/v1/oauth/token` | `lms_mobile_api.oauth.token` | `MobileAuthController::oauthToken` |
| `/api/v1/user/profile` | `lms_mobile_api.user.profile` | `MobileAuthController::getProfile` |
| `/api/v1/user/me` | `lms_mobile_api.user.me` | `MobileAuthController::getUserMe` |
| `/api/v1/user/change-password` | `lms_mobile_api.user.change_password` | `MobileAuthController::changePassword` |

#### Course endpoints (MobileCourseController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/api/v1/courses/search` | `lms_mobile_api.courses.search` | `MobileCourseController::searchCourses` |
| `/api/v1/categories` | `lms_mobile_api.categories` | `MobileCourseController::listCategories` |
| `/api/v1/courses/{courseId}` | `lms_mobile_api.course.detail` | `MobileCourseController::getCourseDetail` |
| `/api/v1/courses/{courseId}/enroll-free` | `lms_mobile_api.course.enroll_free` | `MobileCourseController::enrollFree` |
| `/api/v1/dashboard` | `lms_mobile_api.dashboard` | `MobileCourseController::getDashboard` |
| `/api/v1/my-courses` | `lms_mobile_api.my_courses` | `MobileCourseController::getMyCourses` |

#### Learning endpoints (MobileLearningController)

| Path | Route Name | Controller::method |
|------|------------|-------------------|
| `/api/v1/courses/{courseId}/lessons/{lessonId}` | `lms_mobile_api.lesson.detail` | `MobileLearningController::getLessonDetail` |
| `/api/v1/quiz/{quizId}` | `lms_mobile_api.quiz.detail` | `MobileLearningController::getQuiz` |
| `/api/v1/quiz/{quizId}/submit` | `lms_mobile_api.quiz.submit` | `MobileLearningController::submitQuiz` |
| `/api/v1/quiz/results/{resultId}` | `lms_mobile_api.quiz.result` | `MobileLearningController::getQuizResult` |
| `/api/v1/quiz/my-results` | `lms_mobile_api.quiz.my_results` | `MobileLearningController::listMyQuizResults` |
| `/api/v1/progress/mark-complete` | `lms_mobile_api.progress.mark_complete` | `MobileLearningController::markLessonComplete` |

---

## F — Utility Routes

| Path | Route Name | Controller::method | Module |
|------|------------|--------------------|----|
| `/app/switch-user` | `open_switcher.frontend` | `SwitchFrontendController::page` | open_switcher |
| `/app/switch-user/{user}` | `open_switcher.frontend_switch` | `SwitchController::switchTo` | open_switcher |

---

## Modules with NO routing.yml

The following modules have no custom web routes (entity admin UI auto-generated by `AdminHtmlRouteProvider` only):  
lms_badge, lms_base, lms_bundle, lms_chapter, lms_faq, lms_lesson, lms_paypal, lms_question, lms_stripe, lms_wishlist (wishlist has 3 routes above).

**lms_question** and **lms_stripe** and **lms_paypal** have no routing.yml. Their entities are accessed via auto-generated routes from entity links annotations.

---

## Cross-references

- Template for each route: [template-to-controller.md](template-to-controller.md)
- Source files for each controller: [per-module-files.md](per-module-files.md)
- API surface detail (request/response shapes): [C06-apis/](../../C-Design/C06-apis/)
- Sitemap tree: [C07-sitemap.md](../../C-Design/C07-sitemap.md)
