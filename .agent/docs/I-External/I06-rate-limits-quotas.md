# I06 — Rate Limits & Quotas

**Status: PROPOSED** — Rate limiting chưa được implement ở tầng application.
Các giá trị dưới đây là recommended targets khi API layer được build.

---

## Per-Tier Limits (PROPOSED)

| Tier | Who | Requests / minute | Requests / day | Notes |
|------|-----|-------------------|----------------|-------|
| Anonymous | Unauthenticated users | 60 rpm | 5,000/day | Per IP address |
| Authenticated | Logged-in users with API key | 600 rpm | 100,000/day | Per API key |
| Webhook | Stripe / PayPal inbound | Unlimited | Unlimited | Signature-validated only |

---

## Special Rules

### Certificate Verify Endpoint
- Anonymous: 60 rpm per IP (primary abuse vector for brute-forcing codes).
- Consider CAPTCHA after 10 failed lookups per IP per hour.

### Webhook Endpoints
- No rate limit applied to `/payment/webhook/stripe` and `/payment/webhook/paypal`.
- Protection is via **signature verification** (Stripe-Signature / PayPal-Transmission-Sig).
- **WARNING:** Signature verification is NOT YET IMPLEMENTED (see I03). Until implemented, these endpoints are effectively unprotected.

---

## Response Headers (PROPOSED)

When rate limiting is implemented, every API response should include:

| Header | Meaning |
|--------|---------|
| `X-RateLimit-Limit` | Current window ceiling (rpm) |
| `X-RateLimit-Remaining` | Requests remaining in current window |
| `X-RateLimit-Reset` | Unix timestamp when window resets |
| `Retry-After` | Seconds until retry advisable (on HTTP 429 only) |

---

## HTTP 429 Response Body

```json
{
  "error": {
    "code": "RATE_LIMITED",
    "message": "Too many requests. Please retry after the indicated time.",
    "retry_after": 42
  }
}
```

---

## Burst Policy (PROPOSED)

- Algorithm: token bucket.
- Burst = 2× rpm ceiling for up to 10 seconds.
- Example: Anonymous tier allows 60 rpm steady-state; burst up to 120 rps for ≤ 10 s.

---

## Abuse Policy (PROPOSED)

- Repeated 429s with no backoff → exponential slowdown applied.
- 3 critical abuse signals in 24 hours → temporary IP block (24 hours).
- Automated scraping / malicious use → permanent block + admin notification.

---

## Implementation Stack (Recommended)

- **Short-term:** Nginx `limit_req_zone` for IP-based limiting on `/api/` prefix.
- **Medium-term:** Drupal middleware or Symfony `EventSubscriber` for per-key limits.
- **Long-term:** Redis-backed sliding window counter via `lms_base` service.

---

## Changing Limits

- Limit reductions ≥ 30% require 30-day notice in I05 changelog.
- Enterprise/custom limits: negotiated per-contract, not documented here.
