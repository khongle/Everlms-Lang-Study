# Task: Color Contrast Tooling in CI + Token Fix

**ID:** TASK-109
**Unit:** — (cross-cutting: a11y + CI)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thêm color contrast linting vào CI pipeline (TASK-069) để tự động phát hiện vi phạm WCAG AA contrast ratio (4.5:1 cho normal text, 3:1 cho large text/UI components). Dùng Stylelint plugin `stylelint-a11y` hoặc `@csstools/stylelint-color-no-invalid-hex`. Document các design tokens trong `lms_theme/scss/_variables.scss` vi phạm contrast. Fix tất cả failing color combos trong scope task này.

## Acceptance Criteria
- [ ] `stylelint-a11y` (hoặc equivalent) thêm vào `devDependencies` trong `package.json`
- [ ] `.stylelintrc.json` tại project root include rule `a11y/content-property-no-static-value` và contrast rules
- [ ] GitLab CI stage `lint` (TASK-069) chạy `npx stylelint "web/themes/custom/lms_theme/scss/**/*.scss"` — block MR khi có contrast failures
- [ ] Audit tất cả color pairs trong `lms_theme/scss/_variables.scss` + `_colors.scss`:
  - [ ] Text màu trên background màu
  - [ ] Button text trên button background
  - [ ] Link color trên page background
  - [ ] Placeholder text trong input fields
- [ ] Document failing pairs trong `.agent/docs/K-QA/K04-a11y-audit/COLOR-CONTRAST-AUDIT.md` với:
  - [ ] Token names
  - [ ] Current ratio
  - [ ] Required ratio (WCAG AA)
  - [ ] Proposed fix
- [ ] Fix tất cả P0/P1 contrast failures trong task này (update SCSS tokens)
- [ ] Visual regression check: sau khi fix tokens, verify design không bị phá (screenshot compare top 5 pages)
- [ ] CI: Stylelint pass trên `main` branch sau khi fixes applied

## Technical Notes
- File quan tâm: `web/themes/custom/lms_theme/scss/_variables.scss`, `_colors.scss`, `_buttons.scss`
- Tool: `stylelint` + `stylelint-a11y` package
- Hoặc dùng `axe-cli` để check contrast trên rendered pages thay vì SCSS source
- WCAG AA thresholds: 4.5:1 (normal text ≤ 18px/14px bold), 3:1 (large text > 18px/14px bold, UI components)
- WebAIM Contrast Checker: webaim.org/resources/contrastchecker/
- Tham chiếu: TASK-069 (CI pipeline để add lint step); TASK-107 (audit report location)

## Dependencies
- TASK-069 (CI pipeline — add Stylelint step vào lint stage)
- TASK-107 (WCAG audit — contrast issues có thể đã được identify)

## Definition of Done
- [ ] Code complete (Stylelint config + SCSS fixes committed)
- [ ] G1-G4 gates passed
- [ ] CI lint stage passes với 0 contrast failures
- [ ] COLOR-CONTRAST-AUDIT.md committed
- [ ] Visual regression: no unintended design changes
- [ ] No new debt introduced
