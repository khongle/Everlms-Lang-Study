# Service Contract - app-drupal → ext-zoom

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (Zoom REST API, server-to-server OAuth) |
| Direction | outbound meeting create/schedule; ⚠️ inbound webhook (attendance/recording) partial ([ISSUE-0009](../../E-Quality/E02-issues/open/ISSUE-0009-zoom-depth-partial.md)) |
| Auth | Zoom server-to-server OAuth client ID/secret (⚠️ in config, R-001 → Key/env) |
| Data | session schedule → Zoom meeting; join link stored on `lms_zoom` session entity |
| Idempotency | one Zoom meeting per `lms_zoom` session (create-once; no re-create on retry) |
| Failure | API error → scheduling fails, instructor notified; join link left blank |
| Audit | log meeting create/update; no PII beyond join URL |

> Attendance/recording capture depends on the Zoom Report/Recording APIs, not yet wired ([ISSUE-0009](../../E-Quality/E02-issues/open/ISSUE-0009-zoom-depth-partial.md)).
