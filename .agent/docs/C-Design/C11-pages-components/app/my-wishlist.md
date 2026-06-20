---
page: my-wishlist
mode: as-built
owner_module: lms_wishlist
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: My Wishlist

**Canonical URL:** `/my-wishlist` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_wishlist.my_wishlist`
**Solution:** Custom controller (`WishlistController::myWishlist`) queries `lms_wishlist` entities for the current user, builds rich course card data, returns `lms_my_wishlist` theme render array.
**Owner module:** [`lms_wishlist`](../../C05-modules/lms_wishlist.md)
**Primary source:** `entity:lms_wishlist` (all items owned by current user) → references `entity:lms_course`
**Editable source:** `lms_wishlist` entities — created/removed via AJAX `POST /api/lms/wishlist/toggle`; `lms_course` entities (card data source)
**Editor role:** Learner (wishlist owner); course data maintained by instructor
**Editor management UI:** Course cards updated via `/workspace/courses/{id}/edit`; wishlist toggled via heart icon on course pages
**Revision / moderation:** Not revisionable
**Forms:** — (read-only listing + AJAX toggle per card)
**Theme template:** `web/modules/custom/lms_wishlist/templates/lms-my-wishlist.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** Per-user — cache tags `lms_wishlist:{uid}` (invalidated on toggle via `WishlistController`)
**Translation:** Monolingual EN
**Related entities:** [`lms_wishlist`](../../C04-entities/README.md), [`lms_course`](../../C04-entities/README.md)
**Prototype reference:** — (no dedicated prototype)
**Notes:** Template variable is `cards` (not `wishlisted_courses`). Each card is built by `WishlistController::buildCourseCard()` which resolves course thumbnail, instructor info, rating average, enrollment status, price, category, level, duration, and bestseller/featured flags via `lms_base.helper`. Wishlist items are sorted by `created DESC`.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. Page Header / Empty State

- **Scope:** Page-specific
- **Implementation mechanism:** Twig conditional on `cards|length == 0`
- **Plugin / Module:** `lms_wishlist` — `lms-my-wishlist.html.twig`
- **Source content type / Entity:** Static chrome
- **Backed by fields / config keys:** Static text
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Heading + optional empty-state block with CTA
- **Region / Placement:** Top of content area
- **Notes:** Empty state should CTA to `/courses` (course catalog).
- **How to implement (EN):** `{% if cards is empty %}` show empty-state block with link to `/courses`; otherwise show "My Wishlist" heading and count.
- **How to implement (VI):** `{% if cards is empty %}` hiển thị trạng thái trống với link đến `/courses`; ngược lại hiển thị tiêu đề và số lượng.
- **Solution Architect Request:** —

### 2. Wishlisted Course Card Grid

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over `cards` array; each card is a rich course card built by `buildCourseCard()` private method
- **Plugin / Module:** `lms_wishlist` — `WishlistController::myWishlist` + `lms-my-wishlist.html.twig`
- **Source content type / Entity:** `lms_course` (via `lms_wishlist.field_course`)
- **Backed by fields / config keys:** Course: `name`, `field_short_description`, `field_price`, `field_thumbnail`, `field_category`, `field_level`, `field_bestseller`, `field_featured`; Instructor: `field_first_name`, `field_last_name`, `field_avatar`; Computed: rating average (from `lms_rating`), duration, enrollment status (from `lms_user_course`)
- **Editable source:** `lms_course` entities
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Update course thumbnail → check My Wishlist page → confirm new thumbnail shown in card
- **View mode / Projection:** `card` projection built by `buildCourseCard()`; includes `is_enrolled` flag computed from `lms_user_course` query
- **Region / Placement:** Main content area, responsive grid
- **Notes:** `is_enrolled` flag allows "Go to Course" vs "Enroll" CTA differentiation. Rating average computed by averaging `field_score` across approved `lms_rating` entities. `lms_base.helper` used for duration calculation.
- **How to implement (EN):** `WishlistController::myWishlist` queries `lms_wishlist` by uid; for each, calls `buildCourseCard($course)` which loads all card data. Returns `lms_my_wishlist` theme with `cards` variable.
- **How to implement (VI):** `WishlistController::myWishlist` truy vấn `lms_wishlist` theo uid; với mỗi item, gọi `buildCourseCard($course)` để load dữ liệu thẻ. Trả về theme `lms_my_wishlist` với biến `cards`.
- **Solution Architect Request:** —

### 3. Wishlist Toggle (Remove Heart Button)

- **Scope:** Page-specific
- **Implementation mechanism:** AJAX POST to `lms_wishlist.toggle` (`/api/lms/wishlist/toggle`) triggered by heart icon click; JS removes card from DOM on `status=removed` response
- **Plugin / Module:** `lms_wishlist` — `WishlistController::toggle` (AJAX endpoint)
- **Source content type / Entity:** `lms_wishlist` entity
- **Backed by fields / config keys:** `field_course` (course ID in POST body)
- **Editable source:** `lms_wishlist` entity (deleted on toggle-off)
- **Editor role / permission:** Authenticated (owner)
- **Editor management UI:** Inline heart icon on course card
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Click heart on wishlisted course → card animates out → refresh page → confirm course no longer listed
- **View mode / Projection:** Active heart icon (wishlisted state)
- **Region / Placement:** Top-right of each course card
- **Notes:** Cache tag `lms_wishlist:{uid}` is invalidated by `WishlistController::toggle` after each add/remove.
- **How to implement (EN):** Heart button POSTs `{course_id}` JSON to `/api/lms/wishlist/toggle`; on `status=removed`, JS fades out and removes the card element from the DOM.
- **How to implement (VI):** Nút tim POST JSON `{course_id}` đến `/api/lms/wishlist/toggle`; khi `status=removed`, JS fade-out và xóa phần tử thẻ khỏi DOM.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Header / Empty State | "My Wishlist" heading, item count, or empty-state with CTA | Static chrome | N/A | computed/read-only | N/A | N/A | N/A | N/A | N/A |
| Course Card Grid | Course thumbnail, title, short description, price, category, level, rating, instructor name/avatar, bestseller/featured badge, enrollment CTA | `lms_course`, `lms_rating`, `lms_user_course`, instructor user | `name`, `field_short_description`, `field_price`, `field_thumbnail`, `field_category`, `field_level`, `field_bestseller`, `field_featured` | `/workspace/courses/{id}/edit` | `administer lms_course` | N/A | Monolingual EN | `lms_wishlist:{uid}`, per-user | Add course to wishlist → navigate here → confirm card shown |
| Wishlist Toggle (Remove) | Heart icon (active/filled state) | `lms_wishlist` entity | `field_course` | Inline AJAX heart icon | Authenticated (owner) | N/A | N/A | `lms_wishlist:{uid}` | Click heart → card removed |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "My Wishlist" page title | Static UI chrome | Developer convention | — |
| Empty-state copy | Static developer string | Developer convention | — |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Header / Empty State | DONE | — | As-built |
| Wishlisted Course Card Grid | DONE | — | As-built; full `buildCourseCard()` data pipeline |
| Wishlist Toggle (Remove) | DONE | — | As-built; AJAX toggle with cache invalidation |
| Pagination | NOT IMPLEMENTED | — | All wishlisted courses loaded at once |
