# EverLMS — Data Model Overview

> Tổng quan entity architecture. Chi tiết field definitions trong từng file riêng.

## Entity Relationship Diagram

```mermaid
erDiagram
    COURSE ||--o{ CHAPTER : "field_chapters"
    CHAPTER ||--o{ LESSON : "field_lessons"
    LESSON ||--o| QUIZ : "field_quiz"
    
    QUIZ ||--o{ QUESTION : "field_questions"
    QUIZ ||--o| CERTIFICATE_TEMPLATE : "field_certificate"
    
    QUESTION ||--o{ ANSWER : "field_choices"
    QUESTION ||--o{ MATCHING_PAIR : "field_matching_pairs"
    
    QUIZ_RESULT ||--o{ QUESTION_RESPONSE : "field_responses"
    QUIZ_RESULT }o--|| USER : "field_taker"
    QUIZ_RESULT }o--|| QUIZ : "field_quiz"
    QUIZ_RESULT ||--o| USER_CERTIFICATE : "field_certificate"
    
    USER_COURSE }o--|| USER : "field_user"
    USER_COURSE }o--|| COURSE : "field_course"
    USER_CHAPTER }o--|| USER : "field_user"
    USER_CHAPTER }o--|| CHAPTER : "field_chapter"
    USER_LESSON }o--|| USER : "field_user"
    USER_LESSON }o--|| LESSON : "field_lesson"
    
    ORDER ||--o{ ORDER_ITEM : "field_items"
    ORDER ||--o{ PAYMENT_TRANSACTION : "field_order"
    ORDER }o--|| USER : "field_customer"
    
    MEMBERSHIP }o--|| USER : "field_user"
    MEMBERSHIP }o--|| MEMBERSHIP_PLAN : "field_plan"
    
    NOTIFICATION }o--|| USER : "field_recipient"
    
    FORUM_TOPIC }o--|| COURSE : "field_course"
    FORUM_TOPIC }o--|| USER : "field_author"
    FORUM_REPLY }o--|| FORUM_TOPIC : "field_topic"
    
    RATING }o--|| COURSE : "field_course"
    RATING }o--|| USER : "field_author"
    
    BADGE ||--o{ USER_BADGE : "field_badge"
    USER_BADGE }o--|| USER : "field_user"
```

## Entity Groups

| # | File | Entities | Count |
|---|------|----------|-------|
| 01 | [Content Entities](./01-content-entities.md) | Course, Chapter, Lesson | 3 |
| 02 | [Assessment Entities](./02-assessment-entities.md) | Quiz, Question (8 bundles), Answer, MatchingPair, QuizResult, QuestionResponse | 6 |
| 03 | [Progress Entities](./03-progress-entities.md) | UserCourse, UserChapter, UserLesson | 3 |
| 04 | [Commerce Entities](./04-commerce-entities.md) | Order, OrderItem, Coupon, MembershipPlan, UserMembership, Wishlist, Bundle | 7 |
| 05 | [Certificate Entities](./05-certificate-entities.md) | CertificateTemplate, UserCertificate | 2 |
| 06 | [Taxonomies](./06-taxonomies.md) | 9 taxonomy vocabularies | 9 |
| 07 | [Communication Entities](./07-communication-entities.md) | Notification, ForumTopic, ForumReply, Rating, Comment, FAQ, StudentNote | 7 |
| 08 | [Gamification Entities](./08-gamification-entities.md) | Badge, UserBadge, PaymentTransaction | 3 |
| — | **Total** | — | **40** |
