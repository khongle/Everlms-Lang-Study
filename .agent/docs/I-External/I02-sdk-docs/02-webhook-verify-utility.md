# SDK: Webhook Signature Verification Utility

**Status: BLUEPRINT** — target for v0.2 SDK. Current server-side implementation blocked by FIND-001 (Stripe stub).

This document specifies the **shape** of the signature-verification helper that the SDK must ship. It serves three audiences:

1. Server-side integrators writing their own webhook receiver for EverLMS events (when EverLMS itself emits webhooks — future).
2. EverLMS server code (internal reference for parity across SDK and Drupal handler).
3. PayPal/Stripe webhook receivers that want a uniform verify API.

---

## Why a Utility

Three webhook surfaces exist in the EverLMS ecosystem:

| Surface | Direction | Signature Scheme |
|---------|-----------|------------------|
| Stripe → EverLMS `/payment/stripe/webhook` | inbound | HMAC-SHA256 over `timestamp.body` with shared secret |
| PayPal → EverLMS `/payment/paypal/ipn` | inbound | PayPal signature header (separate verify call) |
| EverLMS → 3rd-party webhook (future) | outbound | HMAC-SHA256 to match Stripe's convention |

A shared utility keeps the verification algorithm identical across languages and reduces per-integrator mistakes (timing attacks, missing tolerance window, etc.).

---

## Contract

```typescript
interface VerifyOptions {
  payload: string | Buffer;         // raw body, un-parsed
  signatureHeader: string;          // raw header value
  secret: string;                   // webhook signing secret
  toleranceSec?: number;            // default 300; max 3600
  clock?: () => number;             // injectable (for tests), returns seconds since epoch
}

interface VerifyResult {
  valid: boolean;
  reason?:
    | 'signature_mismatch'
    | 'timestamp_outside_tolerance'
    | 'malformed_header'
    | 'empty_secret'
    | 'empty_payload';
  parsedTimestamp?: number;
}

// Provider-specific wrappers:
export function verifyStripeSignature(opts: VerifyOptions): VerifyResult;
export function verifyEverlmsOutboundSignature(opts: VerifyOptions): VerifyResult;
// PayPal differs — requires server round-trip, not pure HMAC:
export async function verifyPaypalNotification(opts: {
  body: Record<string, string>;
  environment: 'sandbox' | 'live';
}): Promise<VerifyResult>;
```

---

## Verification Algorithm (Stripe-compatible)

```
1. Parse `Stripe-Signature` header. Format:
   t=<timestamp>,v1=<sig1>[,v1=<sig2>...]
2. If parse fails → { valid: false, reason: 'malformed_header' }.
3. If `|now - t|` > toleranceSec → { valid: false, reason: 'timestamp_outside_tolerance' }.
4. Compute expected = HMAC-SHA256(secret, `${t}.${raw_body}`).
5. For each v1 signature in header:
     if timingSafeEqual(expected, sig) → { valid: true, parsedTimestamp: t }.
6. Else → { valid: false, reason: 'signature_mismatch' }.
```

Rules:
- **Timing-safe compare mandatory.** Plain `===` leaks info.
- **Raw body required** — NOT parsed JSON (any reserialization changes bytes).
- **Reject empty secret** upfront; empty secret + empty signature must NEVER return valid.

---

## Anti-patterns to Prevent

The utility exists specifically to stop these mistakes that repeatedly show up in webhook code review:

1. **Parsing JSON before verification.** `JSON.parse(body)` then `JSON.stringify(obj)` produces bytes that don't match the signature.
2. **String equality on signatures.** `a === b` is timing-unsafe — Node's `crypto.timingSafeEqual` (or equivalent) is the minimum bar.
3. **Missing timestamp tolerance.** Without a window check, a stolen signature is replayable forever.
4. **Using the wrong secret** (test vs live). Secrets must come from env, never hard-coded, and the utility refuses empty string.
5. **Returning generic "invalid"** — the `reason` enum in the result lets the receiver log-and-metric by reason (e.g., distinguish replay attack attempt from honest clock drift).

---

## Reference Implementation Sketch (Node 18+)

```typescript
import { createHmac, timingSafeEqual } from 'node:crypto';

export function verifyStripeSignature(opts: VerifyOptions): VerifyResult {
  const { payload, signatureHeader, secret } = opts;
  const tol = opts.toleranceSec ?? 300;
  const now = (opts.clock ?? (() => Math.floor(Date.now() / 1000)))();

  if (!secret) return { valid: false, reason: 'empty_secret' };
  if (!payload) return { valid: false, reason: 'empty_payload' };

  const parts = Object.fromEntries(
    signatureHeader.split(',').map((kv) => kv.split('=')),
  );
  const t = parseInt(parts.t, 10);
  const sigs = signatureHeader
    .split(',')
    .filter((kv) => kv.startsWith('v1='))
    .map((kv) => kv.slice(3));

  if (!t || sigs.length === 0) return { valid: false, reason: 'malformed_header' };
  if (Math.abs(now - t) > tol) return { valid: false, reason: 'timestamp_outside_tolerance', parsedTimestamp: t };

  const signed = `${t}.${payload}`;
  const expected = createHmac('sha256', secret).update(signed).digest('hex');
  const expectedBuf = Buffer.from(expected, 'hex');

  for (const sig of sigs) {
    const sigBuf = Buffer.from(sig, 'hex');
    if (sigBuf.length === expectedBuf.length && timingSafeEqual(sigBuf, expectedBuf)) {
      return { valid: true, parsedTimestamp: t };
    }
  }
  return { valid: false, reason: 'signature_mismatch', parsedTimestamp: t };
}
```

Python parity (planned):

```python
# everlms_sdk.webhooks
def verify_stripe_signature(
    payload: bytes,
    signature_header: str,
    secret: str,
    tolerance_sec: int = 300,
    now: int | None = None,
) -> VerifyResult: ...
```

Drupal server-side parity: must import the `stripe/stripe-php` library's `Webhook::constructEvent()` OR re-implement the same algorithm, and the result must be structurally identical so SDK and server tests share fixtures.

---

## Test Fixtures (shared with server)

The SDK ships fixture JSON under `fixtures/webhooks/` with labeled cases:

| Fixture | Expected result |
|---------|-----------------|
| `stripe-valid.json` | `{ valid: true }` |
| `stripe-tampered-body.json` | `{ valid: false, reason: 'signature_mismatch' }` |
| `stripe-old-timestamp.json` | `{ valid: false, reason: 'timestamp_outside_tolerance' }` |
| `stripe-malformed-header.json` | `{ valid: false, reason: 'malformed_header' }` |
| `stripe-empty-secret.json` | `{ valid: false, reason: 'empty_secret' }` |

The Drupal server-side handler (U-060) must pass the same fixtures. Parity is the bar.

---

## Prerequisite Before Shipping

- [ ] U-060 Stripe signature verify merged (server side).
- [ ] TASK-003 PayPal handler re-implemented with clean verify.
- [ ] Fixture set agreed with server team.
- [ ] SDK v1 baseline shipped (file 01).

---

## Related

- `I-External/I03-webhook-contracts/01-stripe-webhook.md` — contract definition.
- `I-External/I03-webhook-contracts/02-paypal-webhook.md`
- `G-Governance/G02-risk-register.md` §FIND-001, §RISK-004.
- `D-Development/D05-tasks/backlog/TASK-002-webhook-signature-verify.md` (if filed).

---

*End of I02 webhook-verify-utility.md*
