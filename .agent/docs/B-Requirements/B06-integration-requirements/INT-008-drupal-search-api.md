# INT-008 — Drupal Search API

**ID:** INT-008
**Integration:** Drupal Search API (+ Solr / Database backend)
**Owner Module:** `drupal/search_api` (contrib) + `lms_course` (indexing config)
**Direction:** Internal (Drupal to search backend)
**Protocol:** Drupal Search API abstraction layer
**Status:** Config ready

---

## Scope

Full-text search và faceted filter cho course catalog. Courses, categories, instructors indexed và searchable với filters (category, level, price, rating, language).

## Connection

| Property | Value |
|----------|-------|
| Backend | Database (dev) hoặc Apache Solr (production) |
| API | Drupal Search API abstraction (no external HTTP) |
| Index | `course_index` — entities: course nodes + taxonomy |

## Data Indexed

| Field | Type | Filterable | Fulltext |
|-------|------|-----------|---------|
| title | string | Yes | Yes |
| description | text | No | Yes |
| category | entity ref | Yes (facet) | No |
| level | taxonomy | Yes (facet) | No |
| price_type | list | Yes (facet) | No |
| rating_average | decimal | Yes (range) | No |
| language | taxonomy | Yes (facet) | No |
| instructor | entity ref | Yes | Yes |
| status | boolean | Yes (filter: published only) | No |

## Indexing

| Trigger | Behavior |
|---------|----------|
| Course published | Add to index (real-time or cron) |
| Course updated | Update index entry |
| Course unpublished | Remove from index |
| Cron | Full reindex available via admin UI |

## Rate Limits & Quotas

- Database backend: không có external limit; performance limited by DB.
- Solr: per-server config; horizontal scale.

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| Index out of sync | Stale results ≤24h acceptable; admin manual reindex |
| Solr server down | Fallback to database search (slower); alert admin |
| New course not in search | Cron reindex catches within configured interval |

## Related Docs

- [AC-H05](../B05-acceptance-criteria/AC-10-integrations.md) — search indexing AC
- [AC-A05](../B05-acceptance-criteria/AC-01-course-management.md) — course search & filter AC
- B03: [F-001 Course Management](../B03-features/01-course-management.md) — search feature requirements
