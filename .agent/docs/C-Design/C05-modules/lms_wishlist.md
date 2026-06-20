---
module: lms_wishlist
service: Commerce
route-groups: [App, API]
status: DONE
---

# C05 — Module: LMS Wishlist

**Directory:** `web/modules/custom/lms_wishlist/`
**Responsibility:** Provides course wishlist functionality — a toggle API to add/remove courses, a status-check API, and a user-facing `/my-wishlist` page, all with cache invalidation via custom cache tags.

**Documentation mode:** as-built

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|-----------|-------|
| API (legacy `/api/lms`) | `/api/lms/wishlist` | `WishlistController` | `POST /api/lms/wishlist/toggle`, `GET /api/lms/wishlist/status` |
| App | `/my-wishlist` | `WishlistController` | `GET /my-wishlist` |

> These routes live under the legacy `/api/lms/` prefix (not `/api/v1/`). Authentication is `_user_is_logged_in: TRUE` — session-based, not token-based.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `Wishlist` (`lms_wishlist`) | Content Entity | One record per (user, course) pair; `created` = "Added At" timestamp |
| Cache tag `lms_wishlist:{uid}` | Convention | Invalidated by `toggle()`; consumed by `myWishlist()` for per-user cache |

---

## Internal Structure

```
web/modules/custom/lms_wishlist/
+-- lms_wishlist.info.yml           # deps: lms_base, lms_course
+-- lms_wishlist.routing.yml        # 3 routes: toggle (POST), status (GET), my_wishlist (GET)
+-- lms_wishlist.module             # hook_theme: lms_my_wishlist (template: lms-my-wishlist; var: cards)
+-- src/
|   +-- Controller/
|   |   +-- WishlistController.php  # toggle(), getStatus(), myWishlist(), buildCourseCard()
|   +-- Entity/
|       +-- Wishlist.php            # lms_wishlist entity class
+-- templates/
    +-- lms-my-wishlist.html.twig
```

### Key Entity: `lms_wishlist` (base table: `lms_wishlist`)

| Field | Type | Notes |
|-------|------|-------|
| id, uuid | auto | entity keys |
| uid | entity_reference (user) | owner; `EntityOwnerTrait` |
| field_course | entity_reference | target: `lms_course`; required |
| created | created | label = "Added At" |

> No list_builder, no form handlers, no route_provider, no admin UI — all operations are controller-driven.

### Controller: `WishlistController`

| Method | HTTP | Path | Behavior |
|--------|------|------|----------|
| `toggle(Request): JsonResponse` | POST | `/api/lms/wishlist/toggle` | Reads `course_id` from JSON body; queries `lms_wishlist` for `uid + course_id`; if found → deletes (returns `{status: removed}`); else creates (returns `{status: added}`); invalidates `lms_wishlist:{uid}` cache tag |
| `getStatus(Request): JsonResponse` | GET | `/api/lms/wishlist/status` | Reads comma-separated `course_ids` query param; returns `{id: bool}` map + total wishlist count for current user |
| `myWishlist(): array` | GET | `/my-wishlist` | Loads user wishlist entries sorted by `created DESC`; calls `buildCourseCard()` for each; returns `lms_my_wishlist` theme; cache: user context + `lms_wishlist:{uid}` tag |
| `buildCourseCard($course): array` | private | — | Builds rich card: id, name, short_description, price, rating, category, level, duration (via `lms_base.helper`), bestseller, featured, thumbnail, URL, instructor info (name/avatar/url), `is_enrolled` check via `lms_user_course` |

### Cache Strategy

| Surface | Cache keys | Invalidated by |
|---------|-----------|----------------|
| `/my-wishlist` page | user context + `lms_wishlist:{uid}` tag | `WishlistController::toggle()` |
| API endpoints (`/toggle`, `/status`) | No caching | — |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | `lms_base.helper` service (duration formatting, etc.) |
| `lms_course` | Internal | `lms_course` entity reference in `Wishlist`; course card data |
| `lms_user_course` | Internal (runtime) | `buildCourseCard()` checks enrollment status via `lms_user_course` entity query |

**No circular dependencies.**

---

## Known Issues / Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-TOGGLE-RESPONSE | Low | `toggle()` returns `{status: added/removed}` but does not include updated wishlist count — client must call `/status` for updated totals |
| DEBT-STATUS-PERF | Low | `/status` queries per-course; expensive if called for many course IDs per page |
| DEBT-NO-BULK-REMOVE | Low | No bulk-remove endpoint; users must toggle each course individually |

---

## Cross-links

- Entity: [Wishlist](../C04-entities/01-content-entities.md)
- Course entity: [lms_course](lms_course.md)
