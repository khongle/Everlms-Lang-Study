# Pre-Release Checklist — v{VERSION}

> Complete before any production deploy (or named milestone freeze).
> All items must be checked or explicitly waived with documented reason.

---

**Release:** v{VERSION}
**Date:** {YYYY-MM-DD}
**Release Manager:** {SA / Agent_PM}
**Type:** Production Release | RC | Internal Milestone
**Branch:** {release/{version} or main}

---

## Gate G1 — Build

- [ ] `composer install --no-dev` passes with zero errors
- [ ] Drupal install completes (config import clean): `drush config:import --preview`
- [ ] No PHP fatal errors on bootstrap: `drush status`
- [ ] All custom module hooks implemented without errors

## Gate G2 — Lint & Static Analysis

- [ ] PHP CS Fixer passes: `php-cs-fixer fix --dry-run --diff`
- [ ] PHPStan (or equivalent) passes with zero high-severity findings
- [ ] Twig templates lint clean
- [ ] No `@TODO` / `@FIXME` in release scope (or explicitly triaged to H04)

## Gate G3 — Tests

- [ ] Full PHPUnit suite passes: `php vendor/bin/phpunit`
- [ ] Coverage at or above threshold (target: `{threshold}%`, actual: `____%`)
- [ ] All regression tests pass (E03 test definitions verified)
- [ ] No skipped tests without documented reason

## Gate G4 — Adversarial Review

- [ ] G4 review completed for all units in release scope
- [ ] Zero Critical/High open G4 findings (E02-issues)
- [ ] Medium/Low findings tracked in H04 tech debt

---

## Dependency CVE Scan

- [ ] `composer audit` output: Critical `___` / High `___` / Medium `___`
- [ ] Zero Critical CVEs (block release if any)
- [ ] Zero High CVEs (block release unless documented exception with SA approval)
- [ ] All licenses reviewed: `composer licenses` — no GPL incompatibilities
- [ ] H03-dependencies.md updated with current versions and CVE status

---

## Config Diff Review

- [ ] Sync config export from staging: `drush config:export`
- [ ] `git diff config/sync/` reviewed — no unexpected changes
- [ ] All config changes are intentional and documented in F03 changelog
- [ ] No hardcoded environment values in config YAMLs
- [ ] Feature flags: all new flags present in config, default OFF
- [ ] H05-feature-flags.md updated with all flags for this release

---

## Database Backup

- [ ] Production database backup triggered and verified: `{backup_cmd}`
- [ ] Backup location confirmed: `{backup_path}`
- [ ] Backup restoration tested (last test date: `{date}`)
- [ ] DB migration scripts reviewed: `drush updatedb --yes` (dry run on staging)
- [ ] Migration is reversible OR forward-only plan documented

---

## Acceptance Criteria (B05)

- [ ] All Must-have features implemented (B03)
- [ ] All Must-have acceptance criteria passing (B05)
- [ ] Should-have features: implemented or deferred (deferred items in backlog with reason)
- [ ] E05 traceability 100% for Must and Should features

---

## Documentation Readiness

- [ ] F03 changelog written with all changes for this release
- [ ] D07 code-map complete and matches implementation
- [ ] F01 deployment config current
- [ ] H02 rollback runbook tested in staging
- [ ] API docs updated (if public API changed)

---

## Comms Draft

- [ ] Internal release note drafted (for team / SA)
- [ ] If public-facing: external release note drafted and reviewed
- [ ] Rollback communication plan: who to notify if rollback needed
- [ ] On-call schedule confirmed for deploy day + 48 hours

---

## Feature Flags Pre-Deploy State

| Flag ID | Feature | Default State | Rollout Plan | Cleanup Sprint |
|---------|---------|--------------|-------------|----------------|
| FF_{TYPE}_{ID} | {feature} | OFF | {schedule} | Sprint {N} |

- [ ] All new flags: default OFF confirmed in H05
- [ ] All flags to be enabled: SA has approved rollout schedule
- [ ] No orphaned flags (flags in H05 with no cleanup date past 2 sprints)

---

## Sign-Off

| Role | Name | Approved | Date |
|------|------|----------|------|
| SA | {name} | [ ] APPROVED | {date} |
| Agent_PM | Agent | [ ] APPROVED | {date} |

**Decision:** PROCEED TO DEPLOY | HOLD — {reason}

---

*Post-deploy: complete TEMPLATE-post-release.md within 24 hours. Monitoring: M04-post-release-monitoring.md.*
