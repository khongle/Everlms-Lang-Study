# A01-CLAUDE.md — AI Agent Configuration

> Per-project AI config. **Tier 1** — load at every session start.

## Project

| Field | Value |
|-------|-------|
| Name | EverLMS |
| Type | Learning Management System |
| Stack | Drupal 11.3 + PHP 8.3 + MariaDB 10.11 + Bootstrap 5.3 |
| Dev Environment | DDEV |
| Created | 2026-02-27 |
| Last updated | 2026-04-16 |

## Commands

```bash
build_cmd:   ddev exec drush cr
test_cmd:    ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/
lint_cmd:    ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/
deps_cmd:    ddev composer install
dev_cmd:     ddev start && ddev launch
config_export: ddev exec drush cex -y
config_import: ddev exec drush cim -y
```

## Endpoints

| Endpoint | Description | Status |
|----------|-------------|--------|
| GET /health | App health check | PROPOSED |
| GET /health/db | Database connectivity | PROPOSED |

## Thresholds

| Metric | Target |
|--------|--------|
| coverage_minimum | 60% (MVP, target 80%) |
| max_function_lines | 50 |
| max_file_lines | 300 |
| max_cyclomatic_complexity | 10 |

## Language & Framework

| Field | Value |
|-------|-------|
| Language | PHP 8.3 |
| Framework | Drupal 11.3 (Entity API, Plugin API, Form API) |
| Test framework | PHPUnit |
| Linter | PHP_CodeSniffer (Drupal + DrupalPractice standards) |
| Theme engine | Twig 3 |
| CSS framework | Bootstrap 5.3 (SCSS compiled via lms_theme) |

## Conventions

- `declare(strict_types=1);` in ALL PHP files
- Constructor Promotion for DI (no manual property assignment)
- `use` statements only — never inline FQN
- No `#markup` — use `html_tag` or `#theme` + Twig
- No `readonly` in AJAX form classes (breaks form rebuild)
- Entity classes MUST have `'views_data' => EntityViewsData::class` in handlers
- Twig-first rendering: controllers prepare render arrays → `#theme` → Twig handles HTML
- All custom modules use `lms_` prefix
- Service ID convention: `lms_{module}.{service_name}`
- Template naming: `lms-{module}--{variant}.html.twig`
- CSS class prefix: `lms-`
- Feature flags: `FF_{TYPE}_{ID}` (see A01 document-structure in Guidelines)

## Banned Patterns

- No `\Drupal::service()` static calls — use DI via constructor promotion
- No `#markup` with raw HTML — XSS risk, cache tag miss
- No direct DB queries from controllers — use entity storage or service layer
- No hardcoded API keys — use Drupal config (`\Drupal::config()`)
- No catch-and-swallow — always log via `\Drupal::logger()` or rethrow
- No `\Drupal::entityTypeManager()` in services — inject `EntityTypeManagerInterface`
- No magic numbers — use named constants or config
- No synchronous external API calls in entity hooks — use QueueWorker

## Gate Sequence

G1 (Build) → G2 (Lint) → G3 (Tests) → G4 (Adversarial Self-Review)
Max retries per gate: 3

## G4 Adversarial Categories

1. **Security** — SQL injection, XSS, CSRF, auth bypass, `_access: TRUE` routes, input validation
2. **Error Handling** — unhandled exceptions, silent failures, retry/backoff for external APIs
3. **Performance** — N+1 queries, missing pagination, no cache tags, blocking SCORM unzip
4. **Edge Cases** — null/empty inputs, race conditions (progress cascade), timezone, concurrent quiz submit
5. **Architecture** — layer violations, `\Drupal::` static calls, circular module deps, oversized controllers
6. **Data Integrity** — missing transactions (order+enrollment), orphaned entities, cascade correctness
7. **Access Control** — entity access handlers, route `_permission` vs controller-only auth

## Mandatory Rules

- D07 code-map: MANDATORY — update after every unit completion
- G4 adversarial: MANDATORY — 7 categories, "Looks good" is FORBIDDEN
- Feature flags: REQUIRED for high-risk changes (`FF_{TYPE}_{ID}`)
- H-Operations: MUST be ready before release (H01-H07)
- CP4: MANDATORY per group completion — batch review by SA
- Source of Truth: Tests > Internal APIs > Design Docs > Requirements

## Context Loading

```
tier_1_always: [A01-CLAUDE.md, A03-project-context.md, D04-session-status.md, D06-handoff-protocol.md]
tier_2_varies: load per current phase (see A06-workflow-primer.md)
budget_rule: total context < 80% of model context window
```

## Key Paths

| Path | Description |
|------|-------------|
| `web/modules/custom/lms_*` | 26 LMS modules (+ `open_switcher` utility) |
| `web/themes/custom/lms_theme/` | Custom theme (Bootstrap 5.3 dark premium) |
| `web/themes/custom/lms_classic/` | Alternative classic theme |
| `config/sync/` | Drupal config export |
| `.agent/docs/` | Project documentation (shareable) |
| `.agent/00-Guidelines/` | Framework reference (SA-private) |

## Entity Count

- 29 entity classes (28 content + 1 config `QuestionType`) across 6 domains (Content, Assessment, Progress, Commerce, Certificate, Communication, Gamification)
- 47 database tables (includes per-entity base + revision + field tables)
- 8 taxonomy vocabularies
- 26 custom modules (`lms_*`) + 1 utility (`open_switcher`) — 27 dirs total
- 2 custom themes (`lms_theme`, `lms_classic`)

---

*End of A01-CLAUDE.md*
