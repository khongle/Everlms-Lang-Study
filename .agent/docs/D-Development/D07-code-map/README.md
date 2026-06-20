# D07-code-map - Code Map

Module → key source locations (under `web/modules/custom/<module>/`). Mirrors [D03 stories](../D03-stories/_index.md) and [C05 modules](../../C-Design/C05-modules/_index.md) by module. Update on file create/modify.

| Module | Key paths / classes |
| ------ | ------------------- |
| lms_base | `src/Commands/LmsDemoContentCommand.php`; shared services/base classes |
| open_switcher | `src/Controller/*` (switch/switch-back); `*.routing.yml` |
| lms_course | `src/Entity/Course.php`; `src/Controller/*` (catalog/detail/workspace, ~37 routes); `src/Form/*` |
| lms_chapter | `src/Entity/Chapter.php` |
| lms_lesson | `src/Entity/Lesson.php`; player controller; `field_h5p_upload` |
| lms_progress | `src/Entity/{UserCourse,UserLesson}.php`; progress + gating controllers; `/dashboard` |
| lms_question | `src/Entity/{Question,QuestionType,Answer,MatchingPair}.php` |
| lms_quiz | `src/Entity/{Quiz,QuizResult,QuestionResponse}.php`; **`src/Grading/GradingStrategyFactory.php`** + `QuestionGradingInterface`; `QuizPassedEvent` |
| lms_certificate | `src/Entity/{CertificateTemplate,UserCertificate}.php`; QuizPassedEvent subscriber; mPDF render |
| lms_badge | `src/Entity/{Badge,UserBadge}.php` (⚠️ no award subscriber) |
| lms_payment | `src/Plugin/PaymentGateway*` (`PaymentGatewayManager`, Base, Interface; BankTransfer/PayPal/ZaloPay) |
| lms_stripe | `src/Plugin/PaymentGateway/StripeGateway.php` |
| lms_paypal | `src/Plugin/PaymentGateway/PayPalGateway.php` (⚠️ duplicate — R-004) |
| lms_order | `src/Entity/{Order,OrderItem}.php`; checkout controllers |
| lms_bundle | `src/Entity/CourseBundle.php` |
| lms_membership | `src/Entity/{MembershipPlan,UserMembership}.php` |
| lms_forum | `src/Entity/{ForumTopic,ForumReply}.php` |
| lms_rating | `src/Entity/Rating.php` |
| lms_note | `src/Entity/Note.php`; AJAX `/api/lms/note/*` |
| lms_wishlist | `src/Entity/Wishlist.php`; AJAX `/api/lms/wishlist/*` |
| lms_blog | `src/Entity/LmsArticle.php`; `/news`, `/manage/blog` |
| lms_faq | `src/Entity/Faq.php` |
| lms_zoom | Zoom client service; `/zoom/*`, websdk signature |
| lms_notification | `src/Entity/Notification.php`; `src/Commands/LmsNotificationCommands.php`; events + cron |
| lms_ai | `src/Plugin/AiProvider/{OpenAi,Gemini,Pollinations}Provider.php` + `AiProviderManager`; `CourseContentGenerator.php` |
| lms_mobile_api | `src/Controller/{MobileAuth,MobileCourse,MobileLearning}Controller.php`; `src/Service/OtpService.php`; `/api/v1/*` |
| lms_analytics | report controllers; `/admin/lms/reports/*` |
| lms_content_tools | `src/Service/ContentToolsManager.php`; `src/Controller/ContentToolsController.php`; `src/Form/SeedInputForm.php`; `src/Commands/ContentToolsCommands.php` |
| lms_theme (theme) | `web/themes/custom/lms_theme/` — `*.info.yml` (16 regions), `*.libraries.yml` (21), `*.theme`, `templates/`, `scss/` |

Per-module detail files: [lms_base](lms_base.md) · [open_switcher](open_switcher.md) · [lms_course](lms_course.md) · [lms_chapter](lms_chapter.md) · [lms_lesson](lms_lesson.md) · [lms_progress](lms_progress.md) · [lms_question](lms_question.md) · [lms_quiz](lms_quiz.md) (others: see table above + [C05](../../C-Design/C05-modules/_index.md)).
