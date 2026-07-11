# B02-technical-requirements - Technical Requirements

**Last updated:** 2026-06-21
**source_state:** AS_BUILT (derived from `composer.json`, `.ddev/config.yaml`, `config/sync/`, module code)

## Target stack
| Area | Requirement |
| ---- | ----------- |
| Platform | Drupal 11 (core `^11.3`), install profile `standard` |
| Language | PHP 8.3 |
| Database | MariaDB 10.11 (DDEV service `db`) |
| Web server | nginx-fpm |
| Local env | DDEV (`everlms`, host `https://everlms.ddev.site`) |
| CLI | Drush `^13.7`; Composer 2 |
| PDF | mPDF `^8.3` (certificate rendering) |
| Payments | Stripe SDK (`lms_stripe`); custom `@PaymentGateway` plugins: `bank_transfer`, `stripe`, `paypal`, `zalopay` |
| AI | Custom `@AiProvider` plugins: OpenAI, Google Gemini, Pollinations |
| Frontend | Theme `lms_theme` (Bootstrap 5.x, Twig, SCSS, 15 homepage skins); alt `lms_classic`; admin `Claro` |
| Mobile | Flutter app (`app/`, v1.0.10+33) over custom bearer-token REST API `/api/v1` (`lms_mobile_api`); Firebase/APNs push |
| Config | `config_sync_directory = ../config/sync` (396 YAML, git-tracked) |
| Custom code | 27 `lms_*` modules + `open_switcher`; 29 entity types; ~137 routes |

`public_api_surface = yes` (mobile `/api/v1` là một API surface hạng nhất (first-class) → I-External được hiện thực hóa).
`end_user_presence = yes` (themed web + mobile end-user → U-Userguide được hiện thực hóa).

## NFRs
Tên NFR align **bidirectionally** (hai chiều) với [E10-nfr-tracking](../../E-Quality/E10-nfr-tracking/_index.md) (cùng tên, cả hai chiều).

| NFR | Target |
| --- | ------ |
| Performance | Catalog and course pages render < 1 s server time on seeded data (40 courses / 600 lessons); list views paginated; entity queries indexed. Measured per E12-performance-results. |
| Security | Role-based access on every route; no secrets in code (⚠️ as-built deviation: API keys in config YAML — tracked R-001); payment/AI calls server-side only; CSRF on state-changing forms; bearer-token auth on `/api/v1`. |
| Reliability | Certificate issuance idempotent on `QuizPassedEvent`; orders reconcilable with gateway (⚠️ no webhooks today — R-002); cron-driven jobs (notifications) retried. |
| Accessibility | Themed pages meet WCAG 2.1 AA targets (semantic Twig, alt text on media, keyboard-navigable quiz UI). Verified per E03 accessibility test def. |
| Maintainability | Single project prefix `lms_*`; PSR-4 + Drupal coding standards (phpcs Drupal,DrupalPractice); plugin-based extensibility (gateways, AI providers, grading strategies); code-map in D07. |
| Editorial usability | Every business-visible value maps to a named editable source (entity field / config / View / menu / block / form) — no hardcoded editorial content. Evidence: E03 edit-smoke test def + E04 editor-usability QA. |

## Public-API & end-user presence
- public_api_surface: YES — mobile bearer-token API `/api/v1` (`lms_mobile_api`, ~22 routes) cho Flutter app.
- end_user_presence: YES — public catalog + authenticated learner app (dashboard, player, checkout).
- editorial_content: YES — nội dung do editor quản lý qua management UI (entity-first invariant): course/lesson/quiz/blog/faq/certificate template/notification.
