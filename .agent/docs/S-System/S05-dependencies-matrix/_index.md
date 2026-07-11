# S05-dependencies-matrix - Dependencies Matrix

Full caller → callee matrix with the **blast radius** of each edge — what breaks if the callee is down. `From`/`To` use the [S01 service catalog](../S01-service-catalog/_index.md) service IDs. Every edge resolves to its [S02](../S02-contracts/_index.md) contract file (edge closure — enforced mechanically by Gate 7.S).

| From | To | Sync/Async | Reason | Blast radius | S02 contract |
| ---- | -- | ---------- | ------ | ------------- | ------------ |
| app-drupal | db-mariadb | Sync | persistence for all entities (all `lms_*` modules) | db-mariadb down → all reads/writes fail, SEV1 | [S02](../S02-contracts/app-drupal-to-db-mariadb.md) |
| app-drupal | ext-stripe | Sync | checkout capture (`lms_payment`/`lms_stripe`) | checkout fails → order stays pending (revenue impact) | [S02](../S02-contracts/app-drupal-to-stripe.md) |
| app-drupal | ext-paypal | Sync | checkout capture (`lms_payment`/`lms_paypal`, duplicate module — [ISSUE-0006](../../E-Quality/E02-issues/open/ISSUE-0006-duplicate-paypal-module.md)) | gateway option unavailable; buyer offered other gateway | [S02](../S02-contracts/app-drupal-to-paypal.md) |
| app-drupal | ext-zalopay | Sync | checkout capture (`lms_payment`, stub — [ISSUE-0010](../../E-Quality/E02-issues/open/ISSUE-0010-paypal-zalopay-stub.md)) | gateway option unavailable; buyer offered other gateway | [S02](../S02-contracts/app-drupal-to-zalopay.md) |
| app-drupal | ext-zoom | Async | live session scheduling + join links (`lms_zoom`) | scheduling fails; instructor notified; no live-session data | [S02](../S02-contracts/app-drupal-to-zoom.md) |
| app-drupal | ext-openai | Sync | AI content generation, primary provider (`lms_ai`) | generation unavailable → provider routing falls back | [S02](../S02-contracts/app-drupal-to-openai.md) |
| app-drupal | ext-gemini | Sync | AI content generation, secondary provider (`lms_ai`) | provider unavailable → next fallback in routing chain | [S02](../S02-contracts/app-drupal-to-gemini.md) |
| app-drupal | ext-pollinations | Sync | AI content generation, tertiary provider (`lms_ai`) | provider unavailable → generation fails if all 3 down | [S02](../S02-contracts/app-drupal-to-pollinations.md) |
| app-drupal | ext-smtp | Async | email notification delivery (`lms_notification`, partial — [ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md)) | email not delivered; no cron retry yet | [S02](../S02-contracts/app-drupal-to-smtp.md) |
| app-drupal | ext-fcm-apns | Async | push notification delivery (`lms_mobile_api`/`lms_notification`, partial — [ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md)) | push not delivered; no device-token endpoint yet | [S02](../S02-contracts/app-drupal-to-fcm-apns.md) |
| app-flutter | app-drupal | Sync | mobile client consumes `/api/v1` (`lms_mobile_api`) | mobile app broken/offline-only; no rate limiting yet (R-007) | [S02](../S02-contracts/app-flutter-to-app-drupal.md) |

> All third-party creds SENSITIVE (R-001 — see [ISSUE-0001](../../E-Quality/E02-issues/open/ISSUE-0001-secrets-in-config-yaml.md)). External outages should degrade gracefully (INT contracts, [C06](../../C-Design/C06-apis/_index.md)) — verify via chaos experiments ([E11](../../E-Quality/E11-chaos-results/README.md)). mPDF (certificate rendering, `lms_certificate`) is an in-process PHP library, not a network service — it has no S01/S02 entry; see [D07 code map](../../D-Development/D07-code-map/README.md) for that dependency.
