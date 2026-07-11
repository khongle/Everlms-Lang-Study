# C05 Module - lms_theme

**Name:** EverLMS front-end theme · **Phase:** 1 · **Epic:** [E01](../../D-Development/D02-epics/E01-foundation.md) · **Status:** done

## Responsibility
**Theme** front-end mặc định (không phải module): Twig template theo từng area, SCSS/CSS, JS, 16 region, 21 asset library, 15 skin homepage có thể chọn, và `theme-settings.php` cho branding. `base theme: false` (standalone; tự đóng gói styling nền Bootstrap riêng).

## Owned / related entities
- Không có (chỉ presentation; data/access vẫn nằm ở module — xem ranh giới phạm vi ở [C09](../C09-themes/_index.md)).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-024](../../B-Requirements/B03-features/F-024-theming-and-homepage-skins.md) | Theming & homepage skins |

## Business workflows (B04)
- —

## Integrations / APIs (B06 / C06)
- —

## Backing pages
- Render toàn bộ trang front-end (xem [C11](../C11-pages-components/_index.md)); admin dùng Claro.

## Cross references
- Design system: [C09 themes](../C09-themes/_index.md); wireframe: [C08](../C08-wireframes/_index.md). Theme thay thế: `lms_classic`.

## Dependencies
- Hệ thống theme của Drupal core; `core_version_requirement: ^11`.

## Implementation notes
- 16 region (header, primary_menu, secondary_menu, hero, breadcrumb, highlighted, content_top, content, content_bottom, sidebar_first/second, footer_first..fourth, footer_bottom).
- `lms_theme.theme` preprocess mỏng (thin); SCSS được compile ra CSS; `package.json` cho build toolchain.
