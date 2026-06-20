# Service Contract - app-flutter → app-drupal

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS REST `/api/v1` (JSON) |
| Auth | OTP → bearer token; `Authorization: Bearer` |
| Envelope | `{data, error, meta}` |
| Endpoints | ~22 (auth, catalog, learning, quiz) — [I01](../../I-External/I01-public-api-spec/README.md) |
| Errors | 401/403 unauthenticated; 4xx validation; (target) 429 rate limit |
| Push | FCM/APNs out-of-band (INT-009) |
| Versioning | `/api/v1`; breaking → `/api/v2` ([I05](../../I-External/I05-api-changelog/_index.md)) |
| Risks | no rate limiting (R-007) |
