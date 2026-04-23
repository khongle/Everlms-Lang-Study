# G01 — Architecture Decision Records (ADRs)

> Index of all Architecture Decision Records for EverLMS. Each ADR documents a significant technical decision, its context, rationale, alternatives considered, and consequences.

---

## ADR Index

| ID | Title | Status | Date | Supersedes |
|----|-------|--------|------|------------|
| [ADR-001](ADR-001-drupal-11-monolith.md) | Drupal 11 Entity API + Monolithic Deployment | ACCEPTED | 2026-03-08 | — |
| [ADR-002](ADR-002-php-83-strict-types.md) | PHP 8.3 với Strict Types Bắt Buộc và Constructor Promotion | ACCEPTED | 2026-03-08 | — |
| [ADR-003](ADR-003-twig-first-rendering.md) | Twig-First Rendering (Controller → #theme → Twig) | ACCEPTED | 2026-03-08 | — |
| [ADR-004](ADR-004-bootstrap-5-dark-premium.md) | Bootstrap 5.3 Dark Premium Theme thay vì Tailwind CSS | ACCEPTED | 2026-03-08 | — |
| [ADR-005](ADR-005-question-handler-strategy.md) | Strategy Pattern cho Question Grading (8 loại câu hỏi) | ACCEPTED | 2026-03-08 | — |
| [ADR-006](ADR-006-ai-provider-plugin-architecture.md) | AI Provider Drupal Plugin Architecture | ACCEPTED | 2026-03-08 | — |
| [ADR-007](ADR-007-payment-gateway-interface.md) | PaymentGatewayInterface + PaymentManager (Stripe & PayPal là Adapter) | ACCEPTED | 2026-03-08 | — |
| [ADR-008](ADR-008-progress-cascade-strategy.md) | Progress Cascade Strategy (Lesson → Chapter → Course) | ACCEPTED | 2026-03-08 | — |
| [ADR-009](ADR-009-api-input-validation-strategy.md) | API Input Validation Strategy (Per-Route DTO vs Global Middleware) | PROPOSED | 2026-04-16 | — |
| [ADR-010](ADR-010-progress-race-condition-strategy.md) | Progress Race Condition Strategy (DB Row Lock vs Queue Worker) | PROPOSED | 2026-04-16 | — |

---

## Status Definitions

| Status | Meaning |
|--------|---------|
| **PROPOSED** | Draft — awaiting SA decision. Documents trade-offs for review. |
| **ACCEPTED** | SA-approved and implemented (or implementation underway). |
| **DEPRECATED** | Previously accepted but no longer current. Superseded by a newer ADR. |
| **SUPERSEDED** | Replaced by another ADR — see "Supersedes" column. |

---

## Open Decisions Requiring SA Action

| ADR | Decision ID | Question | SA Action |
|-----|-------------|----------|-----------|
| ADR-009 | DEC-001 | API validation: Per-Route DTO vs Global Middleware | Choose Option A, B, or hybrid |
| ADR-010 | DEC-003 | Race condition: DB Row Lock vs Queue Worker vs Optimistic Lock | Choose Option A, B, or C |

---

## Quick Reference: Key Patterns

| Pattern | ADR | Module(s) |
|---------|-----|-----------|
| Drupal Entity API monolith | ADR-001 | all `lms_*` |
| `declare(strict_types=1)` + Constructor Promotion | ADR-002 | all `lms_*` |
| Controller → `#theme` → Twig | ADR-003 | all controllers |
| Bootstrap 5.3 dark premium | ADR-004 | `lms_theme` |
| Grading Strategy Factory (8 question types) | ADR-005 | `lms_quiz`, `lms_question` |
| AI Provider Plugin Manager | ADR-006 | `lms_ai` |
| Payment Gateway Plugin + Adapter | ADR-007 | `lms_payment`, `lms_stripe`, `lms_paypal` |
| Progress Cascade Service | ADR-008 | `lms_progress` |

---

## Adding a New ADR

1. Create file: `ADR-{NNN}-{kebab-title}.md`
2. Use template from `00-Guidelines/A08-layer-templates/07-g-governance.md` §G01
3. Set Status: PROPOSED initially
4. Add row to index table above
5. Link from `A-Project-Information/A03-project-context.md` if it resolves an open decision

---

*Last updated: 2026-04-16*
