---
id: INT-008
name: Drupal Search API
type: REST
direction: Inbound
status: PROPOSED
---

# INT-008 — Drupal Search API

**Owner:** `drupal/search_api` (contrib module — not yet configured)
**Documentation:** <https://www.drupal.org/project/search_api>

Full-text search and faceted filtering for the LMS course catalog. **This integration is planned but not configured.** The `search_api` contrib module is not present in config sync. The site uses Drupal core search (`search.*`) only, which indexes `node_field_data` (standard Drupal nodes). No LMS-specific course search index, Search API server, or Views display using Search API exists in the current 396-file config export.

## Implementation Status

| Item | Status |
|------|--------|
| `drupal/search_api` contrib module | **not configured** — no `search_api.*` config in sync |
| `drupal/search_api_solr` or DB backend | **not configured** |
| LMS course search index | **not configured** |
| Search API-backed View for courses | **not configured** |
| Core `search.*` module | as-built (15 views, all core; no LMS entities indexed) |
| Active search pages | `node_search` (`/search/node`), `user_search`, `help_search` — core node search only |

**Evidence:** Config inventory shows 15 views, all using `node_field_data`, `users_field_data`, `comment_field_data`, or `watchdog` as base table. No view references `search_api_index` or any `lms_*` entity as a searchable base. The config export contains no `search_api.server.*`, `search_api.index.*`, or `search_api.saved_search.*` files.

## Planned Scope

When implemented, Search API should provide:

| Feature | Description |
|---------|-------------|
| Full-text course search | Query against course title, description, category, instructor |
| Faceted filtering | Category, level, price type, language, rating range |
| Real-time index updates | On `lms_course` publish/update/unpublish |
| Autocomplete | Suggest course titles as user types |
| Mobile API search | `/api/v1/courses/search` backed by Search API results |

## Connection (planned)

| Property | Value |
|----------|-------|
| Backend (dev) | `search_api_db` — Database backend (no external dependency) |
| Backend (production) | `search_api_solr` — Apache Solr (recommended for scale) |
| Protocol | Drupal Search API abstraction — no external HTTP for DB backend; HTTP to Solr for Solr backend |
| Authentication | N/A for DB backend; Solr basic auth for Solr backend |
| Index name | `course_index` (proposed) |

## Data to Be Indexed (planned)

| Field | Type | Filterable | Fulltext |
|-------|------|-----------|---------|
| `name` (course title) | string | Yes | Yes |
| `field_description` | text_long | No | Yes |
| `field_category` (taxonomy ref) | entity reference | Yes — facet | No |
| `field_level` (taxonomy ref) | entity reference | Yes — facet | No |
| `field_price_type` | list_string | Yes — facet | No |
| `field_language` (taxonomy ref) | entity reference | Yes — facet | No |
| `field_instructor` (user ref) | entity reference | Yes | Yes |
| `status` | boolean | Yes — published only | No |

## SLA (planned)

| Metric | Target | Measurement |
|--------|--------|-------------|
| Latency (p95) — DB backend | < 200ms | DB query time |
| Latency (p95) — Solr backend | < 50ms | Solr response time |
| Availability | 99.9% | Health check |
| Index freshness | ≤ 15min (cron-based) or real-time (entity hooks) | Index queue depth |
| Error Rate | < 1% | Drupal watchdog |

## Request/Response Contract (planned)

Search API operates through Drupal's abstraction layer — callers use `\Drupal\search_api\Query\QueryInterface`, not raw HTTP. For reference, a Views-backed search renders as:

**Inbound request (HTTP GET from browser or mobile):**

```json
{
  "keys": "string — search terms",
  "filter[category]": "string — taxonomy term ID",
  "filter[level]": "string — taxonomy term ID",
  "filter[price_type]": "string — free | paid",
  "page": "integer — page number",
  "limit": "integer — results per page"
}
```

**Response (planned JSON for mobile `/api/v1/courses/search`):**

```json
{
  "total": "integer — total matching results",
  "items": [
    {
      "id": "integer — course ID",
      "title": "string",
      "category": "string",
      "level": "string",
      "price_type": "string",
      "thumbnail": "string — URL"
    }
  ]
}
```

**Response (error):**

```json
{
  "error": { "code": "string", "message": "string" }
}
```

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | N/A — DB backend has no remote failure mode |
| Backoff | N/A |
| Timeout | N/A — DB backend |
| Retry On | Solr backend: HTTP 5xx, connection refused |

## Fallback Behavior (planned)

| Scenario | Behavior | Duration |
|----------|----------|----------|
| Solr server down | Fall back to DB backend search (slower) | Until Solr recovery |
| Index out of sync | Stale results; admin manual reindex via `/admin/config/search/search-api` | Until cron runs |
| Search API not configured | Current state — no course search available; users rely on Views filters | Until integration is built |

## Circuit Breaker

| Property | Value |
|----------|-------|
| Failure Threshold | N/A — not implemented; no circuit breaker planned at this layer |

## Data Flow (planned)

- **Frequency:** Real-time index updates via `hook_entity_insert/update/delete`; full reindex on demand or cron
- **Volume:** ~lms_course entity count (low hundreds in current DB); grows with content
- **Data Sensitivity:** Public (course catalog data only — no PII in index)
- **Encryption:** In-transit: TLS (Solr over HTTPS); at-rest: index data is non-sensitive

## Implementation Plan

1. Add `drupal/search_api` + `drupal/search_api_db` to `composer.json`
2. Enable modules via `drush en search_api search_api_db`
3. Create Search API server (DB backend for dev; Solr for production)
4. Create `course_index` index on `lms_course` entity, configure fields above
5. Create a Views display backed by `course_index` for the course catalog page
6. Wire `/api/v1/courses/search` mobile endpoint in `lms_mobile_api` to use Search API query
7. Add `search_api.*` config to `config/sync` and commit

## Feature Flag

- **Flag ID:** Not required
- **Rationale:** Integration is controlled by whether `search_api` module is enabled. When disabled, the site falls back to core node search. No runtime feature flag needed.

## Cross-links

- [INT-004 OpenAI](./INT-004-openai.md) — AI-powered semantic search (future consideration)
- [B03 Feature F-001 Course Management](../B03-features/01-course-management.md) — course search and filter requirements
