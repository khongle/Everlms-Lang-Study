# ISSUE-001 — Third-Party Secrets Committed in Config YAML (No Key Module)

| Field | Value |
|---|---|
| ID | `ISSUE-001` |
| Title | Third-party secrets committed in config YAML — no Key module |
| Source | G4-Security / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `CRITICAL` |
| Affected page / route | `config/sync/lms_ai.settings.yml`, `config/sync/lms_payment.settings.yml`, `config/sync/lms_zoom.settings.yml`, `config/sync/google_api_client.google_api_service_client.everlms_bot.yml` |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`hardening-security`](../../../D-Development/D03-stories/hardening-security.md) |
| Owning module(s) | `lms_ai`, `lms_payment`, `lms_stripe`, `lms_zoom` |

## Summary

All third-party API credentials — OpenAI API key, Gemini API key, a Google service-account RSA private key (PEM), Stripe test secret key, PayPal client secret, Zoom `client_secret` and `sdk_secret` — are stored in **plaintext inside committed Drupal config YAML files** under `config/sync/`. Because `config/sync/` is tracked by git, these secrets are exposed to anyone with repository access and are captured in git history. No Drupal Key module (`key.key.*.yml`) is present in the config export; all values live directly in module settings config objects. Additionally, a `.env` file containing Stripe and PayPal test credentials is committed to the repository root.

Affected config files and credential types:

| Config file | Secret(s) stored |
|---|---|
| `lms_ai.settings.yml` | OpenAI API key (`sk-proj-***`), Gemini API key (`AIzaSy***`) |
| `lms_payment.settings.yml` | Stripe test secret key (`sk_test_***`), Stripe publishable key (`pk_test_***`), PayPal client ID, PayPal secret |
| `lms_zoom.settings.yml` | Zoom `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret` |
| `google_api_client.google_api_service_client.everlms_bot.yml` | Google service-account RSA private key (PEM), `private_key_id`, OAuth `access_token` |

All values are **REDACTED** in this document — see `config/sync/` for actual structure; do not log or display raw values.

## Reproduction

1. Clone the repository (any branch).
2. Inspect `config/sync/lms_ai.settings.yml` — the `openai_api_key` and `gemini_api_key` fields contain live credential strings.
3. Inspect `config/sync/lms_payment.settings.yml` — `stripe_secret_key` (`sk_test_*`) and `paypal_secret` contain live test credentials.
4. Inspect `config/sync/google_api_client.google_api_service_client.everlms_bot.yml` — `private_key` field contains a multi-line PEM RSA private key.
5. Inspect `config/sync/lms_zoom.settings.yml` — `client_secret` and `sdk_secret` present.
6. Search git log: `git log --all -p -- config/sync/lms_ai.settings.yml` — credentials appear in history even if removed from HEAD.
7. Confirm no `key.key.*.yml` files exist: `find config/sync -name 'key.key.*'` returns empty.

## Root Cause

The Drupal Key module was not integrated into the project setup. Module settings forms (e.g., `PaymentSettingsForm`, the AI settings form, Zoom settings) store credentials directly into their config objects (`lms_payment.settings`, `lms_ai.settings`, `lms_zoom.settings`). Drupal's standard `config:export` / `config:import` workflow then captures these values as plain YAML and commits them to the repository. The Google service-account client was configured via the `google_api_client` module, which stores the full service-account JSON (including RSA key) in config entity YAML.

There is also no `.gitignore` rule to exclude `lms_ai.settings.yml`, `lms_zoom.settings.yml`, or the `.env` file from version control.

## Proposed Fix

1. **Install Drupal Key module** (`drupal/key`). Define a Key entity for each credential type (e.g., `key.key.openai_api_key`, `key.key.stripe_secret_key`).
2. **Store actual secret values outside VCS** — use environment variables (via `settings.php`) or a secrets manager. Key module reads from env var or file path.
3. **Update settings forms** — replace raw text-field credential inputs with Key module reference selectors. Module config YAML then stores only the Key entity ID (a non-secret reference), not the raw value.
4. **Rotate all currently-exposed credentials** immediately — the git history retains the plaintext values; rotation is mandatory regardless of Key module adoption.
5. **Add `.gitignore` rule** for `.env` file; audit `config/sync/` for any remaining secrets.
6. **Remove `lms_ai.engine.settings.yml`** `curriculum_suggestion` (~758 KB cached AI response) from config export — store in State API or cache table instead.

## Trace links

- **Story:** `D-Development/D03-stories/hardening-security.md`
- **C05 modules:** `C-Design/C05-modules/lms_payment.md`, `C-Design/C05-modules/lms_ai.md`, `C-Design/C05-modules/lms_zoom.md`
- **Facts base:** `config-inventory.md` §6 Integration / 3rd-Party Configuration, §8 Key Risks R1–R5
- **Related issues:** [ISSUE-002](ISSUE-002-no-payment-webhook-endpoints.md) (payment gateway gaps share the same secrets-in-config root)
- **K04:** Reusable rule — never store API keys or private keys in Drupal config export; always use Key module + env-var backend.
