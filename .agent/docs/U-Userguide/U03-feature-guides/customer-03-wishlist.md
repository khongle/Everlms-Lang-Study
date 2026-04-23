# Feature Guide: Customer — Wishlist

**Feature ID:** FEAT-16 (essential-enhancements)
**Available since:** v0.1.0
**User roles:** Authenticated
**Prerequisites:** —

## What It Does

Save course (hoặc bundle) để cân nhắc sau, không enroll ngay. Wishlist lưu trong `Wishlist` entity (per user).

## How to Use

### Thêm vào wishlist

- **Course card / detail page** → click icon 🤍 (saved) hoặc **Save for Later**.
- Toast message: "Added to wishlist".
- Icon đổi sang ❤️ (active).

### Xem wishlist

1. User menu → **Wishlist** → `/wishlist`.
2. List card tương tự course listing, thêm:
   - **Added date**.
   - **Price at add time** (compare với giá hiện tại — flag nếu tăng).
   - **Remove** button.
   - **Add to Cart** button (chuyển sang checkout flow).

### Remove

- Click ❤️ trên card để toggle off.
- Hoặc ở trang wishlist → **Remove**.

### Clear all

- **Clear Wishlist** button ở đầu trang → confirm modal → xóa sạch.

### Notification (opt-in)

- Setting: **Notify me on wishlist price drops** → email khi course hoặc bundle trong wishlist giảm giá ≥ 10%.

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| Max items per wishlist | 100 | Admin set, unlimited = -1 |
| Price drop threshold % | 10 | Email trigger |
| Wishlist public URL | Off | Nếu bật, có public link share được |

## Tips & Best Practices

- Không enroll impulse — add wishlist, review sau 2-3 ngày để tránh mua hàng thừa.
- Wishlist khóa yêu cầu membership → system check membership hiện tại khi add → flag nếu không eligible.

## Known Limitations

- Wishlist không sync giữa users (private only).
- Không group wishlist theo folder/collection.
- Public share link chưa support trong MVP.

## Related

- Feature spec: `B-Requirements/B03-features/16-essential-enhancements.md`
- Entity: `web/modules/custom/lms_wishlist/src/Entity/Wishlist.php`
- Wireframe: `C-Design/C08-wireframes/02-course-listing.md`, `08-user-account.md`
