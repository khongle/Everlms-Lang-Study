# D07 — Route-to-Controller Map

> Pulled from all `*.routing.yml` files in `web/modules/custom/`.
> Enumerated 2026-04-16.
> Columns: Route ID | Method | Path | Controller/Handler FQCN::method | Primary Unit | Auth Requirement

---

## lms_ai

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_ai.settings | GET | `/admin/config/lms/ai` | `\Drupal\lms_ai\Form\AiSettingsForm` (form) | U-047 | perm: administer site configuration |
| lms_ai.course_builder_form | GET | `/manage/courses/ai-builder` | `\Drupal\lms_ai\Form\CourseBuilderForm` (form) | U-048 | perm: administer lms_course |
| lms_ai.engine_users_form | GET | `/admin/everlms-ai-engine` | `\Drupal\lms_ai\Form\AiEngineUserForm` (form) | U-047 | perm: administer site configuration |
| lms_ai.preview_users_json | GET | `/admin/everlms-ai-engine/preview-users-json` | `\Drupal\lms_ai\Controller\AiPreviewController::previewUsersJson` | U-047 | perm: administer site configuration |

---

## lms_analytics

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_analytics.course_report | GET | `/admin/lms/reports/courses` | `\Drupal\lms_analytics\Controller\CourseReportController::build` | U-050 | perm: administer lms_course |
| lms_analytics.course_report_export | GET | `/admin/lms/reports/courses/export` | `\Drupal\lms_analytics\Controller\CourseReportController::export` | U-050 | perm: administer lms_course |
| lms_analytics.user_report | GET | `/admin/lms/reports/users` | `\Drupal\lms_analytics\Controller\UserReportController::build` | U-050 | perm: administer users |
| lms_analytics.user_report_export | GET | `/admin/lms/reports/users/export` | `\Drupal\lms_analytics\Controller\UserReportController::export` | U-050 | perm: administer users |
| lms_analytics.quiz_report_list | GET | `/admin/lms/reports/quizzes` | `\Drupal\lms_analytics\Controller\QuizReportController::buildList` | U-050 | perm: administer lms_quiz |
| lms_analytics.quiz_report_detail | GET | `/admin/lms/reports/quizzes/{quiz_id}` | `\Drupal\lms_analytics\Controller\QuizReportController::buildDetail` | U-050 | perm: administer lms_quiz |
| lms_analytics.quiz_report_export | GET | `/admin/lms/reports/quizzes/{quiz_id}/export` | `\Drupal\lms_analytics\Controller\QuizReportController::exportItemAnalysis` | U-050 | perm: administer lms_quiz |

---

## lms_blog

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| entity.lms_article.collection | GET | `/manage/blog` | `_entity_list: lms_article` | U-056 | perm: administer lms_blog |
| entity.lms_article.add_form | GET | `/manage/blog/add` | `_entity_form: lms_article.add` | U-056 | entity_create_access: lms_article |
| entity.lms_article.edit_form | GET | `/manage/blog/{lms_article}/edit` | `_entity_form: lms_article.edit` | U-056 | entity_access: lms_article.update |
| entity.lms_article.delete_form | GET | `/manage/blog/{lms_article}/delete` | `_entity_form: lms_article.delete` | U-056 | entity_access: lms_article.delete |
| lms_blog.frontend.collection | GET | `/news` | `\Drupal\lms_blog\Controller\LmsBlogController::index` | U-056 | public (_access: TRUE) |
| lms_blog.frontend.canonical | GET | `/news/{node}` | `\Drupal\lms_blog\Controller\LmsBlogController::view` | U-056 | public (_access: TRUE) |

---

## lms_certificate

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_certificate.verify | GET | `/certificate/verify` | `\Drupal\lms_certificate\Form\VerificationForm` (form) | U-038 | public (_access: TRUE) |
| lms_certificate.revoke | GET | `/admin/lms/certificates/{lms_user_certificate}/revoke` | `\Drupal\lms_certificate\Form\RevokeCertificateForm` (form) | U-035 | perm: revoke any certificate |
| lms_certificate.regenerate | GET | `/workspace/certificates/{lms_user_certificate}/regenerate` | `\Drupal\lms_certificate\Controller\CertificateAdminController::regenerateCertificate` | U-037 | perm: administer lms_course |
| lms_certificate.admin_dashboard | GET | `/workspace/certificates` | `\Drupal\lms_certificate\Controller\CertificateAdminController::adminDashboard` | U-035 | perm: administer lms_course |
| lms_certificate.admin_templates | GET | `/workspace/certificate-templates` | `\Drupal\lms_certificate\Controller\CertificateAdminController::adminTemplates` | U-035 | perm: administer lms_course |
| lms_certificate.admin_template.add | GET | `/workspace/certificate-templates/add` | `\Drupal\lms_certificate\Controller\CertificateAdminController::templateAddForm` | U-035 | perm: administer lms_course |
| lms_certificate.admin_template.edit | GET | `/workspace/certificate-templates/{lms_certificate_template}/edit` | `\Drupal\lms_certificate\Controller\CertificateAdminController::templateEditForm` | U-035 | perm: administer lms_course |
| lms_certificate.admin_template.preview | GET | `/workspace/certificate-templates/{lms_certificate_template}/preview` | `\Drupal\lms_certificate\Controller\CertificateAdminController::templatePreview` | U-035 | perm: administer lms_course |

---

## lms_content_tools

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_content_tools.dashboard | GET | `/workspace/content-tools` | `\Drupal\lms_content_tools\Controller\ContentToolsController::dashboard` | — | perm: administer lms content tools |
| lms_content_tools.clean | GET | `/workspace/content-tools/clean` | `\Drupal\lms_content_tools\Form\CleanContentForm` (form) | — | perm: administer lms content tools |
| lms_content_tools.seed_input | GET | `/workspace/content-tools/seed-input` | `\Drupal\lms_content_tools\Form\SeedInputForm` (form) | — | perm: administer lms content tools |

---

## lms_course

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_course.homepage | GET | `/homepage` | `\Drupal\lms_course\Controller\CoursePublicController::homepage` | U-009 | perm: access content |
| lms_course.catalog | GET | `/courses` | `\Drupal\lms_course\Controller\CoursePublicController::catalog` | U-008 | perm: access content |
| lms_course.detail | GET | `/courses/{lms_course}` | `\Drupal\lms_course\Controller\CoursePublicController::detail` | U-005 | perm: access content |
| lms_course.enroll_free | GET | `/courses/{lms_course}/enroll-free` | `\Drupal\lms_course\Controller\CourseEnrollmentController::enrollFree` | U-011 | perm: access content |
| lms_course.category | GET | `/categories` | `\Drupal\lms_course\Controller\CoursePublicController::categories` | U-008 | perm: access content |
| lms_course.category_detail | GET | `/categories/{taxonomy_term}` | `\Drupal\lms_course\Controller\CoursePublicController::categoryDetail` | U-008 | perm: access content |
| lms_course.glossary | GET | `/glossary` | `\Drupal\lms_course\Controller\CoursePublicController::glossary` | U-008 | perm: access content |
| lms_course.about | GET | `/about` | `\Drupal\lms_course\Controller\PageController::about` | U-008 | perm: access content |
| lms_course.contact | GET | `/contact` | `\Drupal\lms_course\Controller\PageController::contact` | U-008 | perm: access content |
| lms_course.pricing | GET | `/pricing` | `\Drupal\lms_course\Controller\PageController::pricing` | U-026 | perm: access content |
| lms_course.instructor_profile | GET | `/instructor/{user}` | `\Drupal\lms_course\Controller\CoursePublicController::instructorProfile` | U-008 | perm: access content |
| lms_course.api_search | GET | `/api/courses/search` | `\Drupal\lms_course\Controller\CoursePublicController::searchApi` | U-008 | perm: access content |
| lms_course.profile | GET | `/profile` | `\Drupal\lms_progress\Controller\UserPortalController::profile` | U-021 | logged in |
| lms_course.admin_dashboard | GET | `/workspace` | `\Drupal\lms_course\Controller\AdminController::dashboard` | U-010 | perm: administer lms_course |
| lms_course.admin_courses | GET | `/workspace/courses` | `\Drupal\lms_course\Controller\AdminController::courseList` | U-010 | perm: administer lms_course |
| lms_course.admin_course_create | GET | `/workspace/courses/create` | `\Drupal\lms_course\Controller\AdminController::courseCreateWizard` | U-010 | perm: administer lms_course |
| lms_course.admin.course.edit | GET | `/workspace/courses/{lms_course}/edit` | `\Drupal\lms_course\Controller\AdminController::courseEditForm` | U-005 | entity_access: lms_course.update |
| lms_course.admin_quizzes | GET | `/workspace/quizzes` | `\Drupal\lms_course\Controller\AdminController::quizList` | U-013 | perm: administer lms_course |
| lms_course.admin_quiz.create | GET | `/workspace/quizzes/create` | `\Drupal\lms_course\Controller\AdminController::quizCreateForm` | U-013 | entity_create_access: lms_quiz |
| lms_course.admin_quiz.edit | GET | `/workspace/quizzes/{lms_quiz}/edit` | `\Drupal\lms_course\Controller\AdminController::quizEditForm` | U-013 | entity_access: lms_quiz.update |
| lms_course.admin_orders | GET | `/workspace/orders` | `\Drupal\lms_course\Controller\AdminController::orderList` | U-025 | perm: administer lms_course |
| lms_course.admin_order.edit | GET | `/workspace/orders/{lms_order}/edit` | `\Drupal\lms_course\Controller\AdminController::orderEditForm` | U-025 | perm: administer lms_course |
| lms_course.admin_users | GET | `/workspace/users` | `\Drupal\lms_course\Controller\AdminController::userList` | U-010 | perm: administer lms_course |
| lms_course.admin.user.create | GET | `/workspace/users/create` | `\Drupal\lms_course\Controller\AdminController::userCreateForm` | U-010 | perm: administer lms_course |
| lms_course.admin.user.edit | GET | `/workspace/users/{user}/admin-edit` | `\Drupal\lms_course\Controller\AdminController::userEditForm` | U-010 | entity_access: user.update |
| lms_course.admin.users.bulk | POST | `/workspace/users/bulk` | `\Drupal\lms_course\Controller\AdminController::bulkUsers` | U-010 | perm: administer lms_course |
| lms_course.admin_reports | GET | `/workspace/reports` | `\Drupal\lms_course\Controller\AdminController::reports` | U-010, U-050 | perm: administer lms_course |
| lms_course.admin.chapter.edit | GET | `/workspace/chapters/{lms_chapter}/admin-edit` | `_entity_form: lms_chapter.admin_edit` | U-006 | entity_access: lms_chapter.update |
| lms_course.admin.lesson.edit | GET | `/workspace/lessons/{lms_lesson}/admin-edit` | `_entity_form: lms_lesson.admin_edit` | U-007 | entity_access: lms_lesson.update |
| lms_course.workspace_content.list | GET | `/workspace/content/{content_type}` | `\Drupal\lms_course\Controller\WorkspaceContentController::contentList` | U-010 | perm: administer lms_course |
| lms_course.workspace_content.add | GET | `/workspace/content/{content_type}/add` | `\Drupal\lms_course\Controller\WorkspaceContentController::contentCreateForm` | U-010 | perm: administer lms_course |
| lms_course.workspace_content.edit | GET | `/workspace/content/{content_type}/{node}/edit` | `\Drupal\lms_course\Controller\WorkspaceContentController::contentEditForm` | U-010 | perm: administer lms_course |
| lms_course.workspace_content.view | GET | `/workspace/content/{content_type}/{node}/view` | `\Drupal\lms_course\Controller\WorkspaceContentController::contentView` | U-010 | perm: administer lms_course |
| lms_course.workspace_content.bulk | POST | `/workspace/content/bulk` | `\Drupal\lms_course\Controller\WorkspaceContentController::bulkAction` | U-010 | perm: administer lms_course |
| lms_course.workspace_faq.add | GET | `/workspace/content/faqs/add` | `\Drupal\lms_course\Controller\WorkspaceContentController::faqCreateForm` | U-055 | perm: administer lms_course |
| lms_course.workspace_faq.edit | GET | `/workspace/content/faqs/{lms_faq}/edit` | `\Drupal\lms_course\Controller\WorkspaceContentController::faqEditForm` | U-055 | perm: administer lms_course |
| lms_course.workspace_faq.view | GET | `/workspace/content/faqs/{lms_faq}/view` | `\Drupal\lms_course\Controller\WorkspaceContentController::faqView` | U-055 | perm: administer lms_course |

---

## lms_forum

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_forum.index | GET | `/forum` | `\Drupal\lms_forum\Controller\LmsForumController::index` | U-043 | perm: access content |
| lms_forum.topic.add | GET | `/forum/new` | `\Drupal\lms_forum\Controller\LmsForumController::addTopic` | U-040 | entity_create_access: lms_forum_topic |
| lms_forum.reply.add | GET/POST | `/forum/{lms_forum_topic}/reply` | `\Drupal\lms_forum\Controller\LmsForumController::reply` | U-040 | entity_create_access: lms_forum_reply |
| lms_forum.reply.best_answer | GET | `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | `\Drupal\lms_forum\Controller\LmsForumController::markBestAnswer` | U-040 | perm: access content |
| entity.lms_forum_reply.delete_form | GET | `/forum/reply/{lms_forum_reply}/delete` | `_entity_form: lms_forum_reply.delete` | U-040 | entity_access: lms_forum_reply.delete |
| lms_forum.reply.edit | GET | `/forum/reply/{lms_forum_reply}/edit` | `\Drupal\lms_forum\Controller\LmsForumController::editReply` | U-040 | entity_access: lms_forum_reply.update |

**Note (FIND-007):** `/forum/{id}` (topic canonical view) is **missing** from routing. `lms_forum.index` exists but no topic detail route. See A10 FIND-007.

---

## lms_membership

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_membership.my_membership | GET | `/my-membership` | `\Drupal\lms_membership\Controller\MembershipController::myMembership` | U-026 | logged in |
| lms_membership.cancel_membership | GET | `/my-membership/{lms_user_membership}/cancel` | `\Drupal\lms_membership\Controller\MembershipController::cancelMembership` | U-033 | logged in |
| lms_membership.admin_pricing | GET | `/workspace/pricing` | `\Drupal\lms_membership\Controller\MembershipAdminController::pricingList` | U-033 | perm: administer lms_course |
| lms_membership.admin_pricing.add | GET | `/workspace/pricing/add` | `\Drupal\lms_membership\Controller\MembershipAdminController::pricingAddForm` | U-033 | perm: administer lms_course |
| lms_membership.admin_pricing.edit | GET | `/workspace/pricing/{lms_membership_plan}/edit` | `\Drupal\lms_membership\Controller\MembershipAdminController::pricingEditForm` | U-033 | perm: administer lms_course |
| lms_membership.admin_pricing.toggle | GET | `/workspace/pricing/{lms_membership_plan}/toggle` | `\Drupal\lms_membership\Controller\MembershipAdminController::toggleStatus` | U-033 | perm: administer lms_course |
| lms_membership.admin_user_memberships | GET | `/workspace/user-memberships` | `\Drupal\lms_membership\Controller\MembershipAdminController::userMembershipsList` | U-033 | perm: administer lms_course |
| lms_membership.admin_user_memberships.add | GET | `/workspace/user-memberships/add` | `\Drupal\lms_membership\Controller\MembershipAdminController::userMembershipAddForm` | U-033 | perm: administer lms_course |
| lms_membership.admin_user_memberships.edit | GET | `/workspace/user-memberships/{lms_user_membership}/edit` | `\Drupal\lms_membership\Controller\MembershipAdminController::userMembershipEditForm` | U-033 | perm: administer lms_course |

---

## lms_note

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_note.save | POST | `/api/lms/note/save` | `\Drupal\lms_note\Controller\NoteController::save` | U-053 | logged in |
| lms_note.delete | POST | `/api/lms/note/delete` | `\Drupal\lms_note\Controller\NoteController::delete` | U-053 | logged in |
| lms_note.get | GET | `/api/lms/note/get` | `\Drupal\lms_note\Controller\NoteController::getNote` | U-053 | logged in |
| lms_note.my_notes | GET | `/my-notes` | `\Drupal\lms_note\Controller\NoteController::myNotes` | U-053 | logged in |

---

## lms_notification

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_notification.list | GET | `/notifications` | `\Drupal\lms_notification\Controller\NotificationController::listNotifications` | U-039 | logged in |
| lms_notification.preferences | GET | `/manage/settings/notifications` | `\Drupal\lms_notification\Form\NotificationPreferencesForm` (form) | U-039 | logged in |
| lms_notification.broadcast | GET | `/admin/lms/notifications/broadcast` | `\Drupal\lms_notification\Form\BroadcastForm` (form) | U-042 | perm: administer lms_course |
| lms_notification.api_unread_count | GET | `/api/lms/notifications/unread-count` | `\Drupal\lms_notification\Controller\NotificationController::apiUnreadCount` | U-039 | **public** (_access: TRUE) — review risk |

---

## lms_order

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_order.checkout | GET | `/checkout/{course_id}` | `\Drupal\lms_order\Controller\CheckoutController::checkout` | U-027 | **public** (_access: TRUE) — FIND-004 |
| lms_order.checkout_membership | GET | `/checkout/membership/{plan_id}` | `\Drupal\lms_order\Controller\CheckoutController::checkoutMembership` | U-027 | **public** (_access: TRUE) — FIND-004 |
| lms_order.bank_transfer | GET | `/checkout/bank-transfer/{lms_order}` | `\Drupal\lms_order\Controller\CheckoutController::bankTransferInstruction` | U-027 | perm: access content |

---

## lms_payment

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_payment.settings | GET | `/admin/config/everlms/payment` | `\Drupal\lms_payment\Form\PaymentSettingsForm` (form) | U-030 | perm: administer site configuration |

---

## lms_progress

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_progress.my_courses | GET | `/my-courses` | `\Drupal\lms_progress\Controller\UserPortalController::myCourses` | U-021 | logged in |
| lms_progress.lesson_view | GET | `/learn/{lms_course}/{lms_lesson}` | `\Drupal\lms_progress\Controller\UserPortalController::lessonView` | U-022 | logged in |
| lms_progress.dashboard | GET | `/dashboard` | `\Drupal\lms_progress\Controller\UserPortalController::dashboard` | U-024 | logged in |
| lms_progress.certificates | GET | `/my-certificates` | `\Drupal\lms_progress\Controller\UserPortalController::myCertificates` | U-035 | logged in |
| lms_progress.api_mark_complete | POST | `/api/lms/progress/mark-complete` | `\Drupal\lms_progress\Controller\ProgressApiController::markComplete` | U-022, U-059 | logged in |

---

## lms_quiz

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_quiz.take | GET | `/quiz/{lms_quiz}/take` | `\Drupal\lms_quiz\Controller\QuizController::takeQuiz` | U-017 | logged in |
| lms_quiz.submit | POST | `/quiz/{lms_quiz}/submit` | `\Drupal\lms_quiz\Controller\QuizController::submitQuiz` | U-018 | logged in |
| lms_quiz.result | GET | `/quiz-result/{lms_quiz_result}` | `\Drupal\lms_quiz\Controller\QuizController::viewResult` | U-019 | logged in |
| lms_quiz.my_results | GET | `/my-quiz-results` | `\Drupal\lms_quiz\Controller\QuizController::myResults` | U-019 | logged in |

---

## lms_rating

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_rating.admin_list | GET | `/workspace/ratings` | `\Drupal\lms_rating\Controller\RatingManagerController::list` | U-041 | perm: administer lms_course |
| lms_rating.admin_action | GET | `/workspace/ratings/{lms_rating}/{action}` | `\Drupal\lms_rating\Controller\RatingManagerController::executeAction` | U-041 | perm: administer lms_course |

---

## lms_wishlist

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_wishlist.toggle | POST | `/api/lms/wishlist/toggle` | `\Drupal\lms_wishlist\Controller\WishlistController::toggle` | U-051 | logged in |
| lms_wishlist.status | GET | `/api/lms/wishlist/status` | `\Drupal\lms_wishlist\Controller\WishlistController::getStatus` | U-051 | logged in |
| lms_wishlist.my_wishlist | GET | `/my-wishlist` | `\Drupal\lms_wishlist\Controller\WishlistController::myWishlist` | U-051 | logged in |

---

## lms_zoom

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| lms_zoom.settings | GET | `/admin/config/lms/zoom` | `\Drupal\lms_zoom\Form\ZoomSettingsForm` (form) | U-045 | perm: administer site configuration |
| lms_zoom.join_meeting | GET | `/zoom/join/{meeting_id}` | `\Drupal\lms_zoom\Controller\ZoomController::joinMeeting` | U-049 | logged in |
| lms_zoom.generate_signature | POST | `/api/lms/websdk/signature` | `\Drupal\lms_zoom\Controller\ZoomController::generateSignature` | U-049 | logged in |

---

## open_switcher

| Route ID | HTTP Method | Path | Controller FQCN::method | Primary Unit | Auth |
|----------|-------------|------|------------------------|--------------|------|
| open_switcher.switch_form | GET | `/admin/switch-user` | `\Drupal\open_switcher\Form\SwitchUserForm` (form) | — | perm: switch users |
| open_switcher.switch_to | GET | `/admin/switch-user/{user}` | `\Drupal\open_switcher\Controller\SwitchController::switchTo` | — | perm: switch users |
| open_switcher.switch_back | GET | `/admin/switch-back` | `\Drupal\open_switcher\Controller\SwitchController::switchBack` | — | custom access: canSwitchBack |
| open_switcher.frontend | GET | `/app/switch-user` | `\Drupal\open_switcher\Controller\SwitchFrontendController::page` | — | perm: switch users |
| open_switcher.frontend_switch | GET | `/app/switch-user/{user}` | `\Drupal\open_switcher\Controller\SwitchController::switchTo` | — | perm: switch users |

---

## Unmapped / Missing Routes (Findings)

| Finding | Expected Route | Actual Status | Reference |
|---------|---------------|---------------|-----------|
| FIND-004 | `/checkout/{course_id}` uses `_access: TRUE` | Auth in controller only, no routing gate | U-058 |
| FIND-004 | `/checkout/membership/{plan_id}` uses `_access: TRUE` | Auth in controller only, no routing gate | U-058 |
| FIND-006 | `RatingController::toggleApprove`, `::toggleFeatured` | Methods exist, no routes | cleanup task |
| FIND-007 | `/forum/{id}` topic canonical | Missing — no route defined | U-043 |
| FIND-007 | `/admin/lms/reports/revenue` | Missing — no route defined | U-050 |
| — | `/api/lms/notifications/unread-count` | Public (`_access: TRUE`); no auth check | U-039 review |

---

## Route Count Summary

| Module | Route Count |
|--------|-------------|
| lms_course | 37 |
| lms_certificate | 8 |
| lms_membership | 9 |
| lms_analytics | 7 |
| lms_forum | 6 |
| lms_notification | 4 |
| lms_note | 4 |
| lms_wishlist | 3 |
| lms_quiz | 4 |
| lms_zoom | 3 |
| lms_blog | 6 |
| lms_ai | 4 |
| lms_order | 3 |
| lms_progress | 5 |
| lms_rating | 2 |
| lms_payment | 1 |
| lms_content_tools | 3 |
| open_switcher | 5 |
| **Total** | **~114** |

---

*End of D07-code-map/route-to-controller.md*
