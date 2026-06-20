# Runbook - Credential Leak Response

⚠️ Heightened relevance: secrets currently live in `config/sync` (R-001/BLK-001) and signing keys in `app/` (SENSITIVE).

## Steps
1. **Rotate immediately** the exposed credential at the provider (Stripe/PayPal/ZaloPay/OpenAI/Gemini/Zoom/SMTP/FCM/APNs).
2. Revoke the old key; update the new value via Key/env (not config) — accelerate [E06S02T02](../../D-Development/D04-tasks/todo/E06S02T02-move-secrets-to-key.md).
3. Purge the secret from git history (filter-repo/BFG); force-push; invalidate caches.
4. Audit provider logs for misuse during exposure window.
5. Record an incident ([H01](../H01-incident-log/README.md)); add/raise a risk in [G02](../../G-Governance/G02-risk-register/_index.md).

## Prevention
Complete the Secrets standard module; add a secret-scan pre-commit/CI hook.
