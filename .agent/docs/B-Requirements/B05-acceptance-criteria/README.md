# B05 — Acceptance Criteria

> Index of all acceptance criteria files. Mỗi file align với một feature trong [B03-features](../B03-features/). Criteria follow Given/When/Then format với edge cases và NFR tie-ins.

---

## Acceptance Criteria Index

| File | Feature ID | Epic | Description |
|------|-----------|------|-------------|
| [AC-01-course-management.md](./AC-01-course-management.md) | F-001 | EPIC-A | Course creation, curriculum, search/filter |
| [AC-02-assessment-system.md](./AC-02-assessment-system.md) | F-002 | EPIC-B | Quiz grading, question types, attempts |
| [AC-03-learning-progress.md](./AC-03-learning-progress.md) | F-003 | EPIC-C | Progress cascade, concurrency, continue URL |
| [AC-04-order-membership.md](./AC-04-order-membership.md) | F-004 | EPIC-D | Checkout, webhooks, coupons, membership |
| [AC-05-content-media.md](./AC-05-content-media.md) | F-005 | EPIC-A | Video, H5P, SCORM, media uploads |
| [AC-06-certification-badges.md](./AC-06-certification-badges.md) | F-006 | EPIC-E | Auto issuance, verify endpoint, PDF, revoke |
| [AC-07-user-management.md](./AC-07-user-management.md) | F-007 | EPIC-F | Registration, roles, social login, profile |
| [AC-08-communication.md](./AC-08-communication.md) | F-008 | EPIC-F | Forum, notifications, ratings, moderation |
| [AC-09-analytics-reporting.md](./AC-09-analytics-reporting.md) | F-009 | EPIC-G | Reports, filters, accuracy, export |
| [AC-10-integrations.md](./AC-10-integrations.md) | F-010 | EPIC-H | AI provider, Zoom, payment fallback, search |
| [AC-11-mobile-app.md](./AC-11-mobile-app.md) | F-011 | EPIC-A | Responsive, video mobile, offline |
| [AC-12-security-performance.md](./AC-12-security-performance.md) | F-012 | EPIC-B/H | Auth, CSRF, XSS, page load, DB queries |
| [AC-13-modern-features.md](./AC-13-modern-features.md) | F-013 | EPIC-H | Dark mode, streaks, wishlist, bundles |
| [AC-14-ai-features.md](./AC-14-ai-features.md) | F-014 | EPIC-H | AI generation, provider abstraction, rollback |
| [AC-15-customization-branding.md](./AC-15-customization-branding.md) | F-015 | EPIC-A | Theme, logo, email templates |
| [AC-16-essential-enhancements.md](./AC-16-essential-enhancements.md) | F-016 | EPIC-C/G | Bulk ops, audit log, scheduling, SEO |

---

## Epic Summary

| Epic | Description | AC Files |
|------|-------------|---------|
| EPIC-A | Content Lifecycle | AC-01, AC-05, AC-11, AC-15 |
| EPIC-B | Assessment & Grading | AC-02, AC-12 |
| EPIC-C | Learning Progress | AC-03, AC-16 |
| EPIC-D | Commerce & Membership | AC-04 |
| EPIC-E | Certificate & Verification | AC-06 |
| EPIC-F | Community & Notification | AC-07, AC-08 |
| EPIC-G | Analytics & Reports | AC-09, AC-16 |
| EPIC-H | AI & Integrations | AC-10, AC-13, AC-14 |

---

## Traceability

| AC ID | Feature | Related Workflow |
|-------|---------|-----------------|
| AC-A01–A06 | F-001 | WF-001, WF-006 |
| AC-B01–B05 | F-002 | WF-002 |
| AC-C01–C04 | F-003 | WF-001 |
| AC-D01–D06 | F-004 | WF-004, WF-005, WF-009 |
| AC-E01–E04 | F-006 | WF-003 |
| AC-F01–F04 | F-007, F-008 | WF-007, WF-008 |
| AC-G01–G05 | F-009 | — |
| AC-H01–H05 | F-010, F-014 | WF-006 |

---

## Related Docs

- [B03 — Features](../B03-features/) — source feature definitions
- [B04 — Business Workflows](../B04-business-workflows/) — workflows AC tests
- [B06 — Integration Requirements](../B06-integration-requirements/) — integration-specific AC in AC-10
