# M02 — Decision Trees

## 1. New Feature Request

```
New feature request received
├── Fits current scope? (check B01 requirements)
│   ├── YES → Create task in D05-tasks/backlog/
│   │         Assign priority (P0-P3)
│   │         Link to requirement ID
│   └── NO  → Add to out-of-scope backlog
│             Review at next planning cycle
```

## 2. Bug Report Triage

```
Bug reported
├── Reproducible?
│   ├── NO  → Request more info, close if stale >7 days
│   └── YES → Assess severity
│       ├── Sev1 (system down / data loss / security breach)
│       │   → Immediate fix, block release if needed
│       │   → Add to G03-blockers.md
│       ├── Sev2 (major feature broken, no workaround)
│       │   → Fix in current sprint
│       │   → Add to E-Quality issues
│       ├── Sev3 (feature broken, workaround exists)
│       │   → Schedule in next sprint
│       └── Sev4 (minor / cosmetic)
│           → Add to backlog
```

## 3. Which Test to Write

```
What are you testing?
├── Pure logic (no Drupal deps)
│   → Unit test (PHPUnit)
├── Entity CRUD / DB / Services
│   → Kernel test (KernelTestBase)
├── Full page / form / auth flow
│   → Functional test (BrowserTestBase)
├── Multi-step user flow (E2E)
│   → Playwright test
└── Performance concern
    → Benchmark in E01 targets
```

## 4. Adding a New Module

```
Need new functionality
├── Fits in existing module?
│   ├── YES → Add to existing module
│   └── NO  → Create new lms_* module
│       ├── Define entities needed → C04
│       ├── Define services needed
│       ├── Set module dependencies (only downward layers)
│       ├── Update C03-entity-module-map.md
│       ├── Update H03-dependencies.md
│       └── Create task(s) in D05-tasks/
```

## 5. Handling a Security Finding

```
Security issue found
├── Severity?
│   ├── Critical (auth bypass, injection, data leak)
│   │   → Fix immediately
│   │   → Add to G02-risk-register.md
│   │   → Block release until resolved
│   ├── High (privilege escalation, CSRF)
│   │   → Fix within sprint
│   │   → Track in H04-tech-debt.md
│   └── Medium/Low (info disclosure, hardening)
│       → Add to backlog
│       → Track in H04-tech-debt.md
```

## 6. Deployment Decision

```
Ready to deploy?
├── Run F01-deployment.md checklist
│   ├── All P0 requirements verified? → Continue
│   ├── Security checklist pass? → Continue
│   ├── No Sev1/Sev2 open? → Continue
│   ├── Performance baseline met? → Continue
│   ├── Rollback plan tested? → Continue
│   └── All pass?
│       ├── YES → GO — proceed with deployment
│       └── NO  → NO-GO — fix blockers first
```

---

*End of M02-decision-trees.md*
