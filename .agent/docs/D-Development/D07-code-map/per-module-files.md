# D07 — Per-Module Source Files

**Purpose:** For each of the 28 custom modules, list its key `src/` files with type, path (repo-relative), and implementation status.

**Repo root:** `/home/hainn/projects/everlms`  
**Module base:** `web/modules/custom/{module}/`  
**Last verified:** 2026-06-20

---

## Legend

| Type | Meaning |
|------|---------|
| Entity | `src/Entity/*.php` — content or config entity class |
| Controller | `src/Controller/*.php` |
| Service | `src/Service/*.php` or top-level `src/*.php` service class |
| Form | `src/Form/*.php` |
| Plugin | `src/Plugin/**/*.php` |
| EventSub | `src/EventSubscriber/*.php` |
| Access | `src/Access/*.php` |
| Command | `src/Commands/*.php` |
| ListBuilder | `src/*ListBuilder.php` |
| Routing | `src/Routing/*.php` |
| Block | `src/Plugin/Block/*.php` |

Status tags: **as-built** / **stub** / **dead-code** / **bug** — see README.

---

## lms_base

Module path: `web/modules/custom/lms_base/`  
Role: Foundation — shared services, taxonomy install, email-login patch, demo-content Drush command.  
Routes: none (no routing.yml).

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Service/LmsHelper.php` | Service | as-built | `lms_base.helper` — 6 utility methods: getCurrentUserId, currentUserHasRole, getTermsByVocabulary, getTermName, formatDuration, generateSlug |
| `src/Commands/LmsDemoContentCommand.php` | Command | as-built | Drush `lms:generate-demo` — creates demo Course/Chapter/Lesson entities |
| `lms_base.module` | Module hooks | as-built | hook_user_insert (auto-assign student role), hook_form_alter (email login), hook_user_login |
| `lms_base.install` | Install | as-built | hook_install creates 8 taxonomy vocabularies + default terms |

---

## lms_content_tools

Module path: `web/modules/custom/lms_content_tools/`  
Role: Admin content management — clean/seed data forms, AI-driven import pipeline via Drush.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Controller/ContentToolsController.php` | Controller | as-built | `dashboard()` — renders content-tools hub at `/workspace/content-tools` |
| `src/Form/CleanContentForm.php` | Form | as-built | 3-step destruction wizard; step-2 requires typing "CLEAN"; calls ContentToolsManager |
| `src/Form/SeedInputForm.php` | Form | as-built | 6-section AJAX form; saves to `.agent/seed/seed-input.json` |
| `src/Service/ContentToolsManager.php` | Service | as-built | `lms_content_tools.manager` — 21 entity group registry; delete/count/seed-load methods |
| `src/Commands/ContentToolsCommands.php` | Command | as-built | Drush `everlms:clean`, `everlms:seed-input`, `everlms:import`, `everlms:mock-certs` (~2531 lines) |
| `src/Blueprints/` (10 HTML files) | Blueprint | as-built | Certificate template HTML layouts injected with placeholders |

---

## lms_course

Module path: `web/modules/custom/lms_course/`  
Role: Course entity + all public learner pages + the entire `/workspace` admin shell.  
Routes: 12 public + 24 workspace/admin (see [route-to-controller.md](route-to-controller.md)).

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Course.php` | Entity | as-built | `lms_course` — 22 base fields; `field_price_type` allowed: free/paid/membership |
| `src/Controller/CoursePublicController.php` | Controller | as-built | 8 public methods: homepage, catalog, searchApi, detail, categories, categoryDetail, glossary, instructorProfile |
| `src/Controller/AdminController.php` | Controller | as-built | 14 workspace methods; `quizList()` has hardcoded `pending_grading=5` (mock) |
| `src/Controller/CourseEnrollmentController.php` | Controller | as-built | `enrollFree()` — validates free course, creates lms_user_course |
| `src/Controller/PageController.php` | Controller | as-built | `about()`, `contact()`, `pricing()` — static marketing pages; About team data is hardcoded |
| `src/Controller/WorkspaceContentController.php` | Controller | as-built | CRUD wrapper for node bundles + lms_faq in workspace |
| `src/Form/CourseForm.php` | Form | as-built | ContentEntityForm; 5 form modes (default, add, edit, manager_edit, admin_edit) |
| `src/Form/ApproveOrderForm.php` | Form | as-built | Order status + receipt review form; embedded by AdminController::orderEditForm |
| `src/Access/CourseAccessControlHandler.php` | Access | as-built | view/update/delete/create access per lms_course permissions |
| `src/Breadcrumb/LmsCourseBreadcrumbBuilder.php` | Service | as-built | `lms_course.breadcrumb` — Home > Courses > [term/course name] |
| `src/CourseListBuilder.php` | ListBuilder | as-built | Admin list: Name, Category, Level, Price, Status |

---

## lms_chapter

Module path: `web/modules/custom/lms_chapter/`  
Role: Chapter entity only. No routes, no services, no hooks.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Chapter.php` | Entity | as-built | `lms_chapter` — fields: name, field_description, field_lessons (→lms_lesson, unlimited), weight, status |
| `src/Form/ChapterForm.php` | Form | as-built | Standard ContentEntityForm wrapper |
| `src/Access/ChapterAccessControlHandler.php` | Access | as-built | Delegates to lms_course permission set |

---

## lms_lesson

Module path: `web/modules/custom/lms_lesson/`  
Role: Lesson entity + SCORM extraction hook. No routes, no services.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Lesson.php` | Entity | as-built | `lms_lesson` — 18 fields; `field_type` allowed: video/text/quiz/h5p/scorm/pdf/pptx/zoom/mixed |
| `src/Form/LessonForm.php` | Form | as-built | Form API `#states` conditional visibility per lesson type |
| `src/Access/LessonAccessControlHandler.php` | Access | as-built | Delegates to lms_course permission set |
| `lms_lesson.module` | Module hooks | as-built | hook_entity_insert/update call `_lms_lesson_extract_scorm()` — PHP ZipArchive, reads imsmanifest.xml |

---

## lms_blog

Module path: `web/modules/custom/lms_blog/`  
Role: Dual-track blog — `lms_article` entity for admin CRUD; `node:blog` for public frontend.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/LmsArticle.php` | Entity | as-built | `lms_article` — fields: title, content, thumbnail, seo_title, seo_description, status; **no author/uid field** |
| `src/Controller/LmsBlogController.php` | Controller | as-built | `index()` queries `node:blog` (NOT lms_article); `view()` renders single node as lms_blog_single |
| `src/Form/LmsArticleForm.php` | Form | as-built | Optional AI Blog Writer widget if lms_ai is available; AJAX content generation |
| `src/Access/LmsArticleAccessControlHandler.php` | Access | as-built | Own/any edit/delete pattern |
| `src/LmsArticleListBuilder.php` | ListBuilder | as-built | Admin list columns: ID, Title, Status, Created |

**Architecture note:** The public `/news` routes read `node:blog` (core node storage), not `lms_article`. The `lms_article` entity backs only `/manage/blog` admin CRUD. See [C05 lms_blog spec](../../C-Design/C05-modules/lms_blog.md).

---

## lms_faq

Module path: `web/modules/custom/lms_faq/`  
Role: FAQ entity only. No routes, no services, no hooks.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Faq.php` | Entity | as-built | `lms_faq` — fields: question (label), field_answer, field_scope (global/course/lesson), field_course, field_category, weight. **No status field.** |

CRUD routes auto-generated by AdminHtmlRouteProvider. Workspace management via `lms_course`'s WorkspaceContentController.

---

## lms_question

Module path: `web/modules/custom/lms_question/`  
Role: Question bank — 3 content entities + 1 config entity (bundles). No routes, no services, no hooks.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Question.php` | Entity | as-built | `lms_question` — 13 base fields covering all 8 bundle types on one entity; no per-bundle field separation |
| `src/Entity/QuestionType.php` | Entity | as-built | `lms_question_type` — config entity/bundle; 8 installed: essay, fill_blank, matching, multiple_choice, ordering, short_answer, single_choice, true_false |
| `src/Entity/Answer.php` | Entity | as-built | `lms_answer` — fields: name (answer text), field_body, field_correct |
| `src/Entity/MatchingPair.php` | Entity | as-built | `lms_matching_pair` — fields: field_left_item, field_right_item, weight |
| `src/Form/QuestionForm.php` | Form | as-built | Minimal ContentEntityForm wrapper; `save()` redirects to collection |
| `src/Access/QuestionAccessControlHandler.php` | Access | as-built | view→`view lms_course`; update/delete→`edit lms_course`; create→`create lms_course` |
| `config/install/` (8 YML files) | Config | as-built | Installs 8 `lms_question_type` config entities |

---

## lms_quiz

Module path: `web/modules/custom/lms_quiz/`  
Role: Quiz engine — quiz taking, grading (static factory + 8 strategies), results, event dispatch.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Quiz.php` | Entity | as-built | `lms_quiz` — 11 base fields; `field_prerequisite_quizzes` added by update_10001 |
| `src/Entity/QuizResult.php` | Entity | as-built | `lms_quiz_result` — field_start_time is hardcoded to `now-60` (bug) |
| `src/Entity/QuestionResponse.php` | Entity | as-built | `lms_question_response` — no entity links / admin UI; managed programmatically |
| `src/Controller/QuizController.php` | Controller | as-built | takeQuiz, submitQuiz, viewResult, myResults |
| `src/Form/QuizForm.php` | Form | as-built | Minimal wrapper; 7 form op variants all use same class |
| `src/Access/QuizAccessControlHandler.php` | Access | as-built | Same delegation pattern as Question |
| `src/Event/QuizPassedEvent.php` | Event | as-built | `lms_quiz.passed` — dispatched on pass; `QuizPassedEvent::EVENT_NAME` |
| `src/Grading/QuestionGradingInterface.php` | Interface | as-built | `grade()` + `getMaxScore()` |
| `src/Grading/GradingStrategyFactory.php` | Service | as-built | Static PHP factory; `match` on bundle string → strategy instance |
| `src/Grading/Strategy/SingleChoiceStrategy.php` | Service | as-built | Binary grading; checks single correct choice |
| `src/Grading/Strategy/MultipleChoiceStrategy.php` | Service | as-built | Strict exact-match of correct IDs set; no partial credit |
| `src/Grading/Strategy/TrueFalseStrategy.php` | Service | as-built | Compares bool string |
| `src/Grading/Strategy/FillBlankStrategy.php` | Service | as-built | Trimmed text match; respects `field_case_sensitive` |
| `src/Grading/Strategy/ShortAnswerStrategy.php` | Service | as-built | Same logic as FillBlank |
| `src/Grading/Strategy/OrderingStrategy.php` | Service | as-built | Strict array order comparison |
| `src/Grading/Strategy/MatchingStrategy.php` | Service | as-built | All pairs must match |
| `src/Grading/Strategy/EssayStrategy.php` | Service | as-built | Always pending_review=TRUE; manual grading required |

**Architecture note:** Grading is a **static PHP factory + strategy pattern**, NOT a Drupal Plugin API system. No `src/Plugin/` directory in either lms_question or lms_quiz.

---

## lms_progress

Module path: `web/modules/custom/lms_progress/`  
Role: Enrollment + progress tracking; learner portal pages.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/UserCourse.php` | Entity | as-built | `lms_user_course` — field_progress (decimal %), field_status (enrolled/in_progress/completed) |
| `src/Entity/UserLesson.php` | Entity | as-built | `lms_user_lesson` — field_status, field_video_position |
| `src/Controller/UserPortalController.php` | Controller | as-built | dashboard, myCourses, lessonView, myCertificates, profile; dashboard/myCourses duplicate "next lesson" logic |
| `src/Controller/ProgressApiController.php` | Controller | as-built | `markComplete()` — upserts lms_user_lesson, recalculates course progress %, returns JSON |
| `lms_progress.module` | Module hooks | bug | `lms_progress_comment_form_submit_redirect()` returns undefined `$response`; hook_form_alter saves Role entity at form-alter time (security/perf concern) |

---

## lms_note

Module path: `web/modules/custom/lms_note/`  
Role: Student notes on lessons — AJAX save/delete/get + My Notes page.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Note.php` | Entity | as-built | `lms_note` — fields: field_course (added by update_10001), field_lesson, field_body, field_video_timestamp |
| `src/Controller/NoteController.php` | Controller | as-built | save (POST JSON), delete (POST JSON), getNote (GET), myNotes (HTML page) |

---

## lms_forum

Module path: `web/modules/custom/lms_forum/`  
Role: Community forum — threaded replies, best-answer, route override.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/ForumTopic.php` | Entity | as-built | `lms_forum_topic` — fields: name, field_body, field_course, field_replies_count, field_pinned, field_locked, field_category, field_resolved |
| `src/Entity/ForumReply.php` | Entity | as-built | `lms_forum_reply` — fields: field_topic, field_body, field_parent, field_best_answer, field_upvotes |
| `src/Controller/LmsForumController.php` | Controller | as-built | Active controller; `viewTopic()` has debug `addMessage("FRESH CONTROLLER LOADED!")` — **bug** |
| `src/Controller/ForumController.php` | Controller | dead-code | `viewTopic()` opens with `throw new \Exception("HARD DEBUG!")` — never routed; **dangerous dead code** |
| `src/Form/ForumTopicForm.php` | Form | as-built | Hides internal fields from users; redirects to canonical on save |
| `src/Form/ForumReplyForm.php` | Form | as-built | AJAX modal save; triggers lms_notification on reply |
| `src/Form/ForumReplyDeleteForm.php` | Form | as-built | AJAX delete with DOM removal command |
| `src/Routing/RouteSubscriber.php` | Routing | as-built | `lms_forum.route_subscriber` — overrides `entity.lms_forum_topic.canonical` controller to `LmsForumController::viewTopic` |
| `src/Access/ForumTopicAccessControlHandler.php` | Access | as-built | view→access content; update/delete→auth+owner; admin always allowed |
| `src/Access/ForumReplyAccessControlHandler.php` | Access | as-built | Identical pattern |

---

## lms_rating

Module path: `web/modules/custom/lms_rating/`  
Role: Course ratings and reviews; workspace moderation.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Rating.php` | Entity | as-built | `lms_rating` — field_course, field_score (1-5), field_review, field_approved, field_is_featured (update_11001) |
| `src/Controller/RatingManagerController.php` | Controller | as-built | `list()` renders `lms_admin_ratings` (theme defined in lms_course.module hook_theme); `executeAction()` handles approve/feature/delete |
| `src/Controller/RatingController.php` | Controller | bug | `toggleApprove()` and `toggleFeatured()` redirect to `lms_rating.collection` — **route does not exist** (RouteNotFoundException) |
| `src/Form/RatingForm.php` | Form | as-built | Learner rating submission; duplicate prevention; field_approved=FALSE on create |
| `src/RatingListBuilder.php` | ListBuilder | bug | `getOperations()` generates links to `lms_rating.toggle_approve` and `lms_rating.toggle_featured` — **both routes missing** |
| `src/Plugin/Block/RatingBlock.php` | Block | as-built | Plugin id: `lms_rating_block`; renders RatingForm |

**Known issue:** 3 missing routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) will cause `RouteNotFoundException` if RatingListBuilder operations or RatingController redirects are triggered.

---

## lms_notification

Module path: `web/modules/custom/lms_notification/`  
Role: In-app notifications (complete); email and push are stubs.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Notification.php` | Entity | as-built | `lms_notification` — field_type (10 values), field_channel (in_app/email/push), field_title, field_message, field_link, field_read, field_reference_type, field_reference_id |
| `src/NotificationManager.php` | Service | as-built (in_app) / stub (email, push) | `lms_notification.manager` — `dispatch()` routes to `createInAppNotification()` (real); email channel logs + TODO comment; push logs only |
| `src/Controller/NotificationController.php` | Controller | as-built | `listNotifications()` marks read on visit; `apiUnreadCount()` returns JSON count |
| `src/Form/BroadcastForm.php` | Form | as-built | Batch sends announcement to all active users |
| `src/Form/NotificationPreferencesForm.php` | Form | as-built | Per-user channel prefs (in_app + email) via user.data |
| `src/EventSubscriber/NotificationEventSubscriber.php` | EventSub | as-built | Subscribes to `lms_quiz.passed` → sends quiz_result notification |
| `src/Plugin/Block/NotificationBellBlock.php` | Block | as-built | `lms_notification_bell_block` — unread count + top 5 notifications |
| `src/Commands/LmsNotificationCommands.php` | Command | as-built | Drush `lms_notification:remind-inactive` — sends reminder to 7-14 day inactive users |

---

## lms_order

Module path: `web/modules/custom/lms_order/`  
Role: Checkout flow for courses and membership plans.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Order.php` | Entity | as-built | `lms_order` — 14 fields; field_payment_receipt added by update_10001 |
| `src/Entity/OrderItem.php` | Entity | as-built | `lms_order_item` — field_course, field_membership_plan, field_price, field_type |
| `src/Controller/CheckoutController.php` | Controller | as-built | checkout, checkoutMembership, bankTransferInstruction; minor bug: `createAndFulfillFreeMembershipOrder` calls `$order->set('field_items')` twice |
| `src/Form/CheckoutForm.php` | Form | bug | Creates Stripe PaymentIntent in `buildForm()` (one per page load, not per submit) |
| `src/Form/MembershipCheckoutForm.php` | Form | bug | Same Stripe PaymentIntent-at-build-time issue |
| `src/Form/BankTransferForm.php` | Form | as-built | Receipt upload (managed_file); redirects to front |
| `src/Access/OrderAccessControlHandler.php` | Access | as-built | View own orders only; administer lms_order for admin |
| `src/OrderListBuilder.php` | ListBuilder | as-built | Columns: ID, Order Number, Customer, Total, Payment Method, Status |

---

## lms_membership

Module path: `web/modules/custom/lms_membership/`  
Role: Membership plan CRUD + user subscriptions + cron expiry.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/MembershipPlan.php` | Entity | as-built | `lms_membership_plan` — field_price, field_duration_days, field_billing_cycle, field_features, field_course_access, field_trial_days, field_recommended, status |
| `src/Entity/UserMembership.php` | Entity | as-built | `lms_user_membership` — field_plan, field_start_date, field_end_date, field_status, field_auto_renew |
| `src/MembershipManager.php` | Service | as-built | `lms_membership.manager` — `hasActiveMembership()` — canonical access check |
| `src/Controller/MembershipController.php` | Controller | as-built | myMembership, cancelMembership (sets field_auto_renew=FALSE; does not expire immediately) |
| `src/Controller/MembershipAdminController.php` | Controller | as-built | pricingList, pricingAddForm, pricingEditForm, toggleStatus, userMembershipsList, userMembershipAddForm, userMembershipEditForm |
| `src/EventSubscriber/MembershipAccessSubscriber.php` | EventSub | as-built | `lms_membership.access_subscriber` — redirects 403 on `/learn/` or `/course/` to pricing page |

---

## lms_payment

Module path: `web/modules/custom/lms_payment/`  
Role: Payment gateway plugin host — `@PaymentGateway` annotation, manager, 3 built-in gateways.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Plugin/PaymentGatewayManager.php` | Service | as-built | `plugin.manager.payment_gateway` — scans `Plugin/PaymentGateway/` for `@PaymentGateway` annotations |
| `src/Annotation/PaymentGateway.php` | Plugin | as-built | Annotation class: id, label, description |
| `src/Plugin/PaymentGatewayInterface.php` | Interface | as-built | Plugin interface: processPayment, handleWebhook, getLabel |
| `src/Plugin/PaymentGatewayBase.php` | Plugin | stub | `processPayment()` returns `status=failed`; `handleWebhook()` returns 500 — both are default stubs |
| `src/Plugin/PaymentGateway/BankTransferGateway.php` | Plugin | as-built | processPayment → status=pending, MANUAL transaction ID; handleWebhook → no-op 200 |
| `src/Plugin/PaymentGateway/PayPalGateway.php` | Plugin | stub | No server-side PayPal API call; accepts any paypal_order_id |
| `src/Plugin/PaymentGateway/ZaloPayGateway.php` | Plugin | stub | Hardcoded sandbox URL; no HMAC verification |
| `src/Form/PaymentSettingsForm.php` | Form | as-built | Config for all gateway keys in `lms_payment.settings`; **secrets stored in config YAML — security risk** |
| `src/Service/PaymentGatewayInterface.php` | Interface | as-built | **Second separate interface** (not the plugin one); only implemented by lms_paypal (dead) |
| `src/Service/PaymentManager.php` | Service | dead-code | Registry class; NOT registered in services.yml — never instantiated |

---

## lms_stripe

Module path: `web/modules/custom/lms_stripe/`  
Role: Stripe gateway plugin — retrieves PaymentIntent, checks `status === 'succeeded'`.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Plugin/PaymentGateway/StripeGateway.php` | Plugin | as-built (partial) | `processPayment()` calls `\Stripe\PaymentIntent::retrieve()` via stripe/stripe-php SDK; `handleWebhook()` is a **stub** — no signature verification (TODO comment) |

---

## lms_paypal

Module path: `web/modules/custom/lms_paypal/`  
Role: Dead-code module — PayPal gateway class that is never discovered.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Plugin/PaymentGateway/PayPalGateway.php` | Plugin | dead-code | Implements wrong interface (`Service\PaymentGatewayInterface`); no `@PaymentGateway` annotation; never instantiated by plugin manager |

---

## lms_wishlist

Module path: `web/modules/custom/lms_wishlist/`  
Role: Course wishlist — toggle/status JSON API + My Wishlist page.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Wishlist.php` | Entity | as-built | `lms_wishlist` — field_course (required), uid, created |
| `src/Controller/WishlistController.php` | Controller | as-built | toggle (POST), getStatus (GET), myWishlist (HTML); proper cache invalidation via tag `lms_wishlist:{uid}` |

---

## lms_bundle

Module path: `web/modules/custom/lms_bundle/`  
Role: Course bundle entity only. No routes, controllers, services, or checkout integration.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/CourseBundle.php` | Entity | as-built | `lms_course_bundle` — name, field_courses (unlimited), field_price, field_discount_percent, field_thumbnail, status |

**Status: minimal scaffold.** Entity CRUD via auto-generated Drupal admin routes only. No checkout flow. `lms_order_item.field_type` allows `bundle` as a value but no code creates bundle order items. See [C05 lms_bundle spec](../../C-Design/C05-modules/lms_bundle.md).

---

## lms_certificate

Module path: `web/modules/custom/lms_certificate/`  
Role: Certificate auto-issuance on quiz pass (event subscriber) + admin management.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/CertificateTemplate.php` | Entity | as-built | `lms_certificate_template` — field_body (tokens), field_design, field_background, field_signature, field_logo, field_seal, field_orientation, field_layout (raw Twig HTML), field_color_scheme, status |
| `src/Entity/UserCertificate.php` | Entity | as-built | `lms_user_certificate` — field_template, field_quiz_result, field_course, field_certificate_number, field_pdf_file, field_issued_at, field_expiry_date (**varchar not int — bug**), field_revoked, field_revoke_reason |
| `src/Service/CertificateGenerator.php` | Service | as-built | `lms_certificate.generator` — renders `certificate_pdf` theme to HTML, generates PDF via mPDF; degrades gracefully if mPDF missing |
| `src/Service/CertificateManager.php` | Service | as-built | `lms_certificate.manager` — checks for existing cert, finds template, calls generator, saves UserCertificate entity |
| `src/EventSubscriber/CertificateEventListener.php` | EventSub | as-built | `lms_certificate.event_listener` — subscribes to `lms_quiz.passed`; traverses Quiz→Lesson→Chapter→Course to find course; calls CertificateManager |
| `src/Controller/CertificateAdminController.php` | Controller | as-built | adminDashboard, adminTemplates, templateAddForm, templateEditForm, templatePreview (inline PDF), regenerateCertificate |
| `src/Form/RevokeCertificateForm.php` | Form | as-built | ConfirmFormBase; sets field_revoked=1 + reason |
| `src/Form/VerificationForm.php` | Form | as-built | Public code lookup; renders certificate_verification_result in same form |
| `src/CertificateTemplateListBuilder.php` | ListBuilder | as-built | Admin template list |

---

## lms_badge

Module path: `web/modules/custom/lms_badge/`  
Role: Gamification badges — entity scaffold only; zero award logic.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Entity/Badge.php` | Entity | as-built | `lms_badge` — name, field_description, field_icon, field_type (taxonomy_term), field_xp_reward, field_criteria (JSON string — format documented but not evaluated) |
| `src/Entity/UserBadge.php` | Entity | as-built | `lms_user_badge` — uid, field_badge, field_awarded_at |

**Status: near-stub.** No services, routes, controllers, forms, or hooks. No code evaluates `field_criteria` or awards badges. See [C05 lms_badge spec](../../C-Design/C05-modules/lms_badge.md).

---

## lms_zoom

Module path: `web/modules/custom/lms_zoom/`  
Role: Zoom API integration — meeting lifecycle, WebSDK join, AI agenda, AI summaries (VTT stub).

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Service/ZoomService.php` | Service | as-built | `lms_zoom.service` — Server-to-Server OAuth token cache; createMeeting, getMeeting, deleteMeeting, getRecordings, getZakToken |
| `src/Service/ZoomMeetingManager.php` | Service | as-built | `lms_zoom.meeting_manager` — syncMeeting (traverses Lesson→Chapter→Course→field_instructor); deleteMeeting |
| `src/Service/ZoomAiService.php` | Service | as-built (partial) | `lms_zoom.ai_service` — generateAgenda (real); processSummaries VTT download is a **stub** (hardcoded simulation string) |
| `src/Controller/ZoomController.php` | Controller | as-built | joinMeeting (renders WebSDK page); generateSignature (HS256 JWT via firebase/php-jwt) |
| `src/Form/ZoomSettingsForm.php` | Form | as-built | Config for account_id, client_id, client_secret, sdk_key, sdk_secret |
| `src/Plugin/Block/ZoomJoinBlock.php` | Block | as-built | `lms_zoom_join_block` — shows join button + countdown; context: entity:lms_lesson |

**Known issue:** `generateSignature()` references wrong service ID (see DOC-BRIEF.md). See [C05 lms_zoom spec](../../C-Design/C05-modules/lms_zoom.md).

---

## lms_ai

Module path: `web/modules/custom/lms_ai/`  
Role: AI provider plugin system (OpenAI/Gemini/Pollinations) + course builder + multi-step AI engine admin form.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Plugin/AiProviderManager.php` | Service | as-built | `plugin.manager.lms_ai_provider` — scans `Plugin/AiProvider/` for `@AiProvider` annotations |
| `src/Annotation/AiProvider.php` | Plugin | as-built | Annotation: id, label, description |
| `src/Plugin/AiProviderInterface.php` | Interface | as-built | `generateJson()`, `generateImage()`; note: `generateText()` is implemented but NOT in interface |
| `src/Plugin/AiProvider/OpenAiProvider.php` | Plugin | as-built | GPT-4o JSON/text/image (DALL-E 3); configurable model; 60/90s timeouts |
| `src/Plugin/AiProvider/GeminiProvider.php` | Plugin | as-built | gemini-2.5-flash JSON/text + Imagen 4.0 image; strips markdown fences from JSON responses |
| `src/Plugin/AiProvider/PollinationsProvider.php` | Plugin | as-built (image only) | Free service; generateJson/generateText log warning + return NULL; image → Flux model |
| `src/Service/CourseBuilderService.php` | Service | as-built | `lms_ai.course_builder` — generates Course+Lesson scaffold via OpenAI; queues lesson content generation; **no rollback on partial failure** |
| `src/Service/ScormBuilderService.php` | Service | as-built | `lms_ai.scorm_builder` — generates SCORM 1.2 packages via PHP ZipArchive; two methods: full course and per-lesson slides |
| `src/Service/GoogleSlideBuilderService.php` | Service | as-built | `lms_ai.google_slide_builder` — duplicates Google Slides template, batch-updates content; requires google/apiclient |
| `src/Plugin/QueueWorker/CourseContentGenerator.php` | Plugin | as-built | Queue worker `lms_ai_content_generator` — calls OpenAI to fill lesson content; cron time 60s |
| `src/Controller/AiPreviewController.php` | Controller | as-built | `previewUsersJson()` — opens JSON/ai_users.json in modal |
| `src/Form/AiSettingsForm.php` | Form | as-built | Config for default_provider, API keys + models for OpenAI + Gemini |
| `src/Form/CourseBuilderForm.php` | Form | as-built | AJAX form: topic + audience + tone → calls CourseBuilderService |
| `src/Form/AiEngineUserForm.php` | Form | as-built | Large admin wizard (~2361 lines); 15+ AJAX handlers for full AI content pipeline; no rollback |

---

## lms_analytics

Module path: `web/modules/custom/lms_analytics/`  
Role: Internal reporting — course, user, and quiz analytics with CSV export. **No GA4 integration despite description.**

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Service/AnalyticsService.php` | Service | as-built | `lms_analytics.service` — getTotalEnrollments, getTotalRevenue, getCompletionRate |
| `src/Service/CourseReportManager.php` | Service | as-built | `lms_analytics.course_report` — per-course stats via raw SQL with sub-queries; role-aware access filtering |
| `src/Service/UserReportManager.php` | Service | as-built | `lms_analytics.user_report` — per-user stats with 5 sub-queries |
| `src/Service/QuizAnalyticsManager.php` | Service | as-built | `lms_analytics.quiz_analytics` — overview, score distribution (bins), hardest questions |
| `src/Controller/CourseReportController.php` | Controller | as-built | build (render array), export (StreamedResponse CSV with BOM) |
| `src/Controller/UserReportController.php` | Controller | as-built | build, export |
| `src/Controller/QuizReportController.php` | Controller | as-built | buildList, buildDetail, exportItemAnalysis |

---

## lms_mobile_api

Module path: `web/modules/custom/lms_mobile_api/`  
Role: REST/JSON backend for Flutter mobile app at `/api/v1/*`; custom keyvalue bearer token auth (NOT OAuth2/JWT).

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Service/OtpService.php` | Service | as-built | `lms_mobile_api.otp_service` — OTP code generation (6-digit), session storage in keyvalue.expirable, email dispatch |
| `src/Controller/MobileAuthController.php` | Controller | as-built | 10 endpoints: register, verify-OTP, resend-OTP, forgot-password, verify-forgot-OTP, reset-password, oauth/token, profile, me, change-password |
| `src/Controller/MobileCourseController.php` | Controller | as-built | 6 endpoints: searchCourses, listCategories, getCourseDetail, enrollFree, getDashboard, getMyCourses; 12 private helpers |
| `src/Controller/MobileLearningController.php` | Controller | as-built | 6 endpoints: getLessonDetail, getQuiz, submitQuiz, getQuizResult, listMyQuizResults, markLessonComplete; 14 private helpers; uses `GradingStrategyFactory` statically (undeclared dependency) |

**Auth note:** All routes declare `_access: 'TRUE'`; auth is enforced inside controllers via `getAuthenticatedUser()` (code duplicated across all 3 controllers — refactor candidate). Bearer tokens stored in `keyvalue.expirable` collection `lms_mobile_api.tokens`.

---

## open_switcher

Module path: `web/modules/custom/open_switcher/`  
Role: User impersonation utility (package: "Open Platform", not EverLMS). Allows admins to switch to any non-admin user and switch back.

| File | Type | Status | Notes |
|------|------|--------|-------|
| `src/Controller/SwitchController.php` | Controller | as-built | switchTo (validates target, stores original UID in session, calls user_login_finalize), switchBack, canSwitchBack (custom access check) |
| `src/Controller/SwitchFrontendController.php` | Controller | as-built | `page()` — builds user list for frontend impersonation picker; cache max-age=0 |
| `src/Form/SwitchUserForm.php` | Form | as-built | Admin form with role filter + paged user table + JS confirm on switch |
