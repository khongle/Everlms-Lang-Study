# F-024 Acceptance Criteria - Theming & homepage skins

## Core criteria
1. **Given** `lms_theme` là default theme, **when** bất kỳ trang front-end nào render, **then** trang dùng Twig template và Bootstrap styling của theme.
2. **Given** 15 homepage skin, **when** admin chọn một skin trong theme settings, **then** homepage render theo skin đó.
3. **Given** các content block/region của homepage, **when** editor chỉnh sửa chúng, **then** homepage được cập nhật (không hardcode copy).
4. **Given** admin area, **when** được truy cập, **then** admin area dùng theme Claro.

## Evidence expected
- Theme: [lms_theme](../../C-Design/C05-modules/lms_theme.md); [C09 themes](../../C-Design/C09-themes/_index.md).
- Test: skin-switch smoke (mỗi skin trong 15 skin); homepage editor-usability smoke (E03).
