# D07 — Template-to-Controller Map

> Maps every `.html.twig` file under `web/modules/custom/` to its rendering controller(s) or form.
> Enumerated 2026-04-16.
> Columns: Template Name | File Path | Rendered By (Controller/Form FQCN) | Route ID(s) | Primary Unit

---

## lms_analytics

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-analytics-course-report.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-course-report.html.twig` | `CourseReportController::build` | lms_analytics.course_report | U-050 |
| lms-analytics-quiz-report-list.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-list.html.twig` | `QuizReportController::buildList` | lms_analytics.quiz_report_list | U-050 |
| lms-analytics-quiz-report-detail.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-detail.html.twig` | `QuizReportController::buildDetail` | lms_analytics.quiz_report_detail | U-050 |
| lms-analytics-user-report.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-user-report.html.twig` | `UserReportController::build` | lms_analytics.user_report | U-050 |

---

## lms_blog

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-blog-list.html.twig | `web/modules/custom/lms_blog/templates/lms-blog-list.html.twig` | `LmsBlogController::index` | lms_blog.frontend.collection | U-056 |
| lms-blog-single.html.twig | `web/modules/custom/lms_blog/templates/lms-blog-single.html.twig` | `LmsBlogController::view` | lms_blog.frontend.canonical | U-056 |

---

## lms_certificate

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| certificate-pdf.html.twig | `web/modules/custom/lms_certificate/templates/certificate-pdf.html.twig` | `CertificateGenerator` (service, not a controller route) | — (service-rendered via mPDF) | U-037 |
| certificate-verification-result.html.twig | `web/modules/custom/lms_certificate/templates/certificate-verification-result.html.twig` | `VerificationForm` | lms_certificate.verify | U-038 |
| lms-admin-certificates.html.twig | `web/modules/custom/lms_certificate/templates/lms-admin-certificates.html.twig` | `CertificateAdminController::adminDashboard` | lms_certificate.admin_dashboard | U-035 |
| lms-admin-certificate-templates.html.twig | `web/modules/custom/lms_certificate/templates/lms-admin-certificate-templates.html.twig` | `CertificateAdminController::adminTemplates` | lms_certificate.admin_templates | U-035 |

---

## lms_course

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-homepage.html.twig | `web/modules/custom/lms_course/templates/lms-homepage.html.twig` | `CoursePublicController::homepage` | lms_course.homepage | U-009 |
| lms-catalog.html.twig | `web/modules/custom/lms_course/templates/lms-catalog.html.twig` | `CoursePublicController::catalog` | lms_course.catalog | U-008 |
| lms-course-detail.html.twig | `web/modules/custom/lms_course/templates/lms-course-detail.html.twig` | `CoursePublicController::detail` | lms_course.detail | U-005 |
| lms-course-card.html.twig | `web/modules/custom/lms_course/templates/lms-course-card.html.twig` | included by lms-catalog, lms-homepage | lms_course.catalog, .homepage | U-008 |
| lms-categories.html.twig | `web/modules/custom/lms_course/templates/lms-categories.html.twig` | `CoursePublicController::categories` / `::categoryDetail` | lms_course.category, .category_detail | U-008 |
| lms-instructor-profile.html.twig | `web/modules/custom/lms_course/templates/lms-instructor-profile.html.twig` | `CoursePublicController::instructorProfile` | lms_course.instructor_profile | U-008 |
| lms-about.html.twig | `web/modules/custom/lms_course/templates/lms-about.html.twig` | `PageController::about` | lms_course.about | U-008 |
| lms-contact.html.twig | `web/modules/custom/lms_course/templates/lms-contact.html.twig` | `PageController::contact` | lms_course.contact | U-008 |
| lms-pricing.html.twig | `web/modules/custom/lms_course/templates/lms-pricing.html.twig` | `PageController::pricing` | lms_course.pricing | U-026 |
| lms-glossary.html.twig | `web/modules/custom/lms_course/templates/lms-glossary.html.twig` | `CoursePublicController::glossary` | lms_course.glossary | U-008 |
| lms-admin-dashboard.html.twig | `web/modules/custom/lms_course/templates/lms-admin-dashboard.html.twig` | `AdminController::dashboard` | lms_course.admin_dashboard | U-010 |
| lms-admin-courses.html.twig | `web/modules/custom/lms_course/templates/lms-admin-courses.html.twig` | `AdminController::courseList` | lms_course.admin_courses | U-010 |
| lms-admin-courses-table.html.twig | `web/modules/custom/lms_course/templates/lms-admin-courses-table.html.twig` | `AdminController::courseList` (partial) | lms_course.admin_courses | U-010 |
| lms-admin-course-create.html.twig | `web/modules/custom/lms_course/templates/lms-admin-course-create.html.twig` | `AdminController::courseCreateWizard` | lms_course.admin_course_create | U-010 |
| lms-admin-content.html.twig | `web/modules/custom/lms_course/templates/lms-admin-content.html.twig` | `WorkspaceContentController::contentList` | lms_course.workspace_content.list | U-010 |
| lms-admin-content-table.html.twig | `web/modules/custom/lms_course/templates/lms-admin-content-table.html.twig` | `WorkspaceContentController::contentList` (partial) | lms_course.workspace_content.list | U-010 |
| lms-admin-entity-form.html.twig | `web/modules/custom/lms_course/templates/lms-admin-entity-form.html.twig` | `WorkspaceContentController::contentCreateForm` / `::contentEditForm` | .workspace_content.add / .edit | U-010 |
| lms-admin-entity-view.html.twig | `web/modules/custom/lms_course/templates/lms-admin-entity-view.html.twig` | `WorkspaceContentController::contentView` | lms_course.workspace_content.view | U-010 |
| lms-admin-orders.html.twig | `web/modules/custom/lms_course/templates/lms-admin-orders.html.twig` | `AdminController::orderList` | lms_course.admin_orders | U-025 |
| lms-admin-quizzes.html.twig | `web/modules/custom/lms_course/templates/lms-admin-quizzes.html.twig` | `AdminController::quizList` | lms_course.admin_quizzes | U-013 |
| lms-admin-ratings.html.twig | `web/modules/custom/lms_course/templates/lms-admin-ratings.html.twig` | `RatingManagerController::list` | lms_rating.admin_list | U-041 |
| lms-admin-reports.html.twig | `web/modules/custom/lms_course/templates/lms-admin-reports.html.twig` | `AdminController::reports` | lms_course.admin_reports | U-010, U-050 |
| lms-admin-sidebar.html.twig | `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig` | layout include — used by all admin controllers | multiple | U-010 |
| lms-admin-users.html.twig | `web/modules/custom/lms_course/templates/lms-admin-users.html.twig` | `AdminController::userList` | lms_course.admin_users | U-010 |
| lms-admin-users-table.html.twig | `web/modules/custom/lms_course/templates/lms-admin-users-table.html.twig` | `AdminController::userList` (partial) | lms_course.admin_users | U-010 |
| lms-admin-user-form.html.twig | `web/modules/custom/lms_course/templates/lms-admin-user-form.html.twig` | `AdminController::userCreateForm` / `::userEditForm` | .admin.user.create / .edit | U-010 |
| lms-profile.html.twig | `web/modules/custom/lms_course/templates/lms-profile.html.twig` | `UserPortalController::profile` | lms_course.profile | U-021 |

---

## lms_forum

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-forum-index.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-index.html.twig` | `LmsForumController::index` | lms_forum.index | U-043 |
| lms-forum-topic.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-topic.html.twig` | `LmsForumController` (topic detail — **no route defined**, FIND-007) | — (unmapped) | U-043 |
| lms-forum-topic-form.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-topic-form.html.twig` | `LmsForumController::addTopic` | lms_forum.topic.add | U-040 |
| lms-forum-new-topic-page.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-new-topic-page.html.twig` | `LmsForumController::addTopic` (page wrapper) | lms_forum.topic.add | U-040 |
| lms-forum-edit-reply-page.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-edit-reply-page.html.twig` | `LmsForumController::editReply` | lms_forum.reply.edit | U-040 |

---

## lms_membership

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-my-membership.html.twig | `web/modules/custom/lms_membership/templates/lms-my-membership.html.twig` | `MembershipController::myMembership` | lms_membership.my_membership | U-026 |
| lms-admin-pricing.html.twig | `web/modules/custom/lms_membership/templates/lms-admin-pricing.html.twig` | `MembershipAdminController::pricingList` | lms_membership.admin_pricing | U-033 |
| lms-admin-user-memberships.html.twig | `web/modules/custom/lms_membership/templates/lms-admin-user-memberships.html.twig` | `MembershipAdminController::userMembershipsList` | lms_membership.admin_user_memberships | U-033 |

---

## lms_note

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-my-notes.html.twig | `web/modules/custom/lms_note/templates/lms-my-notes.html.twig` | `NoteController::myNotes` | lms_note.my_notes | U-053 |

---

## lms_notification

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-notifications.html.twig | `web/modules/custom/lms_notification/templates/lms-notifications.html.twig` | `NotificationController::listNotifications` | lms_notification.list | U-039 |
| lms-notification-bell.html.twig | `web/modules/custom/lms_notification/templates/lms-notification-bell.html.twig` | `NotificationBellBlock` (Block Plugin) | — (block, not a route) | U-039 |

---

## lms_order

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-checkout.html.twig | `web/modules/custom/lms_order/templates/lms-checkout.html.twig` | `CheckoutController::checkout` / `::checkoutMembership` | lms_order.checkout, .checkout_membership | U-027 |
| lms-bank-transfer.html.twig | `web/modules/custom/lms_order/templates/lms-bank-transfer.html.twig` | `CheckoutController::bankTransferInstruction` | lms_order.bank_transfer | U-027 |

---

## lms_progress

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-dashboard.html.twig | `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig` | `UserPortalController::dashboard` | lms_progress.dashboard | U-024 |
| lms-lesson-view.html.twig | `web/modules/custom/lms_progress/templates/lms-lesson-view.html.twig` | `UserPortalController::lessonView` | lms_progress.lesson_view | U-022 |
| lms-my-courses.html.twig | `web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` | `UserPortalController::myCourses` | lms_progress.my_courses | U-021 |
| lms-my-certificates.html.twig | `web/modules/custom/lms_progress/templates/lms-my-certificates.html.twig` | `UserPortalController::myCertificates` | lms_progress.certificates | U-035 |

---

## lms_quiz

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-quiz-take.html.twig | `web/modules/custom/lms_quiz/templates/lms-quiz-take.html.twig` | `QuizController::takeQuiz` | lms_quiz.take | U-017 |
| lms-quiz-result.html.twig | `web/modules/custom/lms_quiz/templates/lms-quiz-result.html.twig` | `QuizController::viewResult` | lms_quiz.result | U-019 |
| lms-my-quiz-results.html.twig | `web/modules/custom/lms_quiz/templates/lms-my-quiz-results.html.twig` | `QuizController::myResults` | lms_quiz.my_results | U-019 |

---

## lms_wishlist

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-my-wishlist.html.twig | `web/modules/custom/lms_wishlist/templates/lms-my-wishlist.html.twig` | `WishlistController::myWishlist` | lms_wishlist.my_wishlist | U-051 |

---

## lms_zoom

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| lms-zoom-web-player.html.twig | `web/modules/custom/lms_zoom/templates/lms-zoom-web-player.html.twig` | `ZoomController::joinMeeting` | lms_zoom.join_meeting | U-049 |
| lms-zoom-join-block.html.twig | `web/modules/custom/lms_zoom/templates/lms-zoom-join-block.html.twig` | `ZoomJoinBlock` (Block Plugin) | — (block) | U-049 |
| html--lms-zoom-web-player.html.twig | `web/modules/custom/lms_zoom/templates/html--lms-zoom-web-player.html.twig` | Drupal HTML layout override for Zoom player route | lms_zoom.join_meeting | U-049 |
| page--lms-zoom-web-player.html.twig | `web/modules/custom/lms_zoom/templates/page--lms-zoom-web-player.html.twig` | Drupal page layout override for Zoom player route | lms_zoom.join_meeting | U-049 |

---

## open_switcher

| Template | File Path | Rendered By | Route ID | Unit |
|----------|-----------|-------------|----------|------|
| open-switcher-page.html.twig | `web/modules/custom/open_switcher/templates/open-switcher-page.html.twig` | `SwitchFrontendController::page` | open_switcher.frontend | — |

---

## Anomalies

| Template | Issue |
|----------|-------|
| `lms-forum-topic.html.twig` | Template exists but no canonical `/forum/{id}` route — orphaned template (FIND-007) |
| `certificate-pdf.html.twig` | Rendered by `CertificateGenerator` service (mPDF), not a web route — PDF-only output |
| `lms-notification-bell.html.twig` | Block plugin output; not tied to a route |
| `lms-zoom-join-block.html.twig` | Block plugin output; not tied to a route |

---

## Template Count Summary

| Module | Template Count |
|--------|---------------|
| lms_course | 27 |
| lms_certificate | 4 |
| lms_forum | 5 |
| lms_progress | 4 |
| lms_quiz | 3 |
| lms_analytics | 4 |
| lms_zoom | 4 |
| lms_notification | 2 |
| lms_order | 2 |
| lms_membership | 3 |
| lms_blog | 2 |
| lms_note | 1 |
| lms_wishlist | 1 |
| open_switcher | 1 |
| **Total** | **~63** |

---

*End of D07-code-map/template-to-controller.md*
