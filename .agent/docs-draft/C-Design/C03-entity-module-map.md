# C03 — Entity-Module Map

> Roll-up: 29 entity classes × 27 module dirs (26 `lms_*` + `open_switcher`) × 9 layers.

---

## Entity Relationship Diagram

```
COURSE 1──M CHAPTER 1──M LESSON M──1 QUIZ
QUIZ 1──M QUESTION 1──M ANSWER
QUESTION 1──M MATCHING_PAIR
QUIZ 1──M QUIZ_RESULT 1──M QUESTION_RESPONSE
QUIZ_RESULT M──1 USER

USER_COURSE M──1 USER, M──1 COURSE
USER_CHAPTER M──1 USER, M──1 CHAPTER
USER_LESSON M──1 USER, M──1 LESSON

ORDER 1──M ORDER_ITEM, 1──M PAYMENT_TRANSACTION
ORDER M──1 USER
MEMBERSHIP M──1 USER, M──1 MEMBERSHIP_PLAN

NOTIFICATION M──1 USER
FORUM_TOPIC M──1 COURSE, M──1 USER
FORUM_REPLY M──1 FORUM_TOPIC
RATING M──1 COURSE, M──1 USER
BADGE 1──M USER_BADGE M──1 USER
```

## Entity Groups by Domain

### 1. Content Domain (3 entities)

| Entity | Module | Table | Fields | Description |
|--------|--------|-------|--------|-------------|
| `lms_course` | `lms_course` | `lms_course` | 22 | Khóa học (title, description, category, price, status) |
| `lms_chapter` | `lms_chapter` | `lms_chapter` | 7 | Chương trong khóa học |
| `lms_lesson` | `lms_lesson` | `lms_lesson` | 20 | Bài học (7 types: video, text, h5p, scorm, quiz, zoom, mixed) |

### 2. Assessment Domain (6 entities)

| Entity | Module | Table | Fields | Description |
|--------|--------|-------|--------|-------------|
| `lms_quiz` | `lms_quiz` | `lms_quiz` | — | Quiz (threshold, time_limit, max_attempts) |
| `lms_question` | `lms_question` | `lms_question` | — | Câu hỏi (8 bundles) |
| `lms_answer` | `lms_answer` | `lms_answer` | — | Đáp án cho MC/SC questions |
| `lms_matching_pair` | `lms_matching_pair` | `lms_matching_pair` | — | Cặp ghép cho Matching questions |
| `lms_quiz_result` | `lms_quiz_result` | `lms_quiz_result` | — | Kết quả quiz (score, passed, attempt) |
| `lms_question_response` | `lms_question_response` | `lms_question_response` | — | Chi tiết trả lời từng câu |

### 3. Progress Domain (3 entities)

| Entity | Module | Table | Description |
|--------|--------|-------|-------------|
| `lms_user_course` | `lms_progress` | `lms_user_course` | Tiến độ khóa học (% complete) |
| `lms_user_chapter` | `lms_progress` | `lms_user_chapter` | Tiến độ chương |
| `lms_user_lesson` | `lms_progress` | `lms_user_lesson` | Tiến độ bài học (completed flag) |

### 4. Commerce Domain (7 entities)

| Entity | Module | Table | Description |
|--------|--------|-------|-------------|
| `lms_order` | `lms_order` | `lms_order` | Đơn hàng (status lifecycle) |
| `lms_order_item` | `lms_order` | `lms_order_item` | Chi tiết đơn hàng |
| `lms_coupon` | `lms_order` | `lms_coupon` | Mã giảm giá |
| `lms_membership_plan` | `lms_membership` | `lms_membership_plan` | Gói membership |
| `lms_user_membership` | `lms_membership` | `lms_user_membership` | User membership instance |
| `lms_wishlist` | `lms_wishlist` | `lms_wishlist` | Danh sách yêu thích |
| `lms_course_bundle` | `lms_bundle` | `lms_course_bundle` | Gói khóa học |

### 5. Certificate Domain (2 entities)

| Entity | Module | Table | Description |
|--------|--------|-------|-------------|
| `lms_certificate_template` | `lms_certificate` | `lms_certificate_template` | Template chứng chỉ |
| `lms_user_certificate` | `lms_certificate` | `lms_user_certificate` | Chứng chỉ đã cấp |

### 6. Communication Domain (7 entities)

| Entity | Module | Table | Description |
|--------|--------|-------|-------------|
| `lms_notification` | `lms_notification` | `lms_notification` | Thông báo (6 types) |
| `lms_forum_topic` | `lms_forum` | `lms_forum_topic` | Chủ đề forum |
| `lms_forum_reply` | `lms_forum` | `lms_forum_reply` | Phản hồi (threaded) |
| `lms_rating` | `lms_rating` | `lms_rating` | Đánh giá 1-5 sao + review |
| `lms_faq` | `lms_faq` | `lms_faq` | FAQ (3-scope, translatable) |
| `lms_note` | `lms_note` | `lms_note` | Ghi chú video timestamp |
| `lms_comment` | Core | — | Comment trên lesson (Drupal core) |

### 7. Gamification Domain (3 entities)

| Entity | Module | Table | Description |
|--------|--------|-------|-------------|
| `lms_badge` | `lms_badge` | `lms_badge` | Huy hiệu |
| `lms_user_badge` | `lms_badge` | `lms_user_badge` | Huy hiệu đã nhận |
| `lms_payment_transaction` | `lms_payment` | `lms_payment_transaction` | Giao dịch thanh toán |

### 8. Taxonomies (9 vocabularies)

| Vocabulary | Module | Usage |
|-----------|--------|-------|
| `lms_course_category` | `lms_base` | Phân loại khóa học (multi-level) |
| `lms_course_level` | `lms_base` | Beginner / Intermediate / Advanced |
| `lms_course_language` | `lms_base` | Ngôn ngữ giảng dạy |
| `lms_course_tag` | `lms_base` | Tags cho SEO & search |
| `lms_lesson_type` | `lms_base` | Video / Text / H5P / SCORM / Quiz / Zoom / Mixed |
| `lms_question_type` | `lms_base` | 8 question bundles |
| `lms_notification_type` | `lms_base` | 6 notification types |
| `lms_certificate_status` | `lms_base` | Active / Revoked |
| `lms_order_status` | `lms_base` | Pending / Completed / Failed / Refunded |

## Module × Entity Matrix

| Module | Entities | COUNT |
|--------|----------|-------|
| `lms_base` | 9 taxonomies | 9 |
| `lms_course` | Course | 1 |
| `lms_chapter` | Chapter | 1 |
| `lms_lesson` | Lesson | 1 |
| `lms_quiz` | Quiz, QuizResult, QuestionResponse | 3 |
| `lms_question` | Question (8 bundles), Answer, MatchingPair | 3 |
| `lms_progress` | UserCourse, UserChapter, UserLesson | 3 |
| `lms_order` | Order, OrderItem, Coupon | 3 |
| `lms_membership` | MembershipPlan, UserMembership | 2 |
| `lms_payment` | PaymentTransaction | 1 |
| `lms_certificate` | CertificateTemplate, UserCertificate | 2 |
| `lms_notification` | Notification | 1 |
| `lms_forum` | ForumTopic, ForumReply | 2 |
| `lms_rating` | Rating | 1 |
| `lms_wishlist` | Wishlist | 1 |
| `lms_bundle` | CourseBundle | 1 |
| `lms_note` | Note | 1 |
| `lms_badge` | Badge, UserBadge | 2 |
| `lms_faq` | FAQ | 1 |
| **Total** | | **~40** |

---

*End of C03-entity-module-map.md*
