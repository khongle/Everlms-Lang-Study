# G01-ADR-004: Bootstrap 5.3 Dark Premium Theme thay vì Tailwind CSS

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

---

## Context

EverLMS cần một design system cho frontend với các yêu cầu:
- Dark premium aesthetic phù hợp với thương hiệu LMS chuyên nghiệp
- Responsive design cho mobile/tablet/desktop
- Tương thích với Drupal Twig templates và Drupal JavaScript behaviors
- Dễ maintain bởi team nhỏ không có dedicated frontend developer
- SCSS compilation nằm trong custom theme `lms_theme`

Hai lựa chọn chính: Bootstrap 5.3 và Tailwind CSS v3.

---

## Decision

Sử dụng **Bootstrap 5.3** làm CSS framework với **dark premium theme** custom:

- Bootstrap 5.3 compiled từ SCSS source với custom variables
- Dark color palette: `--bs-body-bg: #0d1117`, `--bs-primary: #6366f1` (indigo)
- SCSS nằm tại `web/themes/custom/lms_theme/scss/`
- Drupal behaviors cho JavaScript interactions (không dùng Vue/React)
- Bootstrap Icons hoặc FontAwesome cho icon system

A-Project-Information/A01-CLAUDE.md rule #8 đã xác định Bootstrap 5.3 dark premium là chuẩn.

---

## Rationale

**Bootstrap vs Tailwind trade-offs:**

Bootstrap 5.3 cung cấp component library đầy đủ (modal, dropdown, tabs, carousel, accordion) mà LMS cần. Với Tailwind, phải build từng component từ utility classes — tốn thời gian hơn với team nhỏ.

**Dark premium aesthetic:**

LMS market segment (edtech platform) đang chuyển sang dark theme vì:
- Giảm eye strain khi học video dài
- Premium perception
- Bootstrap 5.3 có `data-bs-theme="dark"` built-in, dễ toggle light/dark

**Drupal compatibility:**

Bootstrap behaviors pattern (`Drupal.behaviors.xxx`) integrate tốt với Drupal JavaScript API. Tailwind's JIT compiler cần build step phức tạp hơn trong Drupal theme.

---

### Options Considered

#### Option 1: Bootstrap 5.3 Dark Premium (CHOSEN)
- **Pros:** Component library đầy đủ, dark theme support tích hợp, Drupal community đã có nhiều experience, SCSS customizable, không cần build watcher riêng nếu compile offline
- **Cons:** File size lớn hơn Tailwind nếu không purge, opinionated component styles cần override nhiều
- **Fit:** Phù hợp — LMS cần nhiều components (modals, tabs, accordions), team nhỏ

#### Option 2: Tailwind CSS v3 + Alpine.js (REJECTED)
- **Pros:** File size nhỏ hơn sau purge, utility-first linh hoạt, Alpine.js nhẹ hơn jQuery
- **Cons:** Phải tự build mọi component, JIT compiler cần Node.js watcher chạy song song DDEV, khó tích hợp với Drupal behaviors pattern, không có dark mode out-of-the-box
- **Reason rejected:** Overhead setup cao hơn, không phù hợp với Drupal-centric workflow

#### Option 3: Drupal Olivero theme (base theme) (REJECTED)
- **Pros:** Drupal native, không cần CSS framework thêm, accessibility tốt
- **Cons:** Light-only, không phù hợp premium dark aesthetic, khó customize sâu
- **Reason rejected:** Không đáp ứng yêu cầu dark premium branding

---

## Consequences

### Positive
- Developer có thể dùng Bootstrap docs làm reference trực tiếp
- Dark mode toggle: chỉ cần switch `data-bs-theme` attribute
- Component library giảm thời gian build UI: modal, tabs, accordion sẵn có
- SCSS variables cho phép theming toàn bộ bằng một file `_variables.scss`

### Negative
- Bootstrap CSS bundle ~30KB gzip (chưa purge) — cần CDN hoặc caching tốt
- Drupal admin theme vẫn dùng Claro (light) — không nhất quán với user-facing dark theme, chấp nhận được

### Risks
- Bootstrap 6 (dự kiến 2026-2027) có thể breaking changes — pin version trong `composer.json` hoặc `package.json`, monitor release notes
- Custom SCSS phức tạp có thể conflict với Bootstrap updates — keep custom SCSS tách biệt trong `_custom.scss`, không modify `_variables.scss` của Bootstrap core

---

## Related

- **Design docs:** C01-architecture-decision.md (Technology Decisions: Theme Framework)
- **Coding rules:** A-Project-Information/A01-CLAUDE.md rule #8 (SCSS compiled via lms_theme, Bootstrap 5.3 dark premium)
- **Theme path:** `web/themes/custom/lms_theme/`
- **Other ADRs:** ADR-003 (Twig-first rendering)
- **Design:** C09-skins.md (design tokens, theme system)
