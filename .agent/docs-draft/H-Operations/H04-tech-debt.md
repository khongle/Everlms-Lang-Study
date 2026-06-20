# H04 — Technical Debt Register

## Debt Items

| Debt ID | Severity | Category | Description | Source | Remediation | Task | Status |
|---------|----------|----------|-------------|--------|-------------|------|--------|
| DEBT-001 | P0 | Security | API input validation gaps trên write endpoints | Code review | Add validation layer per endpoint | TASK-015 | OPEN |
| DEBT-002 | P0 | Security | Notification route access hardening | Code review | Implement proper access handlers | TASK-016 | OPEN |
| DEBT-003 | P0 | Reliability | Progress race condition handling | Testing | DB transactions + optimistic locking | TASK-017 | OPEN |
| DEBT-004 | P1 | Maintainability | Static service calls bypass DI | Code review | Refactor to constructor injection | TASK-018 | OPEN |
| DEBT-005 | P1 | Security | Entity access handler inconsistencies | Audit | Comprehensive access handler audit | TASK-019 | OPEN |
| DEBT-006 | P1 | Observability | Missing logging on critical operations | Code review | Add structured logging | TASK-020 | OPEN |
| DEBT-007 | P1 | Performance | No cache strategy for catalog/reports | Perf test | Implement cache tags + invalidation | TASK-021 | OPEN |
| DEBT-008 | P1 | Performance | N+1 queries in analytics/progress | Perf test | Query optimization + eager loading | TASK-022 | OPEN |
| DEBT-009 | P1 | Security | CSRF audit incomplete | Security audit | Token verification on all state-changing ops | TASK-024 | OPEN |
| DEBT-010 | P1 | Maintainability | Error handling inconsistent across modules | Code review | Standardize exception hierarchy | TASK-025 | OPEN |
| DEBT-011 | P2 | Architecture | AI provider abstraction incomplete | Design | Plugin system with feature flags | TASK-012 | OPEN |
| DEBT-012 | P2 | UX | Mobile responsive gaps | Testing | Responsive audit + CSS fixes | TASK-028 | OPEN |

## Summary

| Severity | Count | Blocking Release? |
|----------|-------|------------------|
| P0 | 3 | Yes — must fix before v1.0 |
| P1 | 7 | Should fix, can defer with approval |
| P2 | 2 | No — backlog ok |

## Debt Reduction Plan

- **Sprint N (current)**: Address P0 items (TASK-015, -016, -017)
- **Sprint N+1**: Address P1 security items (TASK-019, -024)
- **Sprint N+2**: Address P1 performance + maintainability
- **Post-release**: P2 items in backlog

---

*End of H04-tech-debt.md*
