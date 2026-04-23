# DEP (Dependency Update) Playbook 🟠

> Dependency upgrade. SLA: **per CVE severity** (see table below).
> Protocol source: `00-Guidelines/A04-maintenance-workflow.md` §Flow 5.

---

## Trigger Conditions

| Trigger | Example |
|---------|---------|
| CVE alert | CVE-2026-XXXX in `drupal/core` |
| EOL notice | PHP 8.2 EOL; migrate to PHP 8.3 |
| Major version available | Drupal 11 → 12 upgrade path opens |
| Regular update cycle | Composer audit monthly |
| H03 dependency audit finding | Pinned version falls behind by 2 minors |

---

## SLA by CVE Severity

| CVE Severity | CVSS | SLA | Treatment |
|-------------|------|-----|-----------|
| Critical | 9.0+ | < 24 hours | Treat as HOTFIX |
| High | 7.0–8.9 | < 1 week | High priority |
| Medium | 4.0–6.9 | < 2 weeks | Normal priority |
| Low | 0.1–3.9 | Next sprint | Low priority |
| No CVE | — | Planned sprint | Maintenance |

Critical CVE: follow HOTFIX playbook for deploy speed; use DEP documentation requirements.

---

## Feature Flag Requirement

| Condition | Flag Required? |
|-----------|---------------|
| Major version with breaking API changes | Required — `FF_DEP_{ID}` |
| Minor/patch, no breaking changes | Not required |
| Dependency changes serialization / DB schema | Required |

---

## CVE Check Protocol

Before and after every dependency update:

```bash
# PHP / Composer
composer audit

# Node (if applicable)
npm audit --audit-level=high

# Check NVD directly for package-specific CVEs not yet in advisories
```

Confirm: the upgraded version actually resolves the CVE (check CVE description's "Fixed in" version).

---

## Step-by-Step Flow

### Step 1 — TRIAGE

- [ ] Source: CVE database, H03 dependency audit, composer audit output
- [ ] Identify affected dependency, current version, target version
- [ ] Check H03-dependencies.md for existing dependency details and last update
- [ ] Classify: security patch | minor update | major update | EOL migration
- [ ] Create M01 instance file: `DEP-{NNN}.md`

### Step 2 — IMPACT ANALYSIS

- [ ] Read dependency changelog for breaking changes between current and target
- [ ] Use D07 code-map: which files `use` / `import` / `require` this dependency?
- [ ] Check B06 integration requirements: does this affect external integrations?
- [ ] Check E10 NFR tracking: performance implications of new version?
- [ ] Check H05: should update be behind feature flag?
- [ ] Write M03 **full** impact template → `E-Quality/E09-impact-analysis/DEP-{NNN}.md`:
  - Files affected (from D07)
  - API changes (breaking / non-breaking)
  - Migration steps needed
  - Rollback plan

### Step 3 — PLANNING

- [ ] G04 change-request if breaking changes
- [ ] Create D03 unit spec for dependency update
- [ ] Define migration steps:
  1. Update version in `composer.json` / `package.json`
  2. Fix breaking API changes
  3. Update configuration
  4. Update tests
- [ ] If major version: create feature flag plan

### Step 4 — IMPLEMENTATION

- [ ] Update dependency version in manifest
- [ ] Fix any breaking API changes
- [ ] Update configuration files
- [ ] Run full test suite — all must pass
- [ ] If feature flag:
  - Wrap changed APIs: `FeatureFlagManager::isEnabled('FF_DEP_{ID}')`
  - Register in H05, default OFF
  - Test both old and new dependency behavior

### Step 5 — GATES (all mandatory)

| Gate | Requirement |
|------|------------|
| G1 Build | Must pass with new dependency |
| G2 Lint | Must pass (check for new lint rules from dependency) |
| G3 Tests | Must pass; no regressions; full suite run |
| G4 Adversarial | Full 6-category. Focus: SECURITY (CVE actually fixed?), PERFORMANCE (new version performance), DATA_INTEGRITY (serialization changes?) |

### Step 6 — DEPLOY (staged rollout)

| Scenario | Deployment |
|----------|-----------|
| Critical CVE | Deploy immediately via HOTFIX process |
| Breaking changes + feature flag | Deploy OFF → staged rollout |
| Non-breaking, no flag | Deploy with next release cycle |

Staged rollout for breaking changes:
- Stage 1 (10%): monitor 2 hours — error rate, latency
- Stage 2 (50%): monitor 4 hours
- Stage 3 (100%): monitor 24 hours
- Rollback trigger: error rate > baseline by 2× for 15 min

### Step 7 — VERIFY

- [ ] Confirm CVE is resolved (if security update) — re-run `composer audit`
- [ ] Monitor for regressions per M04
- [ ] Update H03-dependencies.md: new version, CVE status, last-updated date
- [ ] Update H04 tech debt (if update was deferred debt)
- [ ] Run dependency audit post-deploy: confirm no new CVEs introduced by upgrade
- [ ] Close M01 instance file

---

## Required Documentation

| Artifact | Required? |
|----------|-----------|
| E02 issue entry | Required |
| D03 unit spec | Required |
| M03 full impact analysis (E09) | Required |
| G04 change-request | If breaking changes |
| E03 regression tests | Required |
| G4 adversarial review (full) | Required |
| H03-dependencies.md update | Required |
| H05 feature flag | If API changes or major version |
| H04 tech debt update | If applicable |
| H01 incident entry | If CVE triggered the update |

---

*Protocol reference: A04 §Flow 5. Gate reference: A03 §Gates. CVE SLA: A04 §SLA Table.*
