# lms_wishlist — Course Wishlist Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_wishlist` |
| Name | LMS Wishlist |
| Layer | **Additional** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_course` |

## Mô tả

Module danh sách yêu thích (wishlist) cho khóa học. Cung cấp toggle API (add/remove), status check API, và trang `/my-wishlist`. Minimal — 1 entity, 1 controller, 1 routing file.

---

## Provided Entities

### `lms_wishlist` (Content Entity — `Wishlist`)
| Fields | Description |
|--------|-------------|
| uid | User |
| course ref | → lms_course |
| created | Timestamp |

---

## Provided Routes

| Route ID | Path | Controller | Method | Access |
|----------|------|-----------|--------|--------|
| `lms_wishlist.toggle` | `/api/lms/wishlist/toggle` | `WishlistController::toggle` | POST | logged in |
| `lms_wishlist.status` | `/api/lms/wishlist/status` | `WishlistController::getStatus` | GET | logged in |
| `lms_wishlist.my_wishlist` | `/my-wishlist` | `WishlistController::myWishlist` | GET | logged in |

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

`/my-wishlist` cache per-user. API endpoints không cache.

---

## Related Units

- **C03** § Commerce Domain — entity Wishlist
- **C05** `03-progress-commerce.md` — layer overview
- `lms_course.md` — course detail page có wishlist button

---

## Known Issues / Debt

- Toggle endpoint không return updated status trong response — client phải call `/status` lại.
- Không có bulk remove wishlist items.
- Status API query per-course — expensive nếu course detail page gọi nhiều lần.
