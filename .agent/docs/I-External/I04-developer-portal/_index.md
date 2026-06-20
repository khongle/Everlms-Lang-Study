# I04-developer-portal - Developer Portal

No public developer portal today (the API serves the first-party Flutter app). 

## If opened to third parties
- Publish the [I01 spec](../I01-public-api-spec/README.md) (consider OpenAPI generation).
- Provide app registration + token issuance, rate-limit/quota docs ([I06](../I06-rate-limits-quotas/_index.md)), and a changelog ([I05](../I05-api-changelog/_index.md)).
- Gate behind the security fixes (R-001 secrets, R-007 rate limiting) first.
