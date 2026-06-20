# E09-impact-analysis - Impact Analysis

Change-impact assessments (run before risky changes). Template: change → affected modules/entities/pages → tests → rollback.

| Change | Affected | Tests to run | Rollback |
| ------ | -------- | ------------ | -------- |
| Move secrets to Key (E06S02T02) | lms_payment/lms_ai/lms_zoom config + gateway/provider load | payment capture, AI gen, zoom schedule | revert config + key entities |
| Add payment webhooks (E06S02T01) | lms_payment/lms_stripe routing + order state | order state-machine, signature, idempotency | disable webhook route |
| Remove lms_paypal (E06S04T01) | lms_paypal uninstall + plugin refs | PayPal checkout | re-enable module |

> Add an entry per significant change; link the D04 task.
