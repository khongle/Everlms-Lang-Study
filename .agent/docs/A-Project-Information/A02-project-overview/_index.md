# A02-project-overview - Project Overview

**Last updated:** 2026-06-21

## Vision
**EverLMS** is a self-hosted, full-featured Learning Management System built on Drupal 11. It lets organisations publish structured courses (course → chapter → lesson), assess learners with a multi-strategy quiz engine, sell access via one-off orders or recurring memberships, auto-issue PDF certificates, run a learner community (forum, ratings, notes, blog, FAQ), author content with AI assistance, and serve everything to a companion **Flutter mobile app** over a custom REST API. The shipped demo configures EverLMS as an **occupational-safety training** platform (Vietnamese), but the platform is domain-agnostic.

## Product posture
- **Rebuild / as-built.** This documentation pass describes an **existing, running** system, captured from live code, the `config/sync/` export, and the `database/db.sql` dump as of 2026-06-21. Documentation mode is predominantly **as-built**; `target` notes mark intended-but-incomplete behavior (e.g. badge awarding, payment webhooks).
- **Primary users:** Students (learners), Instructors (course authors), Content Editors, Managers, Administrators — plus anonymous visitors browsing the public catalog. See `C-Design/C02-user-roles/`.
- **Non-goals (of the product):** EverLMS is not a live-classroom tool (Zoom integration is a light add-on), not a multi-tenant SaaS, and not a headless-only CMS (it ships its own themed front-end *and* a mobile API).

## Documentation cross-reference
| Concept       | Count | Layer            | Links to |
| ------------- | ----- | ---------------- | -------- |
| Epics         | 13    | D-Development    | [D02-epics/](../../D-Development/D02-epics/) |
| Modules       | 28    | C-Design         | [C05-modules/](../../C-Design/C05-modules/) |
| Entities      | 29    | C-Design         | [C04-entities/](../../C-Design/C04-entities/) |
| Features      | 24    | B-Requirements   | [B03-features/](../../B-Requirements/B03-features/) |
| Workflows     | 10    | B-Requirements   | [B04-business-workflows/](../../B-Requirements/B04-business-workflows/) |
| Integrations  | 9     | B-Requirements   | [B06-integration-requirements/](../../B-Requirements/B06-integration-requirements/) |
| Roles         | 7     | C-Design         | [C02-user-roles/](../../C-Design/C02-user-roles/) |
| Vocabularies  | 11    | C-Design         | [C04-entities/](../../C-Design/C04-entities/) |
| Pages         | ~45   | C-Design         | [C11-pages-components/pages/](../../C-Design/C11-pages-components/pages/) |

<!-- Counts are derived from this project's source. Modules/Entities/Roles/Vocabularies are exact (config + code, 2026-06-21). Epics/Features/Workflows/Integrations are the committed build targets reconciled with B/C/D layers. Pages count finalizes in C07/C11. -->

**Exact source-derived counts (2026-06-21):** 28 custom modules (27 `lms_*` + `open_switcher`); 29 custom entity types (28 content + 1 config `lms_question_type`, 8 question bundles); 7 roles; 11 vocabularies; ~137 custom routes; live demo data = 40 courses, 176 chapters, 600 lessons, 40 quizzes, 158 questions, 20 orders, 81 enrollments, 39 users, 24 certificates.

## In scope
- All A-U + Z documentation layers, materialised as a development-grade build contract.
- The 28 custom modules, 29 entities, role/permission model, page/route inventory, business workflows, integrations, and quality/operations posture as **currently built**.
- Honest implementation status: what works, what is a stub, what is dead code, and the open risks.

## Out of scope for this documentation pass
- Drupal core and contrib internals (documented only where they shape a custom contract).
- The Flutter app's internal widget tree (documented at the API-contract boundary, not screen-by-screen).
- Live secret values, the DB dump contents, and PII (SENSITIVE — referenced, never reproduced).
- New feature design beyond what the code already implements (captured as `target`/risk, not specified anew).
