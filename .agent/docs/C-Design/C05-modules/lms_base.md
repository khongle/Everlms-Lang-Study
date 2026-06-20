---
module: lms_base
service: lms_base.helper
route-groups: []
status: DONE
---

# C05 — Module: LMS Base

**Directory:** `web/modules/custom/lms_base/`
**Responsibility:** Foundation module that provides shared utility services, installs the 8 core taxonomy vocabularies, and supplies a Drush demo-content generator used by all other EverLMS modules.

> documentation-mode: **as-built** — all described code matches the current implementation.

## Route Bindings

No routing.yml exists. `lms_base` defines no web routes; it is a pure services/hooks/install module.

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_base.helper` | Service | `LmsHelper` — utility methods for UID lookup, role check, taxonomy tree, duration formatting, slug generation. Injected by downstream modules via service container. |
| `lms_base.commands` | Drush service | `LmsDemoContentCommand` — generates demo courses/chapters/lessons via `lms:generate-demo`. Declared in `drush.services.yml`. |

### LmsHelper method summary

| Method | Signature | Description |
|--------|-----------|-------------|
| `getCurrentUserId` | `(): int` | Returns current user UID via AccountProxy. |
| `currentUserHasRole` | `(string $role): bool` | Checks whether the current user carries the given role. |
| `getTermsByVocabulary` | `(string $vid): array` | Loads full term tree for a vocabulary (returns `TermInterface[]`). |
| `getTermName` | `(int $tid): string` | Returns taxonomy term label by TID, or empty string if not found. |
| `formatDuration` | `(int $minutes): string` | Converts minutes to human-readable "Xh Ym" / "Xh" / "Ym". |
| `generateSlug` | `(string $string): string` | Converts arbitrary string to lowercase URL-safe slug. |

## Internal Structure

```
web/modules/custom/lms_base/
+-- lms_base.info.yml          # name=LMS Base; package=EverLMS; core ^11; stable
+-- lms_base.services.yml      # registers lms_base.helper (LmsHelper)
+-- drush.services.yml         # registers lms_base.commands (LmsDemoContentCommand)
+-- lms_base.module            # hook_user_insert, hook_form_alter, hook_entity_presave,
|                              #   hook_user_login + custom login-with-email validator
+-- lms_base.install           # hook_install (8 vocabs + seed terms),
|                              #   hook_uninstall, _lms_base_create_terms() helper
+-- src/
|   +-- Service/
|   |   +-- LmsHelper.php      # Utility service (getCurrentUserId, getTermsByVocabulary, …)
|   +-- Commands/
|       +-- LmsDemoContentCommand.php  # Drush lms:generate-demo (alias: lms-gen)
```

No Form/, Controller/, Entity/, Plugin/, templates/, config/install/, or config/schema/ directories exist.

### Hook implementations

| Hook | File | What it does |
|------|------|-------------|
| `hook_user_insert` | `.module` | Auto-assigns `student` role to newly registered users who only carry `authenticated`. |
| `hook_form_alter` | `.module` | `user_login_form`: relabels username field to "Email address", adds email-to-username validator. `user_register_form`: adds placeholders, hides profile fields for anonymous. `user_pass`: adds placeholder. |
| `lms_base_user_login_validate` | `.module` | Custom form validator — resolves email address to Drupal username before core validation fires. |
| `hook_user_login` | `.module` | Clears lingering login-error messages after a successful login; re-adds non-auth error messages. |
| `hook_entity_presave` | `.module` | Intentional stub with comment — relies on standard Drupal logic. |
| `hook_install` | `.install` | Creates 8 taxonomy vocabularies (course_category, course_level, course_language, course_tags, quiz_difficulty, quiz_results, faq_category, badge_type) if absent; seeds default terms including nested course_category hierarchy (Programming → Frontend / Backend / Mobile). |
| `hook_uninstall` | `.install` | Deletes all 8 vocabularies created by install. |

### Drush command

| Command | Alias | Options | Description |
|---------|-------|---------|-------------|
| `lms:generate-demo` | `lms-gen` | `--courses` (default 5), `--chapters` (default 3), `--lessons` (default 5) | Creates N courses, each with M chapters, each with K lessons using hardcoded Vietnamese demo content. |

> **Note (field direction):** The demo generator writes `field_chapter` on `lms_lesson` (lesson → chapter reference) and `field_course` on `lms_chapter` (chapter → course reference) — the inverse of the parent→child reference pattern used by `lms_content_tools` importer. This inconsistency should be reviewed. See [Known Issues](../../../E-Quality/E02-issues/).

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `entity_type.manager` | Drupal core service | Entity CRUD in LmsHelper and LmsDemoContentCommand. |
| `current_user` | Drupal core service | AccountProxy injection in LmsHelper. |
| _(none — custom)_ | Internal | lms_base has no upstream custom-module dependency by design; it is the dependency graph root. |

**RULE:** No circular dependencies. All other `lms_*` modules may depend on `lms_base`; `lms_base` must not depend on any of them.
