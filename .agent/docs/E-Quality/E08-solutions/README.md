---
layer: E08
title: Solutions — Reusable Solution Patterns
updated: 2026-06-20
---

# E08 — Solutions

> Reusable solution patterns distilled from as-built EverLMS code. One file per pattern. Each file documents the problem the pattern solves, the real code that implements it, when to apply it, and its known trade-offs. Use this index when designing new features or refactoring existing modules.

---

## Pattern Registry

| ID | File | Pattern Type | Primary Module(s) | Problem Solved | Status |
|----|------|-------------|-------------------|----------------|--------|
| SOL-001 | [PATTERN-001-static-grading-strategy-factory.md](./PATTERN-001-static-grading-strategy-factory.md) | Strategy + Static Factory | `lms_quiz` | Dispatch grading logic across 8 question bundles without DI overhead | ACTIVE |
| SOL-002 | [PATTERN-002-plugin-provider-discovery.md](./PATTERN-002-plugin-provider-discovery.md) | Drupal DefaultPluginManager + Annotation | `lms_payment`, `lms_ai` | Injectable, extensible, metadata-bearing provider registration | ACTIVE |
| SOL-003 | [PATTERN-003-event-driven-side-effects.md](./PATTERN-003-event-driven-side-effects.md) | Symfony EventDispatcher (event + subscriber) | `lms_quiz` (producer), `lms_certificate` (reactor) | Cross-module side effects without hard dependency from producer to reactor | ACTIVE |
| SOL-004 | [PATTERN-004-custom-bearer-token-api.md](./PATTERN-004-custom-bearer-token-api.md) | keyvalue.expirable + OTP + custom bearer | `lms_mobile_api` | Stateless mobile API auth without OAuth2 contrib; X-Authorization header workaround | ACTIVE |

---

## Choosing a pattern

```
Do your implementations need injected Drupal services?
  └─ No  → SOL-001 (Static Factory)
  └─ Yes → SOL-002 (Plugin Manager)

Does Module A need to notify Module B without knowing about B?
  └─ Yes → SOL-003 (Event/Subscriber)

Do you need stateless bearer token auth for a mobile app?
  └─ Yes, no OAuth2 infra → SOL-004 (keyvalue token)
```

---

## When to add an E08 entry

- After resolving a complex E02 issue where the fix pattern will recur.
- When implementing a cross-cutting architectural pattern for the first time.
- When a code review (D06 handoff, retrospective E07) surfaces a recurring design approach worth documenting.

---

## Cross-references

| Related layer | Link |
|---------------|------|
| Issues (source of patterns) | `E-Quality/E02-issues/` |
| Retrospectives | `E-Quality/E07-retrospectives/` |
| Module specs (design context) | `C-Design/C05-modules/` |
| Stories (delivery context) | `D-Development/D03-stories/` |
| Architecture decisions | `C-Design/C01-architecture-decision.md` |
| K03 architecture patterns | (planned, layer K — not yet materialized) |
| K04 lessons | (planned, layer K — not yet materialized) |
