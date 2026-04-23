# D07 — Per-Module File Registry

> Enumerated 2026-04-16 via `find web/modules/custom -type f \( -name "*.php" -o -name "*.twig" \)`.
> Excludes: scripts/, install files, migration YAMLs, *.info.yml, and generated stubs.
> Primary Unit IDs cross-reference D03-unit-specs/.
> Group by convention: G-FOUNDATION, G-CONTENT, G-ASSESSMENT, G-PROGRESS, G-COMMERCE, G-CERTIFICATE, G-COMMUNICATION, G-INTEGRATION, G-ADDITIONAL.

---

## lms_base — G-FOUNDATION

Units: U-004, U-064, U-067, U-068

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| LmsHelper.php | `web/modules/custom/lms_base/src/Service/LmsHelper.php` | Service | U-004, U-064 |
| LmsDemoContentCommand.php | `web/modules/custom/lms_base/src/Commands/LmsDemoContentCommand.php` | Drush Command | U-004 |

---

## lms_course — G-CONTENT, G-INTEGRATION

Units: U-005, U-008, U-009, U-010, U-011, U-012, U-050 (partial)

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Course.php | `web/modules/custom/lms_course/src/Entity/Course.php` | Entity | U-005 |
| CourseListBuilder.php | `web/modules/custom/lms_course/src/CourseListBuilder.php` | List Builder | U-005 |
| CourseAccessControlHandler.php | `web/modules/custom/lms_course/src/Access/CourseAccessControlHandler.php` | Access Handler | U-005 |
| CourseForm.php | `web/modules/custom/lms_course/src/Form/CourseForm.php` | Form | U-005 |
| CoursePublicController.php | `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` | Controller | U-008, U-009 |
| CourseEnrollmentController.php | `web/modules/custom/lms_course/src/Controller/CourseEnrollmentController.php` | Controller | U-011 |
| AdminController.php | `web/modules/custom/lms_course/src/Controller/AdminController.php` | Controller | U-010, U-012 |
| WorkspaceContentController.php | `web/modules/custom/lms_course/src/Controller/WorkspaceContentController.php` | Controller | U-010 |
| PageController.php | `web/modules/custom/lms_course/src/Controller/PageController.php` | Controller | U-008 |
| ApproveOrderForm.php | `web/modules/custom/lms_course/src/Form/ApproveOrderForm.php` | Form | U-025 |
| LmsCourseBreadcrumbBuilder.php | `web/modules/custom/lms_course/src/Breadcrumb/LmsCourseBreadcrumbBuilder.php` | Service | U-005 |
| lms-homepage.html.twig | `web/modules/custom/lms_course/templates/lms-homepage.html.twig` | Template | U-009 |
| lms-catalog.html.twig | `web/modules/custom/lms_course/templates/lms-catalog.html.twig` | Template | U-008 |
| lms-course-detail.html.twig | `web/modules/custom/lms_course/templates/lms-course-detail.html.twig` | Template | U-005 |
| lms-course-card.html.twig | `web/modules/custom/lms_course/templates/lms-course-card.html.twig` | Template | U-008 |
| lms-categories.html.twig | `web/modules/custom/lms_course/templates/lms-categories.html.twig` | Template | U-008 |
| lms-admin-dashboard.html.twig | `web/modules/custom/lms_course/templates/lms-admin-dashboard.html.twig` | Template | U-010 |
| lms-admin-courses.html.twig | `web/modules/custom/lms_course/templates/lms-admin-courses.html.twig` | Template | U-010 |
| lms-admin-courses-table.html.twig | `web/modules/custom/lms_course/templates/lms-admin-courses-table.html.twig` | Template | U-010 |
| lms-admin-orders.html.twig | `web/modules/custom/lms_course/templates/lms-admin-orders.html.twig` | Template | U-025 |
| lms-admin-quizzes.html.twig | `web/modules/custom/lms_course/templates/lms-admin-quizzes.html.twig` | Template | U-013 |
| lms-admin-ratings.html.twig | `web/modules/custom/lms_course/templates/lms-admin-ratings.html.twig` | Template | U-041 |
| lms-admin-reports.html.twig | `web/modules/custom/lms_course/templates/lms-admin-reports.html.twig` | Template | U-010, U-050 |
| lms-admin-sidebar.html.twig | `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig` | Template | U-010 |
| lms-admin-content.html.twig | `web/modules/custom/lms_course/templates/lms-admin-content.html.twig` | Template | U-010 |
| lms-admin-content-table.html.twig | `web/modules/custom/lms_course/templates/lms-admin-content-table.html.twig` | Template | U-010 |
| lms-admin-course-create.html.twig | `web/modules/custom/lms_course/templates/lms-admin-course-create.html.twig` | Template | U-010 |
| lms-admin-entity-form.html.twig | `web/modules/custom/lms_course/templates/lms-admin-entity-form.html.twig` | Template | U-010 |
| lms-admin-entity-view.html.twig | `web/modules/custom/lms_course/templates/lms-admin-entity-view.html.twig` | Template | U-010 |
| lms-admin-users.html.twig | `web/modules/custom/lms_course/templates/lms-admin-users.html.twig` | Template | U-010 |
| lms-admin-users-table.html.twig | `web/modules/custom/lms_course/templates/lms-admin-users-table.html.twig` | Template | U-010 |
| lms-admin-user-form.html.twig | `web/modules/custom/lms_course/templates/lms-admin-user-form.html.twig` | Template | U-010 |
| lms-instructor-profile.html.twig | `web/modules/custom/lms_course/templates/lms-instructor-profile.html.twig` | Template | U-008 |
| lms-profile.html.twig | `web/modules/custom/lms_course/templates/lms-profile.html.twig` | Template | U-021 |
| lms-about.html.twig | `web/modules/custom/lms_course/templates/lms-about.html.twig` | Template | U-008 |
| lms-contact.html.twig | `web/modules/custom/lms_course/templates/lms-contact.html.twig` | Template | U-008 |
| lms-pricing.html.twig | `web/modules/custom/lms_course/templates/lms-pricing.html.twig` | Template | U-026 |
| lms-glossary.html.twig | `web/modules/custom/lms_course/templates/lms-glossary.html.twig` | Template | U-008 |

---

## lms_chapter — G-CONTENT

Units: U-006

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Chapter.php | `web/modules/custom/lms_chapter/src/Entity/Chapter.php` | Entity | U-006 |
| ChapterAccessControlHandler.php | `web/modules/custom/lms_chapter/src/Access/ChapterAccessControlHandler.php` | Access Handler | U-006 |
| ChapterForm.php | `web/modules/custom/lms_chapter/src/Form/ChapterForm.php` | Form | U-006 |

---

## lms_lesson — G-CONTENT

Units: U-007

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Lesson.php | `web/modules/custom/lms_lesson/src/Entity/Lesson.php` | Entity | U-007 |
| LessonAccessControlHandler.php | `web/modules/custom/lms_lesson/src/Access/LessonAccessControlHandler.php` | Access Handler | U-007 |
| LessonForm.php | `web/modules/custom/lms_lesson/src/Form/LessonForm.php` | Form | U-007 |

---

## lms_quiz — G-ASSESSMENT

Units: U-013, U-017, U-018, U-019, U-020

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Quiz.php | `web/modules/custom/lms_quiz/src/Entity/Quiz.php` | Entity | U-013 |
| QuizResult.php | `web/modules/custom/lms_quiz/src/Entity/QuizResult.php` | Entity | U-019 |
| QuestionResponse.php | `web/modules/custom/lms_quiz/src/Entity/QuestionResponse.php` | Entity | U-019 |
| QuizPassedEvent.php | `web/modules/custom/lms_quiz/src/Event/QuizPassedEvent.php` | Event | U-020 |
| QuizAccessControlHandler.php | `web/modules/custom/lms_quiz/src/Access/QuizAccessControlHandler.php` | Access Handler | U-013 |
| QuizController.php | `web/modules/custom/lms_quiz/src/Controller/QuizController.php` | Controller | U-017, U-018 |
| QuizForm.php | `web/modules/custom/lms_quiz/src/Form/QuizForm.php` | Form | U-013 |
| GradingStrategyFactory.php | `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php` | Service | U-020 |
| QuestionGradingInterface.php | `web/modules/custom/lms_quiz/src/Grading/QuestionGradingInterface.php` | Interface | U-020 |
| SingleChoiceStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/SingleChoiceStrategy.php` | Strategy | U-020 |
| MultipleChoiceStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/MultipleChoiceStrategy.php` | Strategy | U-020 |
| TrueFalseStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/TrueFalseStrategy.php` | Strategy | U-020 |
| FillBlankStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/FillBlankStrategy.php` | Strategy | U-020 |
| ShortAnswerStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/ShortAnswerStrategy.php` | Strategy | U-020 |
| EssayStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/EssayStrategy.php` | Strategy | U-020 |
| MatchingStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/MatchingStrategy.php` | Strategy | U-020 |
| OrderingStrategy.php | `web/modules/custom/lms_quiz/src/Grading/Strategy/OrderingStrategy.php` | Strategy | U-020 |
| lms-quiz-take.html.twig | `web/modules/custom/lms_quiz/templates/lms-quiz-take.html.twig` | Template | U-017 |
| lms-quiz-result.html.twig | `web/modules/custom/lms_quiz/templates/lms-quiz-result.html.twig` | Template | U-019 |
| lms-my-quiz-results.html.twig | `web/modules/custom/lms_quiz/templates/lms-my-quiz-results.html.twig` | Template | U-019 |

---

## lms_question — G-ASSESSMENT

Units: U-014, U-015, U-016

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Question.php | `web/modules/custom/lms_question/src/Entity/Question.php` | Entity | U-014 |
| QuestionType.php | `web/modules/custom/lms_question/src/Entity/QuestionType.php` | Entity (bundle) | U-014 |
| Answer.php | `web/modules/custom/lms_question/src/Entity/Answer.php` | Entity | U-015 |
| MatchingPair.php | `web/modules/custom/lms_question/src/Entity/MatchingPair.php` | Entity | U-016 |
| QuestionAccessControlHandler.php | `web/modules/custom/lms_question/src/Access/QuestionAccessControlHandler.php` | Access Handler | U-014 |
| QuestionForm.php | `web/modules/custom/lms_question/src/Form/QuestionForm.php` | Form | U-014 |

---

## lms_progress — G-PROGRESS, G-HARDENING

Units: U-021, U-022, U-023, U-024, U-059

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| UserCourse.php | `web/modules/custom/lms_progress/src/Entity/UserCourse.php` | Entity | U-021 |
| UserLesson.php | `web/modules/custom/lms_progress/src/Entity/UserLesson.php` | Entity | U-022 |
| UserPortalController.php | `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` | Controller | U-023, U-024 |
| ProgressApiController.php | `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php` | Controller | U-022, U-059 |
| lms-dashboard.html.twig | `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig` | Template | U-024 |
| lms-lesson-view.html.twig | `web/modules/custom/lms_progress/templates/lms-lesson-view.html.twig` | Template | U-022 |
| lms-my-courses.html.twig | `web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` | Template | U-021 |
| lms-my-certificates.html.twig | `web/modules/custom/lms_progress/templates/lms-my-certificates.html.twig` | Template | U-035 |

---

## lms_order — G-COMMERCE

Units: U-025, U-034

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Order.php | `web/modules/custom/lms_order/src/Entity/Order.php` | Entity | U-025 |
| OrderItem.php | `web/modules/custom/lms_order/src/Entity/OrderItem.php` | Entity | U-025 |
| OrderAccessControlHandler.php | `web/modules/custom/lms_order/src/Access/OrderAccessControlHandler.php` | Access Handler | U-025 |
| OrderListBuilder.php | `web/modules/custom/lms_order/src/OrderListBuilder.php` | List Builder | U-025 |
| CheckoutController.php | `web/modules/custom/lms_order/src/Controller/CheckoutController.php` | Controller | U-027, U-034 |
| CheckoutForm.php | `web/modules/custom/lms_order/src/Form/CheckoutForm.php` | Form | U-027 |
| MembershipCheckoutForm.php | `web/modules/custom/lms_order/src/Form/MembershipCheckoutForm.php` | Form | U-026 |
| BankTransferForm.php | `web/modules/custom/lms_order/src/Form/BankTransferForm.php` | Form | U-027 |
| lms-checkout.html.twig | `web/modules/custom/lms_order/templates/lms-checkout.html.twig` | Template | U-027 |
| lms-bank-transfer.html.twig | `web/modules/custom/lms_order/templates/lms-bank-transfer.html.twig` | Template | U-027 |

---

## lms_membership — G-COMMERCE

Units: U-026, U-033

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| MembershipPlan.php | `web/modules/custom/lms_membership/src/Entity/MembershipPlan.php` | Entity | U-026 |
| UserMembership.php | `web/modules/custom/lms_membership/src/Entity/UserMembership.php` | Entity | U-026 |
| MembershipManager.php | `web/modules/custom/lms_membership/src/MembershipManager.php` | Service | U-033 |
| MembershipController.php | `web/modules/custom/lms_membership/src/Controller/MembershipController.php` | Controller | U-026 |
| MembershipAdminController.php | `web/modules/custom/lms_membership/src/Controller/MembershipAdminController.php` | Controller | U-033 |
| MembershipAccessSubscriber.php | `web/modules/custom/lms_membership/src/EventSubscriber/MembershipAccessSubscriber.php` | Event Subscriber | U-033 |
| MembershipPlanListBuilder.php | `web/modules/custom/lms_membership/src/MembershipPlanListBuilder.php` | List Builder | U-026 |
| UserMembershipListBuilder.php | `web/modules/custom/lms_membership/src/UserMembershipListBuilder.php` | List Builder | U-026 |
| lms-my-membership.html.twig | `web/modules/custom/lms_membership/templates/lms-my-membership.html.twig` | Template | U-026 |
| lms-admin-pricing.html.twig | `web/modules/custom/lms_membership/templates/lms-admin-pricing.html.twig` | Template | U-033 |
| lms-admin-user-memberships.html.twig | `web/modules/custom/lms_membership/templates/lms-admin-user-memberships.html.twig` | Template | U-033 |

---

## lms_payment — G-COMMERCE, G-HARDENING

Units: U-027, U-030, U-031, U-032, U-062

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| PaymentGatewayInterface.php (Plugin) | `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayInterface.php` | Plugin Interface | U-027 |
| PaymentGatewayBase.php | `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayBase.php` | Plugin Base | U-027 |
| PaymentGatewayManager.php | `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayManager.php` | Plugin Manager | U-027 |
| PaymentGateway.php (Annotation) | `web/modules/custom/lms_payment/src/Annotation/PaymentGateway.php` | Annotation | U-027 |
| PaymentGatewayInterface.php (Service) | `web/modules/custom/lms_payment/src/Service/PaymentGatewayInterface.php` | Service Interface | U-027 |
| PaymentManager.php | `web/modules/custom/lms_payment/src/Service/PaymentManager.php` | Service | U-030, U-062 |
| BankTransferGateway.php | `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/BankTransferGateway.php` | Gateway Plugin | U-031 |
| PayPalGateway.php (lms_payment) | `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` | Gateway Plugin | U-029 |
| ZaloPayGateway.php | `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/ZaloPayGateway.php` | Gateway Plugin | U-031 |
| PaymentSettingsForm.php | `web/modules/custom/lms_payment/src/Form/PaymentSettingsForm.php` | Form | U-030 |

**Note (FIND-002):** `lms_payment` contains a `PayPalGateway` stub duplicate alongside `lms_paypal`. This appears to be an incomplete migration — see FIND-002 in A10.

---

## lms_stripe — G-COMMERCE, G-HARDENING

Units: U-028, U-060

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| StripeGateway.php | `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php` | Gateway Plugin | U-028, U-060 |

**Note (FIND-001):** `StripeGateway` webhook handler is a stub — no signature verification. Target of U-060.

---

## lms_paypal — G-COMMERCE, G-HARDENING

Units: U-029, U-061

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| PayPalGateway.php | `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` | Gateway Plugin | U-029, U-061 |

**Note (FIND-002):** Entirely stub implementation. No routing entry. Target of U-061.

---

## lms_certificate — G-CERTIFICATE

Units: U-035, U-036, U-037, U-038

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| CertificateTemplate.php | `web/modules/custom/lms_certificate/src/Entity/CertificateTemplate.php` | Entity | U-035 |
| UserCertificate.php | `web/modules/custom/lms_certificate/src/Entity/UserCertificate.php` | Entity | U-035 |
| CertificateTemplateListBuilder.php | `web/modules/custom/lms_certificate/src/CertificateTemplateListBuilder.php` | List Builder | U-035 |
| CertificateAdminController.php | `web/modules/custom/lms_certificate/src/Controller/CertificateAdminController.php` | Controller | U-035, U-037 |
| CertificateManager.php | `web/modules/custom/lms_certificate/src/Service/CertificateManager.php` | Service | U-036 |
| CertificateGenerator.php | `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php` | Service | U-037 |
| CertificateEventListener.php | `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php` | Event Subscriber | U-036 |
| CertificateTemplateForm.php | `web/modules/custom/lms_certificate/src/Form/CertificateTemplateForm.php` | Form | U-035 |
| RevokeCertificateForm.php | `web/modules/custom/lms_certificate/src/Form/RevokeCertificateForm.php` | Form | U-035 |
| VerificationForm.php | `web/modules/custom/lms_certificate/src/Form/VerificationForm.php` | Form | U-038 |
| certificate-pdf.html.twig | `web/modules/custom/lms_certificate/templates/certificate-pdf.html.twig` | Template | U-037 |
| certificate-verification-result.html.twig | `web/modules/custom/lms_certificate/templates/certificate-verification-result.html.twig` | Template | U-038 |
| lms-admin-certificates.html.twig | `web/modules/custom/lms_certificate/templates/lms-admin-certificates.html.twig` | Template | U-035 |
| lms-admin-certificate-templates.html.twig | `web/modules/custom/lms_certificate/templates/lms-admin-certificate-templates.html.twig` | Template | U-035 |

---

## lms_notification — G-COMMUNICATION

Units: U-039, U-042, U-044

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Notification.php | `web/modules/custom/lms_notification/src/Entity/Notification.php` | Entity | U-039 |
| NotificationManager.php | `web/modules/custom/lms_notification/src/NotificationManager.php` | Service | U-042 |
| NotificationController.php | `web/modules/custom/lms_notification/src/Controller/NotificationController.php` | Controller | U-039 |
| NotificationEventSubscriber.php | `web/modules/custom/lms_notification/src/EventSubscriber/NotificationEventSubscriber.php` | Event Subscriber | U-042 |
| BroadcastForm.php | `web/modules/custom/lms_notification/src/Form/BroadcastForm.php` | Form | U-042 |
| NotificationPreferencesForm.php | `web/modules/custom/lms_notification/src/Form/NotificationPreferencesForm.php` | Form | U-039 |
| NotificationBellBlock.php | `web/modules/custom/lms_notification/src/Plugin/Block/NotificationBellBlock.php` | Block Plugin | U-039 |
| LmsNotificationCommands.php | `web/modules/custom/lms_notification/src/Commands/LmsNotificationCommands.php` | Drush Command | U-044 |
| lms-notifications.html.twig | `web/modules/custom/lms_notification/templates/lms-notifications.html.twig` | Template | U-039 |
| lms-notification-bell.html.twig | `web/modules/custom/lms_notification/templates/lms-notification-bell.html.twig` | Template | U-039 |

---

## lms_forum — G-COMMUNICATION

Units: U-040, U-043

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| ForumTopic.php | `web/modules/custom/lms_forum/src/Entity/ForumTopic.php` | Entity | U-040 |
| ForumReply.php | `web/modules/custom/lms_forum/src/Entity/ForumReply.php` | Entity | U-040 |
| LmsForumController.php | `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` | Controller | U-043 |
| ForumController.php | `web/modules/custom/lms_forum/src/Controller/ForumController.php` | Controller | U-040 |
| ForumTopicForm.php | `web/modules/custom/lms_forum/src/Form/ForumTopicForm.php` | Form | U-040 |
| ForumReplyForm.php | `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php` | Form | U-040 |
| ForumReplyDeleteForm.php | `web/modules/custom/lms_forum/src/Form/ForumReplyDeleteForm.php` | Form | U-040 |
| ForumTopicAccessControlHandler.php | `web/modules/custom/lms_forum/src/ForumTopicAccessControlHandler.php` | Access Handler | U-040 |
| ForumReplyAccessControlHandler.php | `web/modules/custom/lms_forum/src/ForumReplyAccessControlHandler.php` | Access Handler | U-040 |
| RouteSubscriber.php | `web/modules/custom/lms_forum/src/Routing/RouteSubscriber.php` | Route Subscriber | U-043 |
| lms-forum-index.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-index.html.twig` | Template | U-043 |
| lms-forum-topic.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-topic.html.twig` | Template | U-043 |
| lms-forum-topic-form.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-topic-form.html.twig` | Template | U-040 |
| lms-forum-new-topic-page.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-new-topic-page.html.twig` | Template | U-040 |
| lms-forum-edit-reply-page.html.twig | `web/modules/custom/lms_forum/templates/lms-forum-edit-reply-page.html.twig` | Template | U-040 |

---

## lms_rating — G-COMMUNICATION

Units: U-041

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Rating.php | `web/modules/custom/lms_rating/src/Entity/Rating.php` | Entity | U-041 |
| RatingController.php | `web/modules/custom/lms_rating/src/Controller/RatingController.php` | Controller | U-041 |
| RatingManagerController.php | `web/modules/custom/lms_rating/src/Controller/RatingManagerController.php` | Controller | U-041 |
| RatingForm.php | `web/modules/custom/lms_rating/src/Form/RatingForm.php` | Form | U-041 |
| RatingListBuilder.php | `web/modules/custom/lms_rating/src/RatingListBuilder.php` | List Builder | U-041 |
| RatingBlock.php | `web/modules/custom/lms_rating/src/Plugin/Block/RatingBlock.php` | Block Plugin | U-041 |

**Note (FIND-006):** `RatingController::toggleApprove` and `::toggleFeatured` exist in code but have no routes.

---

## lms_ai — G-INTEGRATION, G-HARDENING

Units: U-047, U-048, U-063

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| AiProvider.php (Annotation) | `web/modules/custom/lms_ai/src/Annotation/AiProvider.php` | Annotation | U-047 |
| AiProviderInterface.php | `web/modules/custom/lms_ai/src/Plugin/AiProviderInterface.php` | Plugin Interface | U-047 |
| AiProviderManager.php | `web/modules/custom/lms_ai/src/Plugin/AiProviderManager.php` | Plugin Manager | U-047 |
| OpenAiProvider.php | `web/modules/custom/lms_ai/src/Plugin/AiProvider/OpenAiProvider.php` | AI Plugin | U-047 |
| GeminiProvider.php | `web/modules/custom/lms_ai/src/Plugin/AiProvider/GeminiProvider.php` | AI Plugin | U-047 |
| PollinationsProvider.php | `web/modules/custom/lms_ai/src/Plugin/AiProvider/PollinationsProvider.php` | AI Plugin | U-047 |
| CourseBuilderService.php | `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php` | Service | U-048, U-063 |
| GoogleSlideBuilderService.php | `web/modules/custom/lms_ai/src/Service/GoogleSlideBuilderService.php` | Service | U-048 |
| ScormBuilderService.php | `web/modules/custom/lms_ai/src/Service/ScormBuilderService.php` | Service | U-048 |
| CourseContentGenerator.php | `web/modules/custom/lms_ai/src/Plugin/QueueWorker/CourseContentGenerator.php` | Queue Worker | U-048, U-063 |
| AiSettingsForm.php | `web/modules/custom/lms_ai/src/Form/AiSettingsForm.php` | Form | U-047 |
| AiEngineUserForm.php | `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php` | Form | U-047 |
| CourseBuilderForm.php | `web/modules/custom/lms_ai/src/Form/CourseBuilderForm.php` | Form | U-048 |
| AjaxQuizTrait.php | `web/modules/custom/lms_ai/src/Form/Traits/AjaxQuizTrait.php` | Form Trait | U-048 |
| AiPreviewController.php | `web/modules/custom/lms_ai/src/Controller/AiPreviewController.php` | Controller | U-047 |

**Note:** `scripts/` directory contains 14 standalone data-seeding PHP scripts (not unit-mapped; excluded per convention).

---

## lms_zoom — G-INTEGRATION

Units: U-045, U-049

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| ZoomService.php | `web/modules/custom/lms_zoom/src/Service/ZoomService.php` | Service | U-045 |
| ZoomMeetingManager.php | `web/modules/custom/lms_zoom/src/Service/ZoomMeetingManager.php` | Service | U-049 |
| ZoomAiService.php | `web/modules/custom/lms_zoom/src/Service/ZoomAiService.php` | Service | U-049 |
| ZoomController.php | `web/modules/custom/lms_zoom/src/Controller/ZoomController.php` | Controller | U-049 |
| ZoomSettingsForm.php | `web/modules/custom/lms_zoom/src/Form/ZoomSettingsForm.php` | Form | U-045 |
| ZoomJoinBlock.php | `web/modules/custom/lms_zoom/src/Plugin/Block/ZoomJoinBlock.php` | Block Plugin | U-049 |
| lms-zoom-web-player.html.twig | `web/modules/custom/lms_zoom/templates/lms-zoom-web-player.html.twig` | Template | U-049 |
| lms-zoom-join-block.html.twig | `web/modules/custom/lms_zoom/templates/lms-zoom-join-block.html.twig` | Template | U-049 |
| html--lms-zoom-web-player.html.twig | `web/modules/custom/lms_zoom/templates/html--lms-zoom-web-player.html.twig` | Template (layout) | U-049 |
| page--lms-zoom-web-player.html.twig | `web/modules/custom/lms_zoom/templates/page--lms-zoom-web-player.html.twig` | Template (layout) | U-049 |

---

## lms_analytics — G-INTEGRATION

Units: U-046, U-050

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| AnalyticsService.php | `web/modules/custom/lms_analytics/src/Service/AnalyticsService.php` | Service | U-046 |
| CourseReportManager.php | `web/modules/custom/lms_analytics/src/Service/CourseReportManager.php` | Service | U-050 |
| QuizAnalyticsManager.php | `web/modules/custom/lms_analytics/src/Service/QuizAnalyticsManager.php` | Service | U-050 |
| UserReportManager.php | `web/modules/custom/lms_analytics/src/Service/UserReportManager.php` | Service | U-050 |
| CourseReportController.php | `web/modules/custom/lms_analytics/src/Controller/CourseReportController.php` | Controller | U-050 |
| QuizReportController.php | `web/modules/custom/lms_analytics/src/Controller/QuizReportController.php` | Controller | U-050 |
| UserReportController.php | `web/modules/custom/lms_analytics/src/Controller/UserReportController.php` | Controller | U-050 |
| lms-analytics-course-report.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-course-report.html.twig` | Template | U-050 |
| lms-analytics-quiz-report-list.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-list.html.twig` | Template | U-050 |
| lms-analytics-quiz-report-detail.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-detail.html.twig` | Template | U-050 |
| lms-analytics-user-report.html.twig | `web/modules/custom/lms_analytics/templates/lms-analytics-user-report.html.twig` | Template | U-050 |

---

## lms_wishlist — G-ADDITIONAL

Units: U-051

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Wishlist.php | `web/modules/custom/lms_wishlist/src/Entity/Wishlist.php` | Entity | U-051 |
| WishlistController.php | `web/modules/custom/lms_wishlist/src/Controller/WishlistController.php` | Controller | U-051 |
| lms-my-wishlist.html.twig | `web/modules/custom/lms_wishlist/templates/lms-my-wishlist.html.twig` | Template | U-051 |

---

## lms_bundle — G-ADDITIONAL

Units: U-052

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| CourseBundle.php | `web/modules/custom/lms_bundle/src/Entity/CourseBundle.php` | Entity | U-052 |

**Note:** No controllers, forms, or templates found. Entity-only implementation.

---

## lms_note — G-ADDITIONAL

Units: U-053

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Note.php | `web/modules/custom/lms_note/src/Entity/Note.php` | Entity | U-053 |
| NoteController.php | `web/modules/custom/lms_note/src/Controller/NoteController.php` | Controller | U-053 |
| lms-my-notes.html.twig | `web/modules/custom/lms_note/templates/lms-my-notes.html.twig` | Template | U-053 |

---

## lms_badge — G-ADDITIONAL

Units: U-054

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Badge.php | `web/modules/custom/lms_badge/src/Entity/Badge.php` | Entity | U-054 |
| UserBadge.php | `web/modules/custom/lms_badge/src/Entity/UserBadge.php` | Entity | U-054 |

**Note:** No controllers, forms, templates, or routing found. Entity-only implementation.

---

## lms_faq — G-ADDITIONAL

Units: U-055

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| Faq.php | `web/modules/custom/lms_faq/src/Entity/Faq.php` | Entity | U-055 |

**Note:** No controllers, forms, templates, or routing found. Entity-only; managed via lms_course workspace routes.

---

## lms_blog — G-ADDITIONAL

Units: U-056

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| LmsArticle.php | `web/modules/custom/lms_blog/src/Entity/LmsArticle.php` | Entity | U-056 |
| LmsBlogController.php | `web/modules/custom/lms_blog/src/Controller/LmsBlogController.php` | Controller | U-056 |
| LmsArticleForm.php | `web/modules/custom/lms_blog/src/Form/LmsArticleForm.php` | Form | U-056 |
| LmsArticleListBuilder.php | `web/modules/custom/lms_blog/src/LmsArticleListBuilder.php` | List Builder | U-056 |
| LmsArticleAccessControlHandler.php | `web/modules/custom/lms_blog/src/Access/LmsArticleAccessControlHandler.php` | Access Handler | U-056 |
| lms-blog-list.html.twig | `web/modules/custom/lms_blog/templates/lms-blog-list.html.twig` | Template | U-056 |
| lms-blog-single.html.twig | `web/modules/custom/lms_blog/templates/lms-blog-single.html.twig` | Template | U-056 |

---

## lms_content_tools — Unmapped

Units: none assigned yet

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| ContentToolsController.php | `web/modules/custom/lms_content_tools/src/Controller/ContentToolsController.php` | Controller | — |
| ContentToolsManager.php | `web/modules/custom/lms_content_tools/src/Service/ContentToolsManager.php` | Service | — |
| CleanContentForm.php | `web/modules/custom/lms_content_tools/src/Form/CleanContentForm.php` | Form | — |
| SeedInputForm.php | `web/modules/custom/lms_content_tools/src/Form/SeedInputForm.php` | Form | — |
| ContentToolsCommands.php | `web/modules/custom/lms_content_tools/src/Commands/ContentToolsCommands.php` | Drush Command | — |

**Action needed:** Assign to a unit or create new unit (SA decision).

---

## open_switcher — Utility (not unit-mapped)

| File | Relative Path | Type | Primary Unit |
|------|--------------|------|--------------|
| SwitchController.php | `web/modules/custom/open_switcher/src/Controller/SwitchController.php` | Controller | — |
| SwitchFrontendController.php | `web/modules/custom/open_switcher/src/Controller/SwitchFrontendController.php` | Controller | — |
| SwitchUserForm.php | `web/modules/custom/open_switcher/src/Form/SwitchUserForm.php` | Form | — |
| open-switcher-page.html.twig | `web/modules/custom/open_switcher/templates/open-switcher-page.html.twig` | Template | — |

---

## Coverage Summary

| Stat | Value |
|------|-------|
| Total custom modules | 27 (26 lms_* + open_switcher) |
| Modules with src/ files mapped | 27 |
| Total PHP files mapped | ~120 |
| Total Twig templates mapped | ~60 |
| Fully unit-mapped modules | 25 |
| Unmapped modules | 2 (lms_content_tools, open_switcher) |
| Modules entity-only (no controllers/templates) | 3 (lms_bundle, lms_badge, lms_faq) |

---

*End of D07-code-map/per-module-files.md*
