# D00 - Drupal 11 Conventions (EverLMS)

**Last updated:** 2026-06-21

## Naming
- **Single module prefix `lms_*`** for all custom modules (+ `open_switcher` utility). Snake_case module + entity machine names.
- Entities: `lms_<noun>` (e.g. `lms_course`); join/user entities `lms_user_<noun>` (e.g. `lms_user_course`).
- Fields: base fields in code via `baseFieldDefinitions()` (the EverLMS norm); configurable fields only where needed (e.g. `field_h5p_upload`). Field names `field_<name>`.
- Routes: `<module>.<route>`; paths grouped (Public `/`, App `/dashboard|/my-*|/learn`, Manage `/workspace|/manage`, API `/api`, Admin `/admin`).
- Pages `P###`, features `F-NNN`, workflows `WF-NNN`, integrations `INT-NNN`, epics `E##`, stories `E##S##`, tasks `E##S##T##`.

## Entity & code patterns
- **PHP 8 attributes** for entity definitions (`#[ContentEntityType]` / `#[ConfigEntityType]`), not annotations.
- **Plugin hosts** for extensibility: `@PaymentGateway` (lms_payment), `@AiProvider` (lms_ai); add a plugin, not a branch.
- **Strategy pattern** for quiz grading (`GradingStrategyFactory` + `QuestionGradingInterface`, one per question type).
- **Events** for cross-module side effects (`QuizPassedEvent` → certificate). Prefer event subscribers over hooks for domain logic.
- Services in `<module>.services.yml`; dependency injection (no `\Drupal::` in classes where avoidable).
- Drush commands under `src/Commands/` or `src/Drush/`.

## Editorial-usability (mandatory)
- No hardcoded editorial content in PHP/Twig/JS/CSS. Every learner-visible value comes from an entity field, config, View, menu, block, or form. UI chrome strings use interface translation.

## Standards & quality
- PSR-4 autoloading; Drupal + DrupalPractice coding standards (`phpcs`).
- Cache metadata on every render (entity tags `lms_*:<id>`, list tags, contexts `user`/`route`/`url`).
- Access in route `_permission`/`_custom_access` + entity access handlers — never only in Twig.

## Security (as-built deviations to fix)
- ⚠️ **Secrets currently in config YAML** (`lms_*.settings.yml`) — should move to Key module/env (R-001/BLK-001). Do not add new secrets to config.
- ⚠️ **No payment webhooks/signature verification** (R-002) — add for any new gateway.

## Config & data
- `config_sync_directory = ../config/sync`; export with `drush cex`, import with `drush cim`.
- Demo/seed via `lms_content_tools` (`lms:import`/`lms:seed-input`/`lms:clean`) — keep generated content reproducible.
