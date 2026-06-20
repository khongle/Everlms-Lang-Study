# SDK Plan: `@everlms/sdk` (TypeScript / JavaScript)

**Status: PLANNED** — not yet implemented. Prerequisites: I01 public API v1, auth scheme finalized, rate-limit headers standardized.

---

## Purpose

A thin TypeScript SDK that wraps the stable read endpoints in `I01-public-api-spec/`:
- Certificate verification
- Catalog browse
- Course detail
- Enrollment status (authenticated)

It is **not** a full LMS client. No admin flows, no content mutations, no payment initiation. Mutations happen through the authenticated web UI.

---

## Scope (v1)

| Method | Endpoint | Auth | Stability |
|--------|----------|------|-----------|
| `verifyCertificate(code)` | `GET /verify/{code}` | none | public |
| `browseCatalog(params)` | `GET /api/v1/courses` | none | public |
| `getCourse(id)` | `GET /api/v1/courses/{id}` | none | public |
| `getEnrollmentStatus(courseId)` | `GET /api/v1/my-learning/{courseId}` | bearer | authenticated |
| `verifyWebhookSignature(payload, signature, secret)` | local helper | — | utility |

---

## Proposed Shape

```typescript
// Package: @everlms/sdk
// tsconfig: ES2022, module esnext, declaration true

export interface ClientConfig {
  baseUrl: string;              // e.g. "https://lms.example.com"
  apiToken?: string;            // bearer for auth endpoints
  timeout?: number;             // default 10000 ms
  fetch?: typeof fetch;         // injectable for testing
}

export class EverlmsClient {
  constructor(config: ClientConfig);

  // Public read
  verifyCertificate(code: string): Promise<CertificateVerification>;
  browseCatalog(params?: CatalogQuery): Promise<Paginated<CourseSummary>>;
  getCourse(id: string | number): Promise<CourseDetail>;

  // Authenticated read
  getEnrollmentStatus(courseId: string | number): Promise<EnrollmentStatus>;
}

// Webhook utility (server-only; don't use in browser)
export function verifyStripeWebhook(opts: {
  payload: string;               // raw body
  signatureHeader: string;       // Stripe-Signature
  secret: string;                // webhook signing secret
  tolerance?: number;            // seconds, default 300
}): { valid: boolean; event?: StripeEvent; error?: string };
```

---

## Type Definitions (derived from I01 schemas)

```typescript
export interface CertificateVerification {
  code: string;
  status: 'valid' | 'revoked' | 'not_found';
  issuedAt?: string;             // ISO-8601
  revokedAt?: string;
  revokeReason?: string;
  learnerName?: string;
  courseTitle?: string;
}

export interface CatalogQuery {
  category?: string;
  level?: 'beginner' | 'intermediate' | 'advanced';
  priceMin?: number;
  priceMax?: number;
  language?: string;
  sort?: 'newest' | 'popular' | 'rating' | 'price_asc' | 'price_desc';
  page?: number;
  perPage?: number;
}

export interface Paginated<T> {
  data: T[];
  meta: { page: number; perPage: number; totalItems: number; totalPages: number };
}

export interface CourseSummary {
  id: string;
  slug: string;
  title: string;
  summary: string;
  thumbnailUrl: string;
  price: number;
  currency: 'VND' | 'USD';
  level: 'beginner' | 'intermediate' | 'advanced';
  rating?: number;
  enrollmentCount?: number;
}

export interface EnrollmentStatus {
  courseId: string;
  status: 'not_enrolled' | 'active' | 'completed';
  progressPct: number;
  lastAccessedLessonId?: string;
  completedAt?: string;
  certificateCode?: string;
}
```

---

## Error Handling

```typescript
export class EverlmsApiError extends Error {
  constructor(
    public status: number,          // HTTP status
    public code: string,            // app-level code (e.g. "VALIDATION_FAILED")
    message: string,
    public details?: unknown,
  );
}

// Client throws EverlmsApiError on 4xx/5xx.
// Networking errors (timeout, offline) propagate the native error.
```

Status-code mapping follows I01. Client must not swallow errors silently — callers decide retry policy.

---

## Retry & Rate-Limit Handling

- Do **not** retry 4xx (caller fault). Retry `5xx` and `429` once with exponential backoff honoring `Retry-After` header (see `I06-rate-limits-quotas.md`).
- Idempotency: all v1 endpoints are GET — retries are safe.

---

## Versioning & Compatibility

- SDK version tracks API version: `@everlms/sdk@1.x` ↔ API v1.
- Breaking API changes (`I05-api-changelog.md`) → major bump.
- SDK may add methods in minor versions as new endpoints stabilize.

---

## Distribution

- npm: `npm install @everlms/sdk`
- CDN: `https://unpkg.com/@everlms/sdk@1/dist/browser.min.js` (UMD for script tags)
- Types: bundled (`.d.ts`); works in TS ≥ 5.0.

---

## Out of Scope for v1

- Admin / instructor mutations (no POST/PUT/DELETE in SDK).
- Streaming endpoints (SSE/WebSocket) — roadmap.
- Retry/queue persistence — caller responsibility.
- Caching strategy — caller chooses (SWR, react-query, raw fetch).

---

## Testing Plan (when built)

- Unit: 90%+ coverage on methods, type narrowing, error mapping.
- Integration: spin up Drupal in CI, exercise against real endpoints.
- Contract: Pact-style test pinning request/response shapes to I01 OpenAPI.

---

## Prerequisite Checklist

- [ ] I01 public API v1 tagged stable (not DRAFT).
- [ ] Auth decided (bearer API key vs OAuth2 — `G01-adrs/` ADR required).
- [ ] Rate-limit headers implemented on server (I06).
- [ ] CORS policy set for browser use cases.
- [ ] Error response schema stabilized across endpoints.
- [ ] Semantic versioning policy confirmed.

---

*End of SDK plan: @everlms/sdk (TypeScript)*
