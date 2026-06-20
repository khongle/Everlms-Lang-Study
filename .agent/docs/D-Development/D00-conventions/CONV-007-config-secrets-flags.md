# CONV-007 — Config, Secrets, and Feature Flags

> **Rule**: Config YAML is for structure and defaults; secrets (API keys, tokens, private keys) must NEVER appear in config YAML. Use the Key module or environment variables instead. Feature flags follow the naming convention `FF_{TYPE}_{ID}` when introduced.
> **Why**: EverLMS currently stores all third-party credentials in `config/sync/` (committed to git). This is a critical security risk. Every new integration must not repeat this pattern. Config sync discipline (cex/cim) must be maintained to keep schema, structure, and defaults in VCS while routing secrets to a safe store.
> **Backed by**: project convention; see security risks R1–R5 confirmed in `the config export (config/sync/)` §8 (G02-risk-register — planned, layer G not yet materialized).

---

## 1. Config Sync Discipline (cex / cim)

EverLMS uses Drupal's config sync at repo root `config/sync/` (396 YAML files, tracked in git). The workflow:

```bash
# Export current DB config to config/sync/
drush cex -y

# Import config/sync/ into the DB (e.g., after pulling)
drush cim -y
```

**Rules:**

| Rule | Detail |
|------|--------|
| Always commit `config/sync/` after any admin UI change that changes structure | Catch: role/permission changes, view changes, entity form/display changes, field additions |
| Never commit with secrets in config | See §2 below |
| Use `config/install/` in module directories for default structure shipped with the module | Example: `lms_question/config/install/lms_question.question_type.essay.yml` |
| Use `config/schema/{module}.schema.yml` for every module config object | Required for config validation |
| Use `hook_update_N()` or `hook_post_update_NAME()` for field/schema changes, not manual DB edits | Example: `lms_quiz_update_10001()` adds `field_prerequisite_quizzes` |

**DDEV context:** Config sync path is set in `settings.php`:

```php
$settings['config_sync_directory'] = '../config/sync';
```

---

## 2. Secrets in Config — Anti-Pattern (as-built violation)

### What is currently wrong (as-built)

The following secrets are stored in plaintext in `config/sync/` and committed to git. **This is a CRITICAL security risk and must not be replicated.**

| Config file | Secret type | Severity |
|-------------|-------------|----------|
| `lms_ai.settings.yml` | OpenAI API key (`sk-proj-***`) | CRITICAL |
| `lms_ai.settings.yml` | Gemini API key (`AIzaSy***`) | CRITICAL |
| `google_api_client.google_api_service_client.everlms_bot.yml` | Google service account RSA private key (PEM) | CRITICAL |
| `lms_payment.settings.yml` | Stripe secret key (`sk_test_***`, test mode) | HIGH |
| `lms_payment.settings.yml` | PayPal client ID + secret | HIGH |
| `lms_zoom.settings.yml` | Zoom client_secret + sdk_secret | HIGH |
| `.env` (repo root) | Stripe/PayPal test credentials | HIGH |

> All values above are REDACTED in this document. Never include actual secret values in any documentation file.

### Required pattern — Key module

The correct pattern is to store secrets in the **Key module** (contrib: `key`). No `key.key.*.yml` files exist in `config/sync/` currently — the Key module is installed but unused (zero keys configured).

```yaml
# config/sync/key.key.openai_api_key.yml  ← key definition (safe to commit: points to env var)
langcode: en
status: true
id: openai_api_key
label: 'OpenAI API Key'
key_type: authentication
key_provider: env            # reads from environment variable
key_provider_settings:
  env_variable: OPENAI_API_KEY
key_input: none
```

```yaml
# lms_ai.settings.yml — after migration (safe to commit)
default_provider: openai
openai_key_id: openai_api_key   # references Key module key by ID
```

```php
// In OpenAiProvider::__construct() (after migration)
$api_key = \Drupal::service('key.repository')->getKey('openai_api_key')->getKeyValue();
```

**Migration path (planned — not yet implemented):**
1. Create Key module key entities pointing to environment variables.
2. Update each settings form to save a `key_id` reference instead of the raw value.
3. Update provider/service classes to retrieve via `key.repository` service.
4. Remove secrets from config YAML and `.env`.
5. Add `.env` to `.gitignore` if not already present.

Until migration is complete, NEVER add new secrets to config YAML. Use `.env` + `$settings['...']` override in `settings.local.php` (not committed) as a temporary measure.

---

## 3. Config Anti-Pattern: Large Data in Config

`lms_ai.engine.settings.yml` contains `curriculum_suggestion` — approximately 758 KB of cached AI-generated curriculum content stored as a config value. This is a misuse of config:

- Config is loaded into memory on every bootstrap for config-reading modules.
- Config is not designed as a blob store — large values slow `drush cex/cim` diffs.
- Large config values cannot be efficiently invalidated or versioned.

**Correct pattern:** Store transient AI outputs as managed files (`public://ai/curriculum_{id}.json`) or in a custom DB table, not in config.

---

## 4. Feature Flags Convention — `FF_{TYPE}_{ID}`

EverLMS does not yet have any feature flags defined (as-built: none exist). When introduced, they must follow this naming convention:

```
FF_{TYPE}_{ID}
```

| Segment | Values | Example |
|---------|--------|---------|
| `FF_` | Fixed prefix | — |
| `{TYPE}` | `FEAT` (new feature), `EXP` (experiment), `KILL` (kill switch), `BETA` | `FEAT`, `EXP` |
| `{ID}` | SCREAMING_SNAKE_CASE identifier | `AI_COURSE_BUILDER`, `STRIPE_WEBHOOK` |

**Full examples (not yet implemented):**

```
FF_FEAT_AI_COURSE_BUILDER
FF_KILL_STRIPE_WEBHOOK
FF_EXP_BADGE_AWARDS
```

**Implementation approach (when needed):** Store in `lms_base.settings` config as boolean values, or use the `state` API for runtime toggles that should not be config-synced:

```php
// State-based toggle (not config-synced — volatile)
$enabled = \Drupal::state()->get('FF_EXP_BADGE_AWARDS', FALSE);

// Config-based toggle (config-synced — permanent)
$enabled = \Drupal::config('lms_base.settings')->get('FF_FEAT_AI_COURSE_BUILDER');
```

Feature flags are evaluated at the call site; they do not alter routing or hook registration at bootstrap.

---

## 5. Config Schema Requirement

Every custom module that defines a config object MUST provide a schema:

```yaml
# lms_question/config/schema/lms_question.schema.yml
lms_question.question_type.*:
  type: config_entity
  label: 'Question type'
  mapping:
    id:
      type: string
      label: 'ID'
    label:
      type: label
      label: 'Label'
    description:
      type: text
      label: 'Description'
```

Settings objects need schema too:

```yaml
# lms_ai/config/schema/lms_ai.schema.yml
lms_ai.settings:
  type: config_object
  label: 'LMS AI settings'
  mapping:
    default_provider:
      type: string
    openai_key_id:      # key reference, NOT the raw key
      type: string
```

---

## Don't

- Do not store any API key, client secret, RSA key, webhook secret, or password in `config/sync/*.yml` or `config/install/*.yml`.
- Do not use `lms_{module}.settings` config to cache large AI-generated content blobs — use file storage or state.
- Do not run `drush cex` without reviewing the diff for accidental secret inclusion.
- Do not use the `state` API for permanent feature flags that need to be version-controlled — use config.
- Do not define feature flag names ad hoc — use `FF_{TYPE}_{ID}` so they are grep-able.
- Do not create a new config object without a matching schema in `config/schema/{module}.schema.yml`.

---

## Checklist

- [ ] New integration stores credentials via Key module key reference, not raw value in config YAML.
- [ ] `drush cex` diff reviewed before commit — no secrets in changed files.
- [ ] Large AI/generated data stored in managed files or DB, not config.
- [ ] New feature flag named `FF_{TYPE}_{ID}` and documented in this file when introduced.
- [ ] Every new config object has a schema entry in `config/schema/{module}.schema.yml`.
- [ ] `config/install/` used for module-default structure; `config/sync/` used for site-level overrides.
- [ ] `.env` is in `.gitignore`; only `.env.example` (no real values) is committed.

---

## Related

[`CONV-005-service-plugin-patterns.md`](CONV-005-service-plugin-patterns.md) · [`C05/lms_ai.md`](../../C-Design/C05-modules/lms_ai.md) · [`C05/lms_payment.md`](../../C-Design/C05-modules/lms_payment.md) · [`C05/lms_zoom.md`](../../C-Design/C05-modules/lms_zoom.md)
