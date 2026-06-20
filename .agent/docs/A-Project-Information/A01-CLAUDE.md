# A01-CLAUDE.md — AI Agent Configuration

> **Tier 1 — load at every session start.**
> This is the single most important onboarding file for an AI agent. It contains
> runnable commands, hard constraints, the full module/entity map, and pointers to
> the canonical Tier-1 file set. All claims are grounded in the live codebase as
> of 2026-06-20.

---

## Project

| Field | Value |
|---|---|
| Name | EverLMS |
| Type | Learning Management System (Drupal 11 custom) |
| Stack | Drupal **11.3.5** · PHP **8.3** · MariaDB **10.11** · Bootstrap **5.3.3** CDN |
| Dev environment | **DDEV** (`everlms.ddev.site`, nginx-fpm) |
| Drush | **13.7.2** |
| Created | 2026-02-27 |
| Last updated | 2026-06-20 |

---

## Commands

All commands run inside the DDEV web container via `ddev drush` / `ddev composer`.

```bash
# --- Start / bootstrap ---
ddev start                             # spin up containers; auto-writes settings.ddev.php
ddev composer install                  # install PHP deps to vendor/

# --- Cache & config ---
ddev drush cr                          # cache:rebuild (use after ANY code or config change)
ddev drush cim -y                      # config:import from config/sync/ (396 YAML files)
ddev drush cex -y                      # config:export to config/sync/ (commit the diff)

# --- Database ---
ddev import-db --file=database/db.sql.gz   # import compressed dump (tracked in git)
ddev drush updatedb -y                     # run pending update hooks after DB import
ddev drush cr                              # rebuild cache post-import

# --- Users ---
ddev drush uli                         # one-time login link for uid=1

# --- Cron ---
ddev drush cron                        # run Drupal cron (queue workers, notifications, etc.)

# --- Lint / code quality ---
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/

# --- Tests ---
ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/

# --- Maintenance scripts (Drush PHP scripts) ---
ddev drush php-script scripts/fix_all_categories.php       # remap taxonomy categories
ddev drush php-script scripts/fix_dummy_quiz_results.php   # patch seeded quiz result data
ddev drush php-script scripts/regenerate_all_certs.php     # regenerate all certificate PDFs

# --- Demo content (Drush command in lms_base) ---
ddev drush lms:generate-demo --courses=5 --chapters=3 --lessons=5
```

### Config sync path

`config/sync/` at repo root (396 YAML files, git-tracked).
`settings.php` declares: `$settings['config_sync_directory'] = '../config/sync';`
(relative to `web/`, resolves to repo root).

### Seed import — `.agent/generated/`

The `.agent/generated/` directory contains AI-generated Vietnamese demo content
(5 courses, 22 chapters, 79 lessons, 5 quizzes, 75 questions, 3 instructors,
15 students). Import order is defined in `.agent/generated/manifest.json`.

Import entry point: **`/workspace/content-tools/seed-input`** (route
`lms_content_tools.seed_input`) — fills `SeedInputForm` which drives the
`lms_content_tools` module's batch importer. Alternatively:

```
# Drush Workspace / Content Tools dashboard route:
https://everlms.ddev.site/workspace/content-tools
```

There is no standalone drush command for seed import — the importer is
invoked through the Drupal form (`lms_content_tools.seed_input`).

---

## Rebuild From Scratch (Bootstrap Sequence)

Use this exact sequence on a fresh clone or a new collaborator machine.

```bash
# 1. Start DDEV (creates containers, writes settings.ddev.php)
ddev start

# 2. Install PHP dependencies
ddev composer install

# 3a. FASTEST — import existing DB dump, then sync config
ddev import-db --file=database/db.sql.gz
ddev drush updatedb -y
ddev drush cim -y
ddev drush cr

# --- OR ---

# 3b. CLEAN INSTALL — install Drupal from scratch using config/sync
ddev drush site:install --existing-config -y
# (installs core, then imports all 396 YAML files from config/sync)
ddev drush cr

# 4. Get a login link
ddev drush uli

# 5. (Optional) Import seed content via browser
#    Navigate to: https://everlms.ddev.site/workspace/content-tools/seed-input
#    Select JSON files from .agent/generated/ in manifest.json order

# 6. (Optional) Fix seeded data if needed
ddev drush php-script scripts/fix_all_categories.php
ddev drush php-script scripts/fix_dummy_quiz_results.php
```

**After any module code change:** `ddev drush cr`
**After any config YAML change:** `ddev drush cim -y && ddev drush cr`
**After any entity schema change:** `ddev drush updatedb -y && ddev drush cr`

---

## Endpoints

| URL | Description |
|---|---|
| `https://everlms.ddev.site` | Public frontend (lms_theme) |
| `https://everlms.ddev.site/admin` | Drupal admin (claro theme) |
| `https://everlms.ddev.site/workspace` | Custom instructor/manager workspace |
| `https://everlms.ddev.site/api/v1/` | Flutter mobile REST API |
| `https://everlms.ddev.site:8025` (or `:8026`) | Mailpit (captures outbound email) |

No `/health` endpoint — Drupal status available at `/admin/reports/status`.

---

## Language & Framework

| Field | Value |
|---|---|
| Language | PHP 8.3 |
| Framework | Drupal 11.3.5 (Entity API, Plugin API, Form API, Event Dispatcher) |
| Test framework | PHPUnit (via Drupal core's phpunit.xml.dist) |
| Linter | PHP_CodeSniffer — `Drupal` + `DrupalPractice` standards |
| Template engine | Twig 3.22.2 |
| CSS framework | Bootstrap 5.3.3 via CDN (dark glassmorphism palette: `#0d1117`, accent `#667eea`/`#764ba2`, font: Inter) |
| PDF generation | mPDF 8.3.1 (certificate PDFs) |
| Mobile backend | Flutter app `app/` v1.0.10+33 ← `lms_mobile_api` (`/api/v1/*`) |

---

## Thresholds

| Metric | Target |
|---|---|
| `coverage_minimum` | 60% (MVP; target 80%) |
| `max_function_lines` | 50 |
| `max_file_lines` | 300 |
| `max_cyclomatic_complexity` | 10 |

---

## Coding Conventions

- `declare(strict_types=1);` in **every** PHP file — no exceptions.
- Use Drupal's **dependency injection** exclusively. Never use `\Drupal::service()`,
  `\Drupal::entityTypeManager()`, or any other static `\Drupal::` call inside
  service/controller classes. Inject via constructor parameters.
- **Constructor promotion** for DI — no manual `$this->prop = $arg` patterns.
- `use` statements for all class references — never inline fully-qualified class names.
- No `#markup` with raw HTML — use `#theme` + Twig or `html_tag` render elements.
  (`#markup` skips XSS filtering and cache tag registration.)
- No `readonly` properties on AJAX form classes — breaks Drupal form rebuild (clone).
- Entity classes **must** declare `'views_data' => EntityViewsData::class` in their
  annotation handlers array.
- **Twig-first rendering** — controllers prepare render arrays with `#theme` keys;
  HTML lives in `.html.twig` templates, not in PHP strings.
- All custom modules use the **`lms_`** prefix (27 modules) except `open_switcher`
  (reusable utility, separate package).
- **Service ID convention:** `lms_{module}.{service_name}` (e.g., `lms_certificate.generator`).
- **Template naming:** `lms-{context}--{variant}.html.twig` (kebab-case).
- **CSS class prefix:** `lms-` for all custom CSS classes.
- **Feature flags:** `FF_{TYPE}_{ID}` (uppercase, underscores) — required for high-risk changes.
- **Commit messages:** conventional commits — `type(scope): description`.
- Dates stored as UTC; timezone `Asia/Bangkok` is the user-configurable default.
- Config sync YAML is the source of truth for site configuration — always `cex`/`cim` symmetrically.

---

## Banned Patterns

| Pattern | Why banned |
|---|---|
| `\Drupal::service(...)` in services/controllers | Violates DI; untestable; breaks container mocking |
| `\Drupal::entityTypeManager()` static call | Same — inject `EntityTypeManagerInterface` |
| `#markup` with raw HTML | XSS risk; skips render cache tag attachment |
| Direct `\Drupal::database()->query(...)` from controllers | Bypass entity access; use entity storage API |
| Hardcoded API keys / secrets in PHP | Already a critical risk in config YAML — do not add more |
| Catch-and-swallow (`catch (\Exception $e) {}`) | Silent failures; always log via injected logger or rethrow |
| `\Drupal::` static calls in service constructors | Circular dependency risk; blocks unit testing |
| Magic numbers in business logic | Use named constants or config values |
| Synchronous external HTTP calls in entity `presave`/hooks | Blocks request; use `QueueWorker` |
| `console.log` / `var_dump` / `print_r` left in code | Use structured Drupal logger; remove debug output before commit |

---

## Gate Sequence

`G1 (Build) → G2 (Lint) → G3 (Tests) → G4 (Adversarial Self-Review)`
Max retries per gate: **3**. No skipping.

### G4 Adversarial Categories (7 — "Looks good" is FORBIDDEN)

1. **Security** — SQL injection, XSS, CSRF, auth bypass, `_access: TRUE` on non-public routes, input validation, secrets in code
2. **Error Handling** — unhandled exceptions, silent failures, missing retry/backoff for external API calls (Stripe, OpenAI, Zoom)
3. **Performance** — N+1 queries on entity loads, missing `addCacheTags`, unbounded result sets, blocking calls in request handlers
4. **Edge Cases** — null/empty inputs, concurrent quiz submit race condition, timezone edge cases, progress cascade on partial failure
5. **Architecture** — layer violations, `\Drupal::` statics, circular module dependencies, controllers >50 lines of logic (delegate to services)
6. **Data Integrity** — missing transactions for order+enrollment pairs, orphaned entities after partial create/delete, cascade correctness
7. **Access Control** — entity access handler coverage, route `_permission` vs controller-only auth (especially `/api/v1/*` which uses `_access: TRUE` then validates bearer token in-controller)

---

## Mandatory Rules

- **D07 code-map**: MANDATORY — update after every Story completion.
- **G4 adversarial**: MANDATORY — all 7 categories examined; Critical/High findings fixed before merge; Medium+ logged to E02-issues/.
- **Feature flags**: REQUIRED (`FF_{TYPE}_{ID}`, default OFF) for high-risk changes.
- **H-Operations**: BASELINE ready by CP5; OPERATIONAL by CP6 (H01–H07 all slots).
- **CP4**: MANDATORY per Epic completion — batch review by SA.
- **Evidence precedence** (when sources conflict): Tests > Internal APIs > Design Docs > Requirements.
- **Entity-first editorial usability**: business-visible content must be editable through entity/config UI with editor role; URL, revision/cache policy, and edit-smoke evidence required.

---

## Security Risk — Secrets in Config (CRITICAL, as-built)

**As of 2026-06-20, the following secrets are stored in plaintext in
`config/sync/` YAML files (git-tracked):**

| Secret | Config key | Severity |
|---|---|---|
| OpenAI API key (`sk-proj-*`) | `lms_ai.settings` | CRITICAL |
| Gemini API key (`AIzaSy*`) | `lms_ai.settings` | CRITICAL |
| Google service account RSA private key (PEM) | `google_api_client.google_api_service_client.everlms_bot` | CRITICAL |
| Stripe test secret key (`sk_test_*`) | `lms_payment.settings` | HIGH |
| Stripe publishable key (`pk_test_*`) | `lms_payment.settings` | HIGH |
| PayPal client ID + secret | `lms_payment.settings` | HIGH |
| Zoom account_id, client_id, client_secret, sdk_key, sdk_secret | `lms_zoom.settings` | HIGH |

**Rule for agents:** NEVER copy, echo, or log any of these values.
Write `«secret in config — see security risk»` wherever a value would appear.
The `drupal/key` module (v1.22.0, installed in composer.lock) has zero
`key.key.*` config files — **the Key module is unused**. Migrating secrets
to Key module + environment variables is a required pre-production task.

---

## Module Map (28 modules — as-built)

Repo path: `web/modules/custom/`

### Foundation

| Module | Responsibility |
|---|---|
| `lms_base` | Foundation: `LmsHelper` service, email-login hook, auto-student-role on register, 8 taxonomy vocabulary install, Drush `lms:generate-demo` command |
| `lms_content_tools` | Content authoring tools: seed import form (`/workspace/content-tools/seed-input`), clean-content form; reads `.agent/generated/` JSON files |
| `open_switcher` | Reusable user-impersonation utility (not EverLMS-specific): `SwitchController` stores/restores session UID; `switch users` permission; routes at `/admin/switch-user` + `/app/switch-user` |

### Content

| Module | Responsibility |
|---|---|
| `lms_course` | Course entity (translatable, owner-uid), public catalog/detail/homepage/category pages, workspace admin hub (`AdminController` owns `/workspace`), breadcrumb builder, course search JSON API |
| `lms_chapter` | Chapter entity (translatable); pure entity/field module — no routing, no services |
| `lms_lesson` | Lesson entity (translatable, supports `comment.lesson_comment`); pure entity/field module — no routing |
| `lms_blog` | Dual-track blog: public `/news` reads core `node:blog`; `lms_article` entity backs `/manage/blog` workspace CRUD |
| `lms_faq` | FAQ entity (translatable); workspace CRUD at `/workspace/content/faqs/*` via `lms_course`'s `WorkspaceContentController` |

### Assessment

| Module | Responsibility |
|---|---|
| `lms_question` | Question entity (bundleable via `lms_question_type` config entity, 8 bundles), `lms_answer` and `lms_matching_pair` sub-entities; pure entity/field module |
| `lms_quiz` | Quiz entity, `lms_quiz_result`, `lms_question_response`; `GradingStrategyFactory` (static PHP factory, NOT a Drupal Plugin manager) dispatches one of 8 strategy classes; strictly binary grading (no partial credit); Essay → `pending_review`; dispatches `QuizPassedEvent` on pass |

### Progress & Communication

| Module | Responsibility |
|---|---|
| `lms_progress` | Enrollment and progress tracking (`lms_user_course`, `lms_user_lesson`); learner portal pages (`/dashboard`, `/my-courses`, `/learn/{course}/{lesson}`); AJAX mark-complete API |
| `lms_note` | Per-lesson student notes (`lms_note` entity); JSON save/delete/get AJAX API + `/my-notes` page |
| `lms_forum` | Community forum: `lms_forum_topic`, `lms_forum_reply` entities; `LmsForumController` overrides canonical topic route via `RouteSubscriber`; **BUG: `ForumController::viewTopic` throws literal string `"HARD DEBUG!"`** (stale duplicate controller) |
| `lms_rating` | Course ratings/reviews (`lms_rating` entity); approve/reject/feature admin actions; **BUG: 3 runtime routes missing** (`lms_rating.toggle_approve`, `lms_rating.toggle_featured`, `lms_rating.collection`) |
| `lms_notification` | In-app notifications (`lms_notification` entity); bell block (`lms_notification_bell_block`); unread-count JSON API; **email and push channels are stubs (not implemented)** |

### Commerce

| Module | Responsibility |
|---|---|
| `lms_order` | Order + order-item entities; `CheckoutController` — course and membership checkout pages; bank transfer instruction page |
| `lms_membership` | Membership plan + user-membership entities; pricing workspace UI; learner `/my-membership` page |
| `lms_payment` | **Payment plugin host**: `PaymentGatewayManager` + `@PaymentGateway` annotation + `PaymentGatewayInterface`; `BankTransferGateway` is real; `PaymentSettingsForm` at `/admin/config/everlms/payment`; **no webhook endpoints exist — signature verification is a TODO** |
| `lms_stripe` | Stripe PaymentIntent gateway (real implementation via `stripe/stripe-php` 19.4.1); **BUG: PaymentIntent is created on form BUILD (one per page load, not on submit)** |
| `lms_paypal` | **Dead code** — wrong interface, missing `@PaymentGateway` annotation, never discovered by plugin manager; PayPal credentials stored in `lms_payment.settings` |
| `lms_wishlist` | Wishlist items (`lms_wishlist` entity); JSON toggle/status API; `/my-wishlist` page |
| `lms_bundle` | Course bundle/package entity (`lms_course_bundle`); pure entity module — no routing, no checkout integration yet |

### Certification & Integrations

| Module | Responsibility |
|---|---|
| `lms_certificate` | Certificate templates + user-certificate entities; `CertificateEventListener` auto-issues on `QuizPassedEvent` (traverses Quiz→Lesson→Chapter→Course); PDF via mPDF; `/certificate/verify` public page; workspace template CRUD |
| `lms_badge` | Badge + user-badge entities; **near-stub — entity structure only, zero award logic implemented** |
| `lms_zoom` | Zoom WebSDK integration (`lms_zoom.settings`); `ZoomController::joinMeeting` renders full-screen WebSDK page; JWT signature endpoint; **BUG: `generateSignature()` references wrong service ID** |
| `lms_ai` | AI plugin manager (`AiProviderManager`, `@AiProvider` annotation): OpenAI (gpt-4o, full), Gemini 2.5 Flash (full), Pollinations (image-only); `CourseBuilderForm` queues AI generation; `CourseBuilderQueueWorker`; **no rollback/compensation on partial generation failure** |
| `lms_analytics` | Admin analytics reports: course enrollment/revenue, user activity, quiz item-analysis; CSV export; routes at `/admin/lms/reports/*` |

### Mobile

| Module | Responsibility |
|---|---|
| `lms_mobile_api` | REST/JSON API at `/api/v1/*` for Flutter app; **custom bearer token auth via `keyvalue.expirable` store + OTP** (NOT OAuth2/JWT — oauth_* config keys in config/sync are unused); `MobileAuthController`, `MobileCourseController`, `MobileLearningController`; 22 routes |

---

## Entity List (29 custom entities)

### 28 Content Entities

| Entity ID | Label | Module | Owner |
|---|---|---|---|
| `lms_article` | Article | lms_blog | — |
| `lms_answer` | Answer | lms_question | — |
| `lms_badge` | Badge | lms_badge | — |
| `lms_certificate_template` | Certificate Template | lms_certificate | — |
| `lms_chapter` | Chapter | lms_chapter | — |
| `lms_course` | Course | lms_course | uid |
| `lms_course_bundle` | Course Bundle | lms_bundle | — |
| `lms_faq` | FAQ | lms_faq | — |
| `lms_forum_reply` | Forum Reply | lms_forum | uid |
| `lms_forum_topic` | Forum Topic | lms_forum | uid |
| `lms_lesson` | Lesson | lms_lesson | — |
| `lms_matching_pair` | Matching Pair | lms_question | — |
| `lms_membership_plan` | Membership Plan | lms_membership | — |
| `lms_note` | Student Note | lms_note | uid |
| `lms_notification` | Notification | lms_notification | uid |
| `lms_order` | Order | lms_order | uid |
| `lms_order_item` | Order Item | lms_order | — |
| `lms_question` | Question | lms_question | — |
| `lms_question_response` | Question Response | lms_quiz | — |
| `lms_quiz` | Quiz | lms_quiz | uid |
| `lms_quiz_result` | Quiz Result | lms_quiz | uid |
| `lms_rating` | Rating | lms_rating | uid |
| `lms_user_badge` | User Badge | lms_badge | uid |
| `lms_user_certificate` | User Certificate | lms_certificate | uid |
| `lms_user_course` | User Course Progress | lms_progress | uid |
| `lms_user_lesson` | User Lesson Progress | lms_progress | uid |
| `lms_user_membership` | User Membership | lms_membership | uid |
| `lms_wishlist` | Wishlist Item | lms_wishlist | uid |

### 1 Config Entity

| Entity ID | Label | Module | Bundle of |
|---|---|---|---|
| `lms_question_type` | Question Type | lms_question | `lms_question` |

**Question bundles (8):** `essay`, `fill_blank`, `matching`, `multiple_choice`,
`ordering`, `short_answer`, `single_choice`, `true_false`.

---

## Taxonomy Vocabularies (11)

`badge_type`, `blog_tags`, `course_category`, `course_language`, `course_level`,
`course_tags`, `faq_category`, `forum_category`, `quiz_difficulty`, `quiz_results`,
`tags`

Created on module install by `lms_base.install` `hook_install()`.

---

## Theme Configuration

| Theme | Role | Notes |
|---|---|---|
| `lms_theme` | **Default (frontend)** | Bootstrap 5.3.3 CDN, dark glassmorphism, Inter font; 15 switchable homepage skin variants (`templates/home/styles/style-*.html.twig`); current default skin: `minimalist` |
| `lms_classic` | Secondary frontend | Alternative classic layout; identical block structure to `lms_theme` |
| `claro` | Admin | Drupal core admin theme |

Homepage skin is controlled by `lms_theme.settings.yml` → `homepage_style`.
Change via `/admin/appearance` or `ddev drush cex` after updating in UI.

---

## Route Summary (137 total)

| Group | Count | Prefix |
|---|---|---|
| Public (anonymous) | 19 | `/homepage`, `/courses`, `/news`, `/forum`, `/checkout/*` |
| Learn / App (authenticated learner) | 21 | `/dashboard`, `/my-*`, `/learn/*`, `/quiz/*`, `/zoom/*` |
| Workspace / Manage (instructor/admin) | 47 | `/workspace/*`, `/manage/*` |
| Drupal /admin | 17 | `/admin/config/lms/*`, `/admin/lms/*`, `/admin/switch-user` |
| API (web + mobile) | 31 | `/api/lms/*`, `/api/courses/*`, `/api/v1/*` |
| Utility | 2 | `/app/switch-user` |

---

## Key Paths (repo-relative)

| Path | Description |
|---|---|
| `web/modules/custom/lms_*/` | 27 LMS modules |
| `web/modules/custom/open_switcher/` | User-impersonation utility |
| `web/themes/custom/lms_theme/` | Default frontend theme (15 homepage skins) |
| `web/themes/custom/lms_classic/` | Alternate frontend theme |
| `web/sites/default/settings.php` | Drupal settings (no DB creds — handled by settings.ddev.php) |
| `config/sync/` | 396 YAML config files (git-tracked, source of truth) |
| `.agent/generated/` | AI-generated seed JSON (git-tracked, 19 files) |
| `.agent/generated/manifest.json` | Seed import order |
| `.agent/seed/seed-input.json` | Seed content specification |
| `scripts/*.php` | 4 operational Drush PHP scripts |
| `database/db.sql.gz` | Compressed DB dump (git-tracked) |
| `app/` | Flutter mobile app (v1.0.10+33) |
| `.agent/docs/` | Canonical project documentation (shareable) |
| `.agent/00-Guidelines/` | Framework reference (SA-private; gitignored) |

---

## Known Bugs (as-built, open)

These are tracked issues logged as of 2026-06-20. Full register: `G-Governance/G02-risk-register.md` (planned, layer G — not yet materialized).

| Bug | Location | Impact |
|---|---|---|
| `ForumController::viewTopic` throws literal string `"HARD DEBUG!"` | `lms_forum/src/Controller/ForumController.php` | Any request to stale controller crashes with 500 |
| 3 runtime routes missing for rating toggle/collection | `lms_rating` — `lms_rating.toggle_approve`, `lms_rating.toggle_featured`, `lms_rating.collection` | Admin rating list operations fail with 404 |
| `ZoomController::generateSignature()` references wrong service ID | `lms_zoom/src/Controller/ZoomController.php` | Zoom WebSDK signature generation throws ServiceNotFoundException |
| `lms_user_certificate.field_expiry_date` is a Drupal `datetime` field (stored as ISO8601 string); any raw integer timestamp comparisons against it silently fail | `lms_certificate` entity field storage | Expiry-date logic must use `DrupalDateTime` objects, not Unix integer comparisons |
| Stripe `PaymentIntent` created on form BUILD, not on submit | `lms_stripe` — `CheckoutController` / Stripe form | One PaymentIntent per page load; orphaned intents on back-button |
| `lms_paypal` is dead code | `lms_paypal/src/` — wrong interface, no `@PaymentGateway` annotation | PayPal gateway never discovered; PayPal credentials in config are unused |

---

## Tier-1 Always-Loaded Files

Load these four files at the start of every agent session:

```
1. .agent/docs/A-Project-Information/A01-CLAUDE.md       ← this file
2. .agent/docs/A-Project-Information/A03-project-context.md
3. .agent/docs/D-Development/D05-session-status.md
4. .agent/docs/D-Development/D06-handoff-protocol.md
```

`tier_2_varies`: load per current phase — see
[A06-workflow-primer.md](./A06-workflow-primer.md) for phase-gated load order.

`budget_rule`: total context < 80% of model context window.

---

*End of A01-CLAUDE.md*
