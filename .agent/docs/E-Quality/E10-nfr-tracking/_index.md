# E10-nfr-tracking - NFR Tracking

**Last updated:** 2026-06-21

NFR names align **bidirectionally** with [B02 technical requirements](../../B-Requirements/B02-technical-requirements/_index.md) (same names, both directions).

| NFR | Target (B02) | Current status | Evidence | Gap |
| --- | ------------ | -------------- | -------- | --- |
| Performance | Catalog/course < 1s server time on seeded data; indexed queries | 🟡 unmeasured | [E12](../E12-performance-results/README.md) | add perf baseline |
| Security | RBAC per route; no secrets in code; server-side payment/AI; CSRF; bearer auth | 🔴 partial | secrets in config (R-001); no webhooks (R-002) | R-001/R-002 |
| Reliability | Idempotent cert issuance; order↔gateway reconcilable; cron retries | 🟡 partial | cert idempotent ✅; no webhook reconcile 🔴 | R-002 |
| Accessibility | WCAG 2.1 AA (semantic Twig, alt text, keyboard nav) | 🟡 unverified | [E03](../E03-test-definitions/) a11y def | run axe/manual |
| Maintainability | `lms_*` prefix; phpcs Drupal,DrupalPractice; plugin extensibility; code-map | ✅ good | [D07](../../D-Development/D07-code-map/README.md), C01 | dedupe lms_paypal (R-004) |
| Editorial usability | Every business-visible value maps to a named editable source | ✅ enforced | C11 visible-content inventories; C02 matrix | edit-smoke tests (R-006) |

> Each row mirrors a B02 NFR row 1:1. Status uses ✅/🟡/🔴. Release-critical NFR gaps (Security/Reliability) trace to [G02 risks](../../G-Governance/G02-risk-register/_index.md).
