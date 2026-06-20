# K02-coding-conventions - Coding Conventions

Distilled from [D00-conventions](../../D-Development/D00-conventions/README.md). Enforced by `phpcs --standard=Drupal,DrupalPractice`.

- **Prefix:** all custom modules `lms_*` (+ `open_switcher`); entities `lms_<noun>` / `lms_user_<noun>`; snake_case.
- **Entities:** PHP 8 attributes (`#[ContentEntityType]`); fields as base fields in code (`baseFieldDefinitions`); configurable fields only when needed.
- **Extensibility:** plugin hosts (`@PaymentGateway`, `@AiProvider`), strategy factory (grading), events (`QuizPassedEvent`) over hooks for domain logic.
- **Services:** DI via `*.services.yml`; avoid `\Drupal::` in classes; Drush commands under `src/Commands`.
- **Rendering:** cache metadata on every render; access in route/entity handlers, not Twig; no hardcoded editorial content.
- **Security:** no secrets in code/config (deviation R-001 to fix); server-side payment/AI calls; CSRF on forms; bearer auth on API.
- **IDs/paths:** stable canonical IDs; numeric IDs never renumbered; folder-per-slot in docs.
