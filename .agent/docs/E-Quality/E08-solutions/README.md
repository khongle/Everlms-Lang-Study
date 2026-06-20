# E08-solutions - Solution Patterns

Reusable solution patterns discovered during the build.

| Pattern | Where | Summary |
| ------- | ----- | ------- |
| Plugin host | lms_payment / lms_ai | Add a gateway/provider as a plugin, not a branch (`@PaymentGateway`, `@AiProvider`). |
| Strategy + factory | lms_quiz | One grading strategy per question type via `GradingStrategyFactory`. |
| Event-driven side effects | lms_certificate | `QuizPassedEvent` subscriber issues certificate (idempotent). |
| Editor-managed layout → PDF | lms_certificate | Template entity rendered by mPDF; branding stays with editors. |
| Reproducible seed | lms_content_tools | JSON generators + Drush import/clean for a deterministic demo dataset. |

> Add a row when a pattern is reused ≥2×.
