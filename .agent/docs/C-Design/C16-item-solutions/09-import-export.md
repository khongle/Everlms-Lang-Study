# C16 Sheet - 09 import-export

Giải pháp cho importer + export-form. Nguồn: lms_content_tools, lms_analytics.

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Import | demo content | Batch API + Drush | lms_content_tools | JSON → entities (`lms:import`) | SRC-10 | import count |
| Seed | seed input | Form + service | lms_content_tools | `lms:seed-input` | SRC-11 | seed |
| Clean | wipe generated | Drush + service | lms_content_tools | `lms:clean` | C05 | idempotent |
| Export | analytics CSV | controller stream | lms_analytics | `/admin/lms/reports/*/export` | C11 P049 | CSV shape |
| Generate | content gen JSON | scripts/generators | .agent/generated | per-domain JSON | SRC-10 | n/a |
| Maintenance | fix scripts | Drush php-script | scripts/ | category/cert/quiz fixers | A01 | run |
