# E01 - Foundation & platform

**Status:** done · **Phase:** 1

## Goal
Nền tảng chung, theme và các tiện ích platform mà mọi epic khác đều dựa vào.

## Scope
- Modules: [lms_base](../../C-Design/C05-modules/lms_base.md), [open_switcher](../../C-Design/C05-modules/open_switcher.md), [lms_theme](../../C-Design/C05-modules/lms_theme.md)
- Features: F-021 (users/roles/impersonation), F-024 (theming/skins)
- Roles/permissions baseline ([C02](../../C-Design/C02-user-roles/_index.md)); DDEV + config sync ([A01](../../A-Project-Information/A01-CLAUDE/_index.md))

## Stories
- [E01S01](../D03-stories/E01S01-lms_base.md), [E01S02](../D03-stories/E01S02-open_switcher.md), [E01S03](../D03-stories/E01S03-lms_theme.md)

## Dependencies
Không có (root).

## Open work
Không có việc cốt lõi; secrets/Key (R-001) và test baseline (R-006) mang tính cross-cutting (D04).
