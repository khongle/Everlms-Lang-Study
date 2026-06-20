# F03 — Changelog

## Format

```
## [version] — YYYY-MM-DD
### Added / Changed / Fixed / Removed / Security
- Description (Req-ID / Task-ID)
```

---

## [Unreleased]

### Added
- 23 custom modules scaffolded (lms_ prefix) — Phase 0-3
- Course → Chapter → Lesson content hierarchy (lms_course, lms_chapter, lms_lesson)
- 8 question types with Strategy pattern (lms_question)
- Quiz engine with configurable grading (lms_quiz)
- Progress tracking cascade: lesson → chapter → course (lms_progress)
- Enrollment system with 4 states (lms_course)
- User roles: Administrator, Manager, Instructor, Content Editor, Student
- Catalog with search, filter, sort (lms_course)
- Bundle/combo course packages (lms_bundle)
- Wishlist functionality (lms_wishlist)
- Forum discussions per course (lms_forum)
- Rating & review system (lms_rating)
- FAQ management (lms_faq)
- Student notes per lesson (lms_note)
- Badge/gamification system (lms_badge)
- Order entity scaffold (lms_order)
- Payment gateway interface (lms_payment)
- Stripe gateway stub (lms_stripe)
- PayPal gateway stub (lms_paypal)
- Certificate entity scaffold (lms_certificate)
- Notification system scaffold (lms_notification)
- Analytics module scaffold (lms_analytics)
- Zoom integration scaffold (lms_zoom)
- Membership subscription scaffold (lms_membership)
- Dark premium Bootstrap 5.3 theme (lms_theme)

### Needs Implementation
- Checkout workflow end-to-end (TASK-001)
- Webhook handler with signature verification (TASK-002)
- Coupon/discount system (TASK-003)
- Membership lifecycle (TASK-004)
- Certificate issuance + PDF generation (TASK-006, TASK-007)
- Notification dispatch service (TASK-009)
- AI provider plugin system (TASK-012, TASK-013)
- Analytics dashboards (TASK-014)

### Known Issues
- API input validation gaps across modules (TASK-015)
- Access control hardening needed (TASK-016)
- Progress race condition on concurrent updates (TASK-017)
- Static service calls need DI refactor (TASK-018)

---

*End of F03-changelog.md*
