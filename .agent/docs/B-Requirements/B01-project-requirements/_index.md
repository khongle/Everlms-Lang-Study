# B01-project-requirements - Business Requirements

**Last updated:** 2026-06-21
**source_state:** REQUIREMENT (reverse-derived from as-built behavior + `.agent/seed/seed-input.json`)

## Goal
Provide a self-hosted, brandable **Learning Management System** on Drupal 11 that lets an organisation publish structured online courses, assess learners, monetise access (one-off purchase or membership), certify completion, and grow a learning community — accessible both through a themed web front-end and a native mobile app. The shipped configuration targets **occupational-safety training** but the platform is domain-agnostic.

## Stakeholders
| Stakeholder | Need |
| ----------- | ---- |
| Learner / Student | Browse and buy courses, learn at own pace, take quizzes, track progress, earn certificates, ask questions in the forum, use the mobile app offline-ish |
| Instructor | Author courses (chapter/lesson/quiz), use AI to draft content, run live Zoom sessions, see who enrolled and how they perform |
| Content Editor | Maintain catalog metadata, blog, FAQ, homepage content, certificate/notification templates without touching code |
| Manager | Oversee orders, memberships, refunds, coupons; read analytics/KPIs; manage instructors |
| Administrator | Configure the platform, roles/permissions, payment gateways, AI keys, theme/skins; operate the system |
| Anonymous visitor | Discover the catalog, read marketing content, register/login |
| Mobile app | Authenticate (OTP/token) and consume catalog + learning data over `/api/v1` |

## Functional scope
Each goal below projects onto one or more features in [B03-features/](../B03-features/_index.md):
1. **Catalog & authoring** — course→chapter→lesson hierarchy, media lessons, bundles, categories/tags (F-001, F-002, F-003, F-009, F-024).
2. **Learning & assessment** — enrollment, content gating, progress tracking/resume, multi-type quizzes with automated grading (F-004, F-005, F-006).
3. **Certification** — auto-issued PDF certificates on pass; badges/gamification (F-007, F-008).
4. **Commerce** — orders, checkout, coupons, multiple payment gateways, memberships/subscriptions (F-010, F-011, F-012).
5. **Community & engagement** — forum, ratings/reviews, notes, wishlist, blog, FAQ, notifications (F-013, F-014, F-015, F-016, F-017).
6. **AI & integrations** — AI-assisted content generation, Zoom live sessions, push/email delivery (F-018, F-022).
7. **Reach & operations** — mobile API, analytics, user/role management & impersonation, content seeding tools (F-019, F-020, F-021, F-023).

## Business constraints
- **Self-hosted Drupal 11 / PHP 8.3 / MariaDB**; deployable via DDEV locally. No reliance on Drupal Commerce — commerce is custom (`lms_order`/`lms_payment`).
- **Editorial independence:** all learner-visible copy (course/lesson text, certificates, notifications, FAQ, homepage skins) must be editable by non-developers (see B02 Editorial-usability NFR).
- **Multi-channel:** the same content must serve the web theme and the Flutter mobile app via a stable API.
- **Localisation:** shipped content is Vietnamese; the platform must not hardcode language.
- **Known as-built gaps (constraints on release):** secrets currently in config YAML, no payment webhooks, badge awarding incomplete, ~0% automated tests — see [G02-risk-register](../../G-Governance/G02-risk-register/_index.md).
