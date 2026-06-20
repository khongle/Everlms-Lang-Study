# K03-architecture-patterns - Architecture Patterns

The recurring patterns that define EverLMS (see [C01](../../C-Design/C01-architecture-decision/_index.md), [E08](../../E-Quality/E08-solutions/README.md)).

| Pattern | Implementation | Why |
| ------- | -------------- | --- |
| Modular monolith | one Drupal install, 28 `lms_*` modules | transactional consistency, simple ops (ADR-002) |
| Custom entities | content/config entities per domain noun | precise schema/access (ADR-002) |
| Plugin host | `@PaymentGateway`, `@AiProvider` | swappable backends without caller changes (ADR-003) |
| Strategy + factory | quiz grading per question type | extensible grading (ADR-003) |
| Event-driven side effects | `QuizPassedEvent` → certificate | decouple assessment from certification |
| Editor-managed content → render | entity fields + Twig; mPDF for certs | editorial usability invariant |
| Reproducible seed | JSON generators + Drush (`lms_content_tools`) | deterministic demo/QA data |
| Multi-surface | shared services → web theme + `/api/v1` (Flutter) | one source of truth, two clients |

## Anti-patterns observed (to fix)
- Secrets in config (R-001); duplicate module (`lms_paypal`, R-004); synchronous-only payment (no webhooks, R-002); no automated tests (R-006).
