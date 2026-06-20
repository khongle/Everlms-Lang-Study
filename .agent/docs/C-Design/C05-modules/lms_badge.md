---
module: lms_badge
service: badge
route-groups: []
status: IN_PROGRESS
documentation-mode: as-built
---

# C05 — Module: LMS Badge

**Directory:** `web/modules/custom/lms_badge/`
**Responsibility:** Defines the Badge and UserBadge entities for gamification; award logic, routes, and services are not yet implemented (near-stub state).

> **Implementation status: NEAR-STUB.** Only entity definitions exist. No services, no routes, no `.module` hooks, no install hooks, and no badge award logic of any kind. The module cannot award badges automatically or manually at runtime.

---

## Route Bindings

No `lms_badge.routing.yml` exists. Entity canonical/collection links (via `AdminHtmlRouteProvider` on `lms_badge`) provide:

| Route | Path | Access |
|---|---|---|
| `entity.lms_badge.canonical` | `/manage/badges/{lms_badge}` | `administer lms_course` |
| `entity.lms_badge.add_form` | `/manage/badges/add` | `administer lms_course` |
| `entity.lms_badge.edit_form` | `/manage/badges/{lms_badge}/edit` | `administer lms_course` |
| `entity.lms_badge.collection` | `/manage/badges` | `administer lms_course` |

`lms_user_badge` has no route_provider — no routes generated.

---

## Public Interface

None. No services.yml exists. No exported services or interfaces.

---

## Internal Structure

```
web/modules/custom/lms_badge/
+-- lms_badge.info.yml        # deps: lms_base; lifecycle: stable
+-- src/
    +-- Entity/
        +-- Badge.php          # lms_badge content entity
        +-- UserBadge.php      # lms_user_badge content entity (EntityOwnerTrait)
```

**Missing files (not implemented):**
- `lms_badge.routing.yml` — no custom routes
- `lms_badge.services.yml` — no services
- `lms_badge.permissions.yml` — no custom permissions
- `lms_badge.module` — no hooks
- `lms_badge.install` — no schema updates
- No controllers, forms, event subscribers, or queue workers

---

## Entities Owned

### `lms_badge` (ContentEntity)

Base table: `lms_badge`

| Field | Type | Notes |
|---|---|---|
| id | serial | entity key |
| uuid | uuid | entity key |
| name | string (255) | required; label key |
| field_description | text_long | |
| field_icon | image | dir: `badges/icons` |
| field_type | entity_reference (taxonomy_term) | bundle: `badge_type` vocabulary |
| field_xp_reward | integer | default=0 |
| field_criteria | string (1024) | JSON format: `{"type":"courses_completed","count":5}` — **documented but never evaluated** |

Handlers: `EntityViewBuilder`, `EntityViewsData`, `ContentEntityForm` (core default), `ContentEntityDeleteForm`, `AdminHtmlRouteProvider`, `EntityAccessControlHandler`.

### `lms_user_badge` (ContentEntity — EntityOwnerInterface)

Base table: `lms_user_badge`

| Field | Type | Notes |
|---|---|---|
| id | serial | entity key |
| uuid | uuid | entity key |
| uid | entity_reference (user) | owner (EntityOwnerTrait) |
| field_badge | entity_reference (lms_badge) | required |
| field_awarded_at | created | timestamp |

Handlers: `EntityViewsData`, `EntityAccessControlHandler` only — no form handler, no route_provider.

---

## Dependencies

| Dependency | Type | Why |
|---|---|---|
| `lms_base` | Internal | Base entity traits |

---

## Known Issues / Debt

| Ref | Severity | Description |
|---|---|---|
| STUB | Critical | No badge award logic exists anywhere — criteria in `field_criteria` are never evaluated. No service, cron job, or event subscriber awards badges. |
| STUB | High | No user-facing routes — learners cannot view their badges. No leaderboard implementation despite the module description mentioning it. |
| STUB | High | `lms_user_badge` has no `awarded_by` or `reason` fields — cannot track who awarded a badge or why. |
| DEBT | Medium | `field_criteria` uses a JSON string in a plain `string` field; no schema validation; the format `{"type":"courses_completed","count":5}` is documented only in code comments. |
| PLANNED | — | Full badge award engine (criteria evaluator, event-driven awarding, leaderboard) is target-state / planned — not yet materialized. |
