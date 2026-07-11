---
id: WF-010-001
workflow: WF-010
title: AI provider API keys stored in plaintext in git-tracked config (R-001)
severity: critical
category: security
status: open
roles: admin
source: round-2 real-session QA
found: 2026-07-11
needs: owner action (key rotation)
risk: R-001
---

# WF-010-001 - OpenAI & Gemini API keys committed in plaintext config

## Problem
`lms_ai.settings` stores live provider secrets as plaintext:
- `openai.api_key` (a real `sk-proj-…` OpenAI key)
- `gemini.api_key` (a real `AIza…` Gemini key)

These are exported to **`config/sync/lms_ai.settings.yml`, which is tracked in
git** — so the keys are exposed in the repository and its history. The providers
read them straight from config (`OpenAiProvider::getApiKey()` /
`GeminiProvider::getApiKey()` → `configFactory->get('lms_ai.settings')`). This is
the documented P0 risk **R-001 (secrets-in-config)**, now concretely confirmed.

## Impact
Anyone with repo access (or a leaked clone / CI log) obtains working API keys →
billable usage, data exfiltration via the account, quota abuse.

## Expected
Secrets must not live in config/sync or git. Use the enabled **`key` module**
(8.x-1.22) with keys backed by environment variables or a file outside the repo,
or read from `settings.php` / env directly.

## Remediation plan
1. **Rotate both keys now** (OpenAI + Gemini) — they are compromised (in git
   history). *(Owner action — cannot be automated here.)*
2. Create `key` entities (e.g. `lms_ai_openai`, `lms_ai_gemini`) using the
   "Environment" or "File" key provider so the secret is not in config sync.
3. Change `OpenAiProvider::getApiKey()` / `GeminiProvider::getApiKey()` to read
   via `key.repository` (`\Drupal::service('key.repository')->getKey($id)->getKeyValue()`),
   falling back to an env var.
4. Remove `openai.api_key` / `gemini.api_key` from `lms_ai.settings` and
   `config/sync/lms_ai.settings.yml`; purge from git history (BFG/filter-repo).
5. Update `AiSettingsForm` to reference key IDs instead of raw values.

## Notes
Not auto-fixed: step 1 (rotation) is owner-only and mandatory, and a code-only
migration without rotated keys would break the working AI builder while leaving
the exposed keys valid. Track alongside R-001 in the risk register.

Round-2 also confirmed the AI builder UI itself is functional and human-in-the-loop:
`/manage/courses/ai-builder` (`create lms_course`; instructor + content_editor)
collects Topic / Audience / Tone / Provider and "Generate draft" before any
entity is saved (draft preview → accept). Providers configured: OpenAI (default),
Gemini. Screens: `010-ai-assisted-content-generation/`.
