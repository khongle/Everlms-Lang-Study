---
id: TEST-009
type: Kernel
priority: P1 — Should
ac: AC-050, AC-051
feature: F-004 (Order & Membership — Wishlist)
module: lms_wishlist
service: N/A (WishlistController; no custom service)
status: NOT_YET_AUTOMATED
---

# TEST-009 — Wishlist Toggle

> Verifies the wishlist toggle API (add/remove), status check (bulk by course IDs), cache tag invalidation, and the wishlist listing page. The `lms_wishlist` entity has no admin form or route_provider — all access is via `WishlistController`.

**Story:** [D03 lms_wishlist](../../D-Development/D03-stories/lms_wishlist.md)
**AC:** [AC-050 — Wishlist toggle add (B05 AC-04)](../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) · [AC-051 — Wishlist toggle remove (B05 AC-04)](../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md)
**C05 Module:** [lms_wishlist](../../C-Design/C05-modules/lms_wishlist.md)

---

## TC-009-A: Toggle add — creates a `lms_wishlist` entity

**Layer:** Kernel
**Given:**
- A published `lms_course` entity with `course_id = X`
- An authenticated student with no existing `lms_wishlist` entity for that course

**When:** POST `/api/lms/wishlist/toggle` with JSON body `{"course_id": X}` and Bearer token

**Then:**
- HTTP 200 JSON: `{"status":"added","wishlist_id":<id>}`
- A `lms_wishlist` entity is created in the `lms_wishlist` DB table with `uid=<student>`, `field_course_target_id=X`, `created=<now>`
- Cache tag `lms_wishlist:<uid>` is invalidated

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid `course_id`, no prior wishlist | `{"status":"added"}`, entity created |
| Non-existent `course_id` | HTTP 400 or graceful error (course lookup fails) |
| Anonymous user | HTTP 403 (route requires `_user_is_logged_in: TRUE`) |

---

## TC-009-B: Toggle remove — deletes the `lms_wishlist` entity

**Layer:** Kernel
**Given:**
- A `lms_wishlist` entity already exists for student + course

**When:** POST `/api/lms/wishlist/toggle` with same `{"course_id": X}`

**Then:**
- HTTP 200 JSON: `{"status":"removed"}`
- The `lms_wishlist` entity is deleted from the DB
- `SELECT COUNT(*) FROM lms_wishlist WHERE uid=<student> AND field_course_target_id=X` = 0
- Cache tag `lms_wishlist:<uid>` is invalidated

---

## TC-009-C: Toggle idempotency — alternating add/remove

**Layer:** Kernel
**Given:** Two sequential toggle calls for the same user + course

**When:**
1. POST toggle → response `{"status":"added"}`
2. POST toggle → response `{"status":"removed"}`
3. POST toggle → response `{"status":"added"}` again

**Then:** Database row count matches toggle state (0 when removed, 1 when added) at each step

---

## TC-009-D: Status check — bulk query by course IDs

**Layer:** Kernel
**Given:**
- Student has courses 1 and 3 wishlisted; course 2 is not wishlisted
- Total wishlist count = 2

**When:** GET `/api/lms/wishlist/status?course_ids=1,2,3`

**Then:**
- HTTP 200 JSON: `{"1": true, "2": false, "3": true, "total": 2}`
- The `total` key reflects the total wishlist count for the user, not just the queried courses

### Test Data

| Input | Expected Output |
|-------|----------------|
| `course_ids=1,2,3` with courses 1+3 wishlisted | `{"1":true,"2":false,"3":true,"total":2}` |
| `course_ids` empty | `{"total":<count>}` or empty map |
| Anonymous user | HTTP 403 |

---

## TC-009-E: Wishlist listing page renders correctly

**Layer:** Kernel
**Given:**
- Student has 2 courses wishlisted; each course has name, thumbnail, price, rating, category, level, instructor fields

**When:** `WishlistController::myWishlist()` is called for the authenticated student

**Then:**
- Returns a render array with `#theme = 'lms_my_wishlist'`
- The `cards` variable contains 2 entries, each with fields: `id, name, short_description, price, rating, category, level, duration, bestseller, featured, thumbnail, url, instructor, is_enrolled`
- Cache contexts include user context; cache tag includes `lms_wishlist:<uid>`

---

## Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_wishlist`; install entity schemas for `lms_wishlist`, `lms_course`; create published courses; create student user; install `course_category`/`course_level`/`course_language` taxonomy vocabularies (required by `buildCourseCard()`)
- Teardown: Delete test entities

## Notes

- `WishlistController` source: `web/modules/custom/lms_wishlist/src/Controller/WishlistController.php`
- The `lms_wishlist` entity has no `list_builder`, no `route_provider`, no form handler — only accessible via `WishlistController`. There is no admin CRUD UI.
- Cache invalidation uses `lms_wishlist:<uid>` tag (not core Drupal cache tag patterns) — the test should verify the tag is invalidated after toggle (use `\Drupal::cache()->get()` with a primed cache to detect invalidation)
- `buildCourseCard()` also reads `lms_user_course` to compute `is_enrolled` — install `lms_progress` if testing full card output
- Expected file: `web/modules/custom/lms_wishlist/tests/src/Kernel/WishlistToggleTest.php`
