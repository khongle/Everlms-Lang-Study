# lms_badge — Gamification Badges Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_badge` |
| Name | LMS Badge |
| Layer | **Communication / Gamification** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module gamification badges và leaderboard. Cung cấp 2 entity: Badge (template) và UserBadge (instance đã trao). Module minimal — chỉ có entity definitions, không có route hay service riêng.

---

## Provided Entities

### `lms_badge` (Content Entity)
| Fields | Description |
|--------|-------------|
| name | Tên huy hiệu |
| description | Mô tả |
| image | Icon huy hiệu |
| criteria | Điều kiện trao (text) |

### `lms_user_badge` (Content Entity)
| Fields | Description |
|--------|-------------|
| uid | User nhận |
| badge ref | → lms_badge |
| awarded_at | Timestamp |
| awarded_by | UID người trao / system |
| reason | Lý do cụ thể |

---

## Provided Routes

Không có routing file.

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

Không có template riêng. Badge display phụ thuộc vào các module khác render.

---

## Related Units

- **C03** § Gamification Domain — entities Badge, UserBadge
- **C05** `04-certificate-communication.md` — layer overview

---

## Known Issues / Debt

- Module scaffold-only level — entity definitions nhưng không có service để trao badge tự động.
- Không có leaderboard implementation dù description đề cập "leaderboard".
- Badge criteria là plain text field — không có automated criteria engine.
- Không có route nào để user xem badges của mình — cần integrate với profile page.
