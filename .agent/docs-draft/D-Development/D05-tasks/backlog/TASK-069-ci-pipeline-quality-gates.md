# Task: CI/CD Pipeline (GitLab CI) with G1-G4 Gates

**ID:** TASK-069
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Author `.gitlab-ci.yml` cho repo tại `gitlab.weebpal.com` với đầy đủ stages tương ứng G1-G4 quality gates: build (composer + npm) → lint (phpcs + ESLint) → static analysis (phpstan) → test (phpunit + behat + e2e) → security (composer audit + retire.js). Merge request bị block khi bất kỳ stage nào fail. Cache được cấu hình để giảm build time.

## Acceptance Criteria
- [ ] Stage `build`: `composer install --no-dev --optimize-autoloader`; `npm ci` (nếu có package.json); `drush cr` health check
- [ ] Stage `lint`: `phpcs --standard=Drupal,DrupalPractice web/modules/custom/`; `eslint web/themes/custom/lms_theme/js/`; Twig lint
- [ ] Stage `static`: `phpstan analyse --level=6 web/modules/custom/` — zero high-severity findings to merge
- [ ] Stage `test`: PHPUnit suite (`php vendor/bin/phpunit`); Behat (`vendor/bin/behat`); Playwright E2E (TASK-068 spec files)
- [ ] Stage `security`: `composer audit --no-dev` — block on Critical CVE; `npx retire --path web/themes/custom/lms_theme/js/`
- [ ] Pipeline caches: `vendor/` (keyed by `composer.lock` hash), `node_modules/` (keyed by `package-lock.json` hash)
- [ ] Merge requests: pipeline must pass before merge (branch protection rule documented)
- [ ] Artifacts: PHPUnit XML coverage report, Behat HTML report, Playwright HTML report — expire 7 days
- [ ] Environment variables: `CI_DB_*`, `CI_DRUPAL_HASH_SALT` via GitLab CI/CD Settings (documented in `H01-infrastructure.md`)
- [ ] Pipeline runs trong ≤15 minutes total cho typical PR (lint + static + unit tests)

## Technical Notes
- `.gitlab-ci.yml` tại project root
- Base Docker image: `php:8.3-cli` + Composer 2 + Node 20 (custom image or official)
- Services: `mariadb:10.11` (matching production per project context)
- Drupal install strategy cho test stage: `drush si minimal --db-url=...` (fast) hoặc import fixture DB
- phpcs config: `.phpcs.xml` tại project root (Drupal + DrupalPractice standards)
- phpstan config: `phpstan.neon` tại project root
- ESLint config: `.eslintrc.json` (hoặc inherit từ `lms_theme`)
- Behat config: `behat.yml` tại project root, Mink + Goutte driver
- Cache strategy: GitLab CI `cache:` with `key: ${CI_COMMIT_REF_SLUG}` + fallback to default branch cache
- Reference: TEMPLATE-pre-release.md §Gate G1–G4 cho gate definitions

## Dependencies
- TASK-068 (Playwright E2E — test stage depends on e2e specs)
- TASK-067 (E03 test definitions — Behat scenarios map to E03)

## Definition of Done
- [ ] `.gitlab-ci.yml` committed và pipeline green trên `main` branch
- [ ] Có ít nhất 1 failed MR demo (lint error) để verify blocking works
- [ ] Cache hit rate >50% sau lần build thứ 2 (verify trong GitLab CI job log)
- [ ] Pipeline time documented: ≤15 min cho lint+static+unit; ≤25 min full
- [ ] `H01-infrastructure.md` updated với CI environment variable list
