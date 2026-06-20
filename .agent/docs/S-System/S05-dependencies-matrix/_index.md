# S05-dependencies-matrix - Dependencies Matrix

Service → external dependency, by owning module/integration.

| Consumer (module) | Depends on | Integration | Criticality | Failure mode |
| ----------------- | ---------- | ----------- | ----------- | ------------ |
| lms_payment/lms_stripe | Stripe | INT-001 | High (revenue) | checkout fails → order pending |
| lms_payment/lms_paypal | PayPal | INT-002 | Medium | gateway option down |
| lms_payment | ZaloPay | INT-003 | Low (stub) | gateway option down |
| lms_zoom | Zoom | INT-004 | Medium | live session scheduling fails |
| lms_ai | OpenAI | INT-005 | Medium | AI gen unavailable (degrade) |
| lms_ai | Gemini | INT-006 | Low (alt) | switch provider |
| lms_ai | Pollinations | INT-007 | Low | switch provider |
| lms_notification | SMTP | INT-008 | Medium (partial) | email not delivered |
| lms_mobile_api/lms_notification | FCM/APNs | INT-009 | Medium (partial) | push not delivered |
| all | MariaDB | — | Critical | app down |
| lms_certificate | mPDF (lib) | — | High | cert render fails |

> All third-party creds SENSITIVE (R-001). External outages should degrade gracefully (INT contracts) — verify via chaos experiments ([E11](../../E-Quality/E11-chaos-results/README.md)).
