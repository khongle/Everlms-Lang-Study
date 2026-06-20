# Certificate + Communication Layer

---

## `lms_certificate` — Certificate & Badges

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Certificate templates, issuance, PDF generation, badges, gamification |
| **Entities** | `lms_certificate`, `lms_user_certificate`, `lms_badge`, `lms_user_badge` |
| **Dependencies** | `lms_base`, `lms_quiz_result` |
| **Services** | `lms_certificate.generator`, `lms_certificate.pdf`, `lms_badge.manager` |

### Key Services
- `lms_certificate.generator` — Token replacement, auto-issue on quiz pass
- `lms_certificate.pdf` — PDF generation via DOMPDF/mPDF
- `lms_badge.manager` — Badge criteria evaluation, auto-award

---

## `lms_notification` — Notifications

| Attribute | Value |
|-----------|-------|
| **Responsibility** | In-app + email notifications, user preferences, notification queue |
| **Entities** | `lms_notification` |
| **Dependencies** | `lms_base` |
| **Services** | `lms_notification.manager`, `lms_notification.dispatcher` |

### Notification Types
| Event | Channel | Template |
|-------|---------|----------|
| Enrollment success | Email + In-app | `enrollment_confirmation` |
| Quiz passed | In-app | `quiz_passed` |
| Certificate issued | Email + In-app | `certificate_issued` |
| Membership expiry (7d) | Email | `membership_expiry_warning` |
| New lesson published | In-app | `new_lesson` |
| Forum reply | In-app | `forum_reply` |
| Payment success | Email | `payment_receipt` |
| Payment failed | Email | `payment_failed` |

---

## `lms_forum` — Forum & Discussion

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Course forums, topics, replies, @mentions, moderation |
| **Dependencies** | `lms_base`, `lms_course` |
| **Notes** | Can leverage Drupal contrib forum or custom entity |

### Features
- Course-scoped forums (each course has its own forum)
- Topics with pinning, locking
- Threaded replies with rich text
- @mentions highlighting
- Instructor moderation tools

---

## `lms_rating` — Rating & Reviews

| Attribute | Value |
|-----------|-------|
| **Responsibility** | 5-star course ratings, text reviews, moderation |
| **Dependencies** | `lms_base`, `lms_course` |
| **Services** | `lms_rating.manager` |

### Business Rules
- Only enrolled users can rate
- One rating per user per course
- Average rating auto-calculated, displayed on course card
