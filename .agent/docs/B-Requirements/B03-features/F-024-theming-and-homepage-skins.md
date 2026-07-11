# F-024 - Theming & homepage skins

**Status:** built
**Primary module:** [lms_theme](../../C-Design/C05-modules/lms_theme.md) (theme)
**Epic:** [E01 Foundation](../../D-Development/D02-epics/E01-foundation.md)
**Related workflows:** —
**Acceptance:** [B05 F-024](../B05-acceptance-criteria/F-024-theming-and-homepage-skins.md)
**Source:** `web/themes/custom/lms_theme/`, `web/themes/custom/lms_classic/`

## Requirement
Platform đi kèm một front-end theme có branding (`lms_theme`, Bootstrap 5.x, Twig/SCSS) với **15 skin homepage** có thể chọn và một theme phụ `lms_classic`. Admin chọn skin và branding qua theme settings (`theme-settings.php`); nội dung homepage do editor quản lý (không có copy hardcode).

## Business value
Branding mạnh, có thể đổi được giúp mỗi deployment trông như được may đo riêng (bespoke) mà không cần đổi code — quan trọng cho việc bán lại/white-label LMS.

## Scope notes
- Template của `lms_theme` được tổ chức theo khu vực (home, user, navigation, block, page, content…).
- Có 15 skin homepage có thể chọn; region/block của homepage chỉnh sửa được.
- Admin theme là Claro; `lms_theme` là front-end mặc định.

## Cross references
- Design: [C09 themes](../../C-Design/C09-themes/_index.md), [C11 pages/blocks](../../C-Design/C11-pages-components/_index.md)
