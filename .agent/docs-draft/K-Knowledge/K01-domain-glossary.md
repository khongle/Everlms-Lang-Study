# K01 — Domain Glossary

> Authoritative terminology reference. ~70 terms covering entities, domain concepts, Vietnamese ↔ English mappings, and abbreviations.
> Cross-reference: C04-entities/ for full field specs, C05-modules/ for module ownership.

---

## Content Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **Course** | Khóa học | Top-level learning container. Has enrollment rules, pricing, prerequisite courses, and completion criteria. Must have ≥ 1 chapter to publish. | `lms_course` | `lms_course` |
| **Chapter** | Chương | Organizational unit within a Course. Groups related Lessons. Ordered by `weight`. Must belong to ≥ 1 course. | `lms_chapter` | `lms_course` |
| **Lesson** | Bài học | Atomic learning unit. 7 types: `video`, `text`, `quiz`, `h5p`, `scorm`, `zoom`, `mixed`. Completion rules vary by type. | `lms_lesson` | `lms_lesson` |
| **Category** | Danh mục | Hierarchical taxonomy classifying Courses. Displayed on frontend only if ≥ 1 active course. | `course_category` | `lms_course` |
| **Course Bundle** | Gói khóa học | Package of ≥ 2 Courses sold at combined discount. Purchasing auto-enrolls in all included courses. | `lms_bundle` | `lms_course` |
| **Learning Path** | Lộ trình học | Ordered sequence of Courses toward a specific skill goal. | `field_learning_path` (ref) | `lms_course` |
| **Drip Content** | Nội dung nhỏ giọt | Lesson unlocking schedule: lesson opens N days after enrollment (`field_drip_days`). | — | `lms_lesson` |
| **Free Preview** | Xem trước miễn phí | Lesson accessible without enrollment (`field_free_preview = true`). | — | `lms_lesson` |
| **Prerequisite** | Tiên quyết | Course/Chapter/Lesson that must be completed before the next item unlocks. | `field_prerequisites` | multiple |

---

## Assessment Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **Quiz** | Bài kiểm tra | Assessment attached to a Lesson or Course. Configurable: time limit, attempt count, pass threshold, random question pool. | `lms_quiz` | `lms_quiz` |
| **Question** | Câu hỏi | Individual assessment item. 8 bundles: `single_choice`, `multiple_choice`, `true_false`, `fill_blank`, `short_answer`, `matching`, `ordering`, `essay`. | `lms_question` | `lms_question` |
| **Answer** | Đáp án | Choice option for `single_choice` / `multiple_choice` questions. Has `field_correct` flag. | `lms_answer` | `lms_question` |
| **Matching Pair** | Cặp ghép đôi | Left/right item pair for `matching` questions. Displayed as dropdown or drag-drop. | `lms_matching_pair` | `lms_question` |
| **Quiz Result** | Kết quả bài kiểm tra | Attempt record: score %, pass/fail taxonomy, start/end time, links to all QuestionResponses. | `lms_quiz_result` | `lms_quiz` |
| **Question Response** | Câu trả lời | Per-question answer record within a QuizResult. Stores raw answer, correctness, and teacher feedback for essay type. | `lms_question_response` | `lms_quiz` |
| **Essay Question** | Câu hỏi tự luận | Only manually-graded question type. Has `field_pending_review = true` until instructor grades. | bundle `essay` | `lms_question` |
| **Question Bank** | Ngân hàng câu hỏi | Pool of Questions from which a Quiz can randomly draw N items (`field_question_count > 0`). | — (concept) | `lms_question` |
| **Pass Threshold** | Điểm đầu qua | Minimum score % required to pass a Quiz (`field_threshold`). | `field_threshold` | `lms_quiz` |

---

## Progress Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **Enrollment** | Đăng ký học | Active learning relationship between a User and a Course. Created by purchase, membership grant, or admin action. | `lms_user_course` | `lms_progress` |
| **UserCourse** | Tiến độ khóa học | Enrollment record with aggregate progress (0–100 %) and completion status. | `lms_user_course` | `lms_progress` |
| **UserChapter** | Tiến độ chương | Chapter-level progress record. Created when user first accesses a lesson in the chapter. | `lms_user_chapter` | `lms_progress` |
| **UserLesson** | Tiến độ bài học | Lesson-level progress. Tracks `field_video_progress` (video lessons), `field_time_spent`, completion date. | `lms_user_lesson` | `lms_progress` |
| **Progress Cascade** | Tính toán tiến độ dây chuyền | Three-level roll-up: Lesson completion → Chapter % → Course %. Implemented in `lms_progress.calculator`. Race-condition risk: see RISK-002, ADR-010. | — (service) | `lms_progress` |
| **Completion** | Hoàn thành | `UserCourse.field_progress = 100 %` + `field_status = completed`. Triggers `CourseCompletedEvent`. | — | `lms_progress` |

---

## Commerce Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **Order** | Đơn hàng | Purchase transaction record. States: `pending → paid → completed / refunded / cancelled`. | `lms_order` | `lms_order` |
| **OrderItem** | Dòng đơn hàng | Line within an Order referencing one Course or MembershipPlan at its price-at-time-of-purchase. | `lms_order_item` | `lms_order` |
| **Coupon** | Mã giảm giá | Discount code. Types: `percentage` or `fixed`. Constraints: expiry date, usage limit, course scope. | `lms_coupon` | `lms_order` |
| **Gateway** | Cổng thanh toán | Payment processor plugin (Stripe, PayPal). Implements `PaymentGatewayPluginBase`. See ADR-007. | plugin | `lms_payment` |
| **Webhook** | — | Async server-to-server callback from payment gateway. **FIND-001/002**: Stripe + PayPal webhooks currently stub-only. | — | `lms_payment` |
| **MembershipPlan** | Gói thành viên | Subscription tier (basic / premium / enterprise). Grants access to Course set. Durations: monthly / yearly / lifetime. | `lms_membership_plan` | `lms_membership` |
| **UserMembership** | Đăng ký thành viên | Active subscription linking a User to a MembershipPlan. States: `trial / active / expired / cancelled`. | `lms_user_membership` | `lms_membership` |
| **Wishlist** | Danh sách yêu thích | Saved course list per user. Unique `(user, course)`. Notifies user when saved course is discounted. | `lms_wishlist` | `lms_course` |
| **Payment Transaction** | Giao dịch thanh toán | Immutable payment log (charge, refund, subscription_renewal). Stores raw gateway JSON response. | `lms_payment_transaction` | `lms_payment` |

---

## Certificate Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **CertificateTemplate** | Mẫu chứng chỉ | Design template with background, logo, signature, layout JSON, and token placeholders (`{student_name}`, `{course_name}`, etc.). | `lms_certificate` | `lms_certificate` |
| **UserCertificate** | Chứng chỉ | Issued certificate record. Unique ID format `ELMS-{PREFIX}-{RANDOM}-{INITIALS}`. PDF generated via DOMPDF/mPDF. | `lms_user_certificate` | `lms_certificate` |
| **Verification** | Xác minh chứng chỉ | Public page at `/verify/{certificate_id}` showing certificate validity. Returns "Revoked" if `field_revoked = true`. | — (route) | `lms_certificate` |

---

## Communication Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **Notification** | Thông báo | In-app / email / push message sent to a User. Types: enrollment, quiz_result, certificate, payment, forum_reply, etc. Auto-cleaned after 90 days. | `lms_notification` | `lms_notification` |
| **ForumTopic** | Chủ đề diễn đàn | Threaded discussion post. Scoped to a Course or global. Supports pinning, locking, resolved flag. | `lms_forum_topic` | `lms_forum` |
| **ForumReply** | Phản hồi | Reply to a ForumTopic. Max 3 nesting levels. Best-answer flag marks topic as resolved. | `lms_forum_reply` | `lms_forum` |
| **Rating** | Đánh giá | 1–5 star course review. Unique per `(user, course)`. Only enrolled users. Pending moderation until approved. | `lms_rating` | `lms_rating` |
| **Note** | Ghi chú | Per-user, per-lesson note. Auto-saved (debounce 2 s). Exportable to PDF/TXT. | `lms_note` | `lms_lesson` |
| **FAQ** | Câu hỏi thường gặp | Scoped to `global` / `course` / `lesson`. Accordion UI. | `lms_faq` | `lms_faq` |
| **Blog** | Blog | Site-level articles (not course-scoped). Standard Drupal node, styled via theme. | `node` (blog) | `lms_blog` |

---

## Gamification Entities

| Term | Vietnamese | Definition | Technical Name | Module |
|------|-----------|------------|----------------|--------|
| **Badge** | Huy hiệu | Achievement awarded automatically by criteria type: `course_completion`, `quiz_master`, `streak`, `community`, `points_threshold`. | `lms_badge` | `lms_gamification` |
| **UserBadge** | Huy hiệu của người dùng | Record of a Badge awarded to a User. Unique `(user, badge)`. User chooses ≤ 5 to display on profile. | `lms_user_badge` | `lms_gamification` |

---

## Domain Concepts

| Term | Vietnamese | Definition |
|------|-----------|------------|
| **Cascade** | Tính toán dây chuyền | Progress roll-up: Lesson → Chapter → Course. Service: `lms_progress.calculator`. |
| **Gate G1–G4** | Cổng kiểm tra G1–G4 | Four quality gates run before a unit can pass: G1 spec review, G2 lint/static, G3 unit tests, G4 integration/security test. |
| **Checkpoint CP1–CP6** | Điểm kiểm soát | Six project milestones: CP1 setup, CP2 content layer, CP3 assessment+commerce, CP4 hardening, CP5 UAT, CP6 launch. |
| **Unit** | Đơn vị công việc | Smallest shippable code task. Defined in D03-unit-specs. Has owner, gate, AC, and test plan. |
| **Group** | Nhóm | Batch of related Units (e.g., G-HARDENING, G-UI-POLISH). Tracked together in D01. |
| **Sprint** | Sprint | Time-boxed iteration (2-week default). Units move from backlog → in-progress → done. |
| **FF flag** | Feature flag | Runtime on/off switch. Types: `FF_FEAT_*` (feature), `FF_EXP_*` (experiment), `FF_ROLLOUT_*` (gradual). Prevents partial deploy. |
| **RISK** | Rủi ro | Known architectural or security weakness with mitigation plan. Tracked in G02. |
| **FIND** | Phát hiện | Code-review discovery of a concrete defect or missing implementation. Distinguished from RISK by specificity. Tracked in A10. |
| **Persona** | Vai trò tác nhân | Specialized agent mode: Agent_PM, Agent_Coder, Agent_Architect, Agent_Reviewer. Controls which files are loaded and what actions are permitted. |
| **Handler Manager** | Trình quản lý handler | Service that discovers and dispatches to typed handler plugins. Example: `QuestionHandlerManager` routes quiz grading to the correct `QuestionHandlerInterface` implementation. |
| **Plugin Discovery** | Khám phá plugin | Drupal mechanism (PHP Attributes in v11) that auto-registers plugin classes without manual wiring. Used for PaymentGateway, AiProvider, QueueWorker. |
| **Entity API** | — | Drupal's abstraction for content/config storage: load, save, delete through `EntityTypeManagerInterface`. Never raw SQL for entity reads. |
| **BundleEntityType** | — | Drupal entity attribute declaring that an entity has multiple subtypes (bundles), each with different fields. E.g., `lms_question` has 8 bundles. |
| **views_data handler** | — | Entity annotation registering the entity with Drupal Views for list/report building. Must be declared or explicitly excluded. |
| **Drip** | — | See Drip Content above. |

---

## Status Values

| Entity | Status Values | Notes |
|--------|--------------|-------|
| Order | `pending → paid → completed`, `pending → cancelled`, `paid → refunded` | Enrollment auto-created on `paid` |
| UserCourse | `not_started / in_progress / completed` | `completed` set when progress = 100 % |
| UserMembership | `trial / active / expired / cancelled` | Grace period 3 days after payment failure |
| UserLesson | `not_started / in_progress / completed` | Completion rules vary by lesson type |
| Quiz (result) | `passed / failed` | Determined by `field_threshold` |
| Notification | `unread / read` | Auto-deleted after 90 days (read) |
| UserCertificate | `valid / revoked` | Revoked by admin; public verify page reflects state |

---

## Abbreviations

| Abbr | Full | Notes |
|------|------|-------|
| LMS | Learning Management System | EverLMS là một LMS |
| AC | Acceptance Criteria | Defined per Unit in D03 |
| DI | Dependency Injection | Constructor injection; static calls only in .module files |
| RBAC | Role-Based Access Control | 5 roles: Student, Instructor, Content Editor, Manager, Administrator |
| CSRF | Cross-Site Request Forgery | Drupal Form API protects forms; JSON endpoints need explicit token |
| NFR | Non-Functional Requirement | Defined in B02 |
| E2E | End-to-End (testing) | Functional tests via Drupal FunctionalTestBase |
| UAT | User Acceptance Testing | CP5 milestone |
| PDF | Portable Document Format | Used for certificates and invoices |
| API | Application Programming Interface | — |
| SCORM | Sharable Content Object Reference Model | Standard for e-learning packages |
| MoSCoW | Must / Should / Could / Won't | Prioritization framework (B01) |
| SA | Solution Architect | Project technical authority and decision maker |
| FF | Feature Flag | `FF_FEAT_*`, `FF_EXP_*`, `FF_ROLLOUT_*` |
| CP | Checkpoint | CP1–CP6 project milestones |
| ADR | Architecture Decision Record | Documented in G01-adrs/ |
| DTO | Data Transfer Object | Typed input container for API endpoints (see ADR-009) |
| XSS | Cross-Site Scripting | Mitigated by Twig auto-escape + Drupal render API |
| DEC | Open Decision | Tracked in A10. Requires SA sign-off before dependent Units proceed. |
| BEM | Block Element Modifier | CSS naming: `.lms-card__header--active` |
| DDEV | — | Docker-based local Drupal dev environment |
| DOMPDF / mPDF | — | PHP PDF generation libraries used for certificates and invoices |

---

*End of K01-domain-glossary.md*
