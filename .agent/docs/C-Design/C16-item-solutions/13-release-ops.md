# C16 Sheet - 13 release-ops

Quality/release/operations solutions. Source: E/F/H layers.

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Build | config import | `drush cim` from config/sync | core | deploy config | A01 | cim clean |
| Data | DB import | `ddev import-db` | core | seed state | A01 | import |
| Test | PHPUnit baseline | kernel/functional suites | each module | ⚠️ absent today (BLK-004) | E01 | coverage |
| Lint | phpcs | Drupal/DrupalPractice | each | CI lint | A01 | lint |
| Secrets | gateway/AI keys | ⚠️ move to Key/env (R-001) | lms_payment/lms_ai/lms_zoom | Secrets std module (missing) | C01/G02 | secret-scan |
| Webhooks | payment reconcile | ⚠️ add webhook + signature (R-002) | lms_payment/lms_stripe | endpoint + verify | C06 INT-001 | signature |
| Cron | notifications | `drush cron` | lms_notification | queue dispatch | C05 | dispatch |
| Release | go/no-go | Z-review verdict | — | GO-WITH-FIXES | Z-Reviews | gate |
