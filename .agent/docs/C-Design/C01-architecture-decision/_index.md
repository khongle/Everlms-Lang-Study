# C01-architecture-decision - Architecture Roll-up

**Last updated:** 2026-06-21
**source_state:** AS_BUILT

## Decision
EverLMS is a **modular monolith on Drupal 11**: one Drupal install, one MariaDB database, ~28 custom modules under a single `lms_*` prefix, each owning a slice of the domain via **custom content/config entities** (not nodes, except a few legacy `node.type`s). Cross-cutting concerns use **plugin hosts**: payments (`@PaymentGateway`), AI (`@AiProvider`), and quiz grading (`GradingStrategyFactory`). The front-end is a custom Drupal theme (`lms_theme`); a separate **Flutter app** consumes a custom REST API (`/api/v1`, `lms_mobile_api`). Commerce is **custom** (no Drupal Commerce).

## Rationale
- **Custom entities over nodes:** the domain (courses, quizzes, orders, certificates) needs precise schemas, relationships, and access rules that generic nodes do not model cleanly.
- **Plugin hosts** keep gateways/AI providers/grading strategies open for extension without touching callers (Open/Closed) — adding a gateway or LLM is a new plugin.
- **Modular monolith** (vs microservices): a single team, single deploy, shared DB transactions for order→enrollment→certificate consistency; far simpler ops for this scale.
- **Event-driven side effects:** certificate issuance subscribes to `QuizPassedEvent` — decoupling assessment from certification.
- **Decision recorded as** [ADR-002 modular monolith](../../G-Governance/G01-adrs/ADR-002-drupal-11-modular-monolith.md), [ADR-003 plugin hosts](../../G-Governance/G01-adrs/ADR-003-plugin-hosts-for-extensibility.md).

## Source classification
This is a **rebuild/as-built** capture. Every module below exists in `web/modules/custom/`. Status reflects the verified as-built state: `done` = built & working; `in-progress` = partial; `blocked` = defined but non-functional. (Rebuild refinement: all rows are `as-built-backed` — verified against live code/config/DB, 2026-06-21.)

## Standard runtime shape
- **Request:** nginx-fpm → Drupal 11 (PHP 8.3) → MariaDB 10.11; Drush 13 for CLI/ops; DDEV locally.
- **Rendering:** server-rendered Twig via `lms_theme` (Bootstrap-based, 16 regions, 15 homepage skins); admin via Claro.
- **Mobile:** Flutter app → `/api/v1` JSON (bearer token) → `lms_mobile_api` controllers → shared services/entities.
- **Async:** Drupal cron (`drush cron`) drives notification dispatch; events drive certificate issuance.
- **PDF:** mPDF renders certificate templates server-side.

## Route groups
| Route group | Path prefix (logical) | Audience | Access posture | EverLMS mapping |
| --- | --- | --- | --- | --- |
| Public | `/*` | anonymous + authenticated | open / minimal | catalog (`/courses`, `/course/*`), blog, FAQ, login/register |
| App | `/app/*` (logical) | authenticated learners | role-gated | dashboard, my-courses, lesson player, quiz attempt, certificates |
| Manage | `/manage/*` (logical) | instructors / staff | role + permission | course authoring, analytics, order/membership management |
| API | `/api/v1/*` | mobile client | bearer token + scope | `lms_mobile_api` (~22 routes) |
| Admin | `/admin/*` | administrators | admin permission | Drupal admin + `lms_*.settings` config forms |

> EverLMS does not use literal `/app` / `/manage` prefixes for every route (many sit at `/course/*`, `/dashboard`, etc.); the table maps real paths to the 5 logical groups. Exact paths are in [C07-sitemap](../C07-sitemap/_index.md).

## Standard modules
The framework mandates 3 cross-cutting standard modules. As-built status:
| Standard module | EverLMS realisation | Status |
| --- | --- | --- |
| **Content Tools** (seed/demo generators) | [lms_content_tools](../C05-modules/lms_content_tools.md) + `lms_base` Drush commands (`lms:import`, `lms:generate-demo`, `lms:clean`) | ✅ present |
| **Secrets** (single entry point + rotation + audit) | ❌ **absent** — secrets stored in `lms_*.settings.yml` config (R-001). `key` module is a declared dep of `lms_ai` but **no key entities are configured**. | ⚠️ gap (BLK-001) |
| **Feature Flag Manager** (evaluator + rollout DSL + kill-switch) | ❌ **absent** — only core `system.feature_flags` (`linkset_endpoint`) exists; no custom rollout/kill-switch. | ⚠️ gap |

## Module table
Status: all built modules are `done` unless noted. Epic links → [D02-epics/](../../D-Development/D02-epics/). Backing-pages counts are approximate logical pages per module.

| Module | Name | Phase | Epic | Status | Backing pages |
| ------ | ---- | ----- | ---- | ------ | ------------- |
| [lms_base](../C05-modules/lms_base.md) | Base / shared services | 1 | E01 | done | 0 |
| [open_switcher](../C05-modules/open_switcher.md) | User impersonation | 1 | E01 | done | 1 |
| [lms_theme](../C05-modules/lms_theme.md) | Front-end theme | 1 | E01 | done | (theme) |
| [lms_course](../C05-modules/lms_course.md) | Courses & catalog | 2 | E02 | done | 4 |
| [lms_chapter](../C05-modules/lms_chapter.md) | Chapters | 2 | E02 | done | 1 |
| [lms_lesson](../C05-modules/lms_lesson.md) | Lessons & media | 2 | E02 | done | 2 |
| [lms_progress](../C05-modules/lms_progress.md) | Enrollment & progress | 3 | E03 | done | 3 |
| [lms_question](../C05-modules/lms_question.md) | Questions (8 types) | 4 | E04 | done | 2 |
| [lms_quiz](../C05-modules/lms_quiz.md) | Quizzes & grading | 4 | E04 | done | 3 |
| [lms_certificate](../C05-modules/lms_certificate.md) | Certificates (PDF) | 5 | E05 | done | 2 |
| [lms_badge](../C05-modules/lms_badge.md) | Badges | 5 | E05 | blocked | 1 |
| [lms_payment](../C05-modules/lms_payment.md) | Payment gateways | 6 | E06 | in-progress | 1 |
| [lms_stripe](../C05-modules/lms_stripe.md) | Stripe gateway | 6 | E06 | done | 0 |
| [lms_paypal](../C05-modules/lms_paypal.md) | PayPal gateway (duplicate) | 6 | E06 | blocked | 0 |
| [lms_order](../C05-modules/lms_order.md) | Orders & checkout | 6 | E06 | done | 2 |
| [lms_bundle](../C05-modules/lms_bundle.md) | Course bundles | 6 | E06 | done | 2 |
| [lms_membership](../C05-modules/lms_membership.md) | Memberships | 7 | E07 | done | 2 |
| [lms_forum](../C05-modules/lms_forum.md) | Community forum | 8 | E08 | done | 3 |
| [lms_rating](../C05-modules/lms_rating.md) | Ratings & reviews | 8 | E08 | done | 1 |
| [lms_note](../C05-modules/lms_note.md) | Learner notes | 8 | E08 | done | 1 |
| [lms_wishlist](../C05-modules/lms_wishlist.md) | Wishlist | 8 | E08 | done | 1 |
| [lms_blog](../C05-modules/lms_blog.md) | Blog | 8 | E08 | done | 2 |
| [lms_faq](../C05-modules/lms_faq.md) | FAQ | 8 | E08 | done | 1 |
| [lms_zoom](../C05-modules/lms_zoom.md) | Live sessions (Zoom) | 8 | E08 | in-progress | 1 |
| [lms_notification](../C05-modules/lms_notification.md) | Notifications | 9 | E09 | in-progress | 1 |
| [lms_ai](../C05-modules/lms_ai.md) | AI authoring | 10 | E10 | done | 1 |
| [lms_mobile_api](../C05-modules/lms_mobile_api.md) | Mobile REST API | 11 | E11 | done | 0 (API) |
| [lms_analytics](../C05-modules/lms_analytics.md) | Analytics & reports | 12 | E12 | in-progress | 3 |
| [lms_content_tools](../C05-modules/lms_content_tools.md) | Content tools / seed | 13 | E13 | done | 1 |

> 28 modules (data-derived 2026-06-21). Every module here has a [C05-modules](../C05-modules/_index.md) contract.
