# Task: GDPR Cookie Consent Banner + Privacy / Terms / Cookies Pages

**ID:** TASK-062
**Unit:** — (cross-unit: lms_theme, lms_base, contrib eu_cookie_compliance)
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement GDPR-compliant cookie consent flow: banner xuất hiện lần đầu visit với lựa chọn Accept/Reject. Cookies không thiết yếu (Google Analytics 4, marketing pixels) bị block cho đến khi user đồng ý. Author 3 static legal pages: `/privacy`, `/terms`, `/cookies`. Footer phải link đến cả 3.

Đây là requirement bắt buộc trước khi go-live tại EU market và trước khi TASK-071 (GA4 integration) được enable.

## Acceptance Criteria
- [ ] Cookie consent banner hiển thị cho anonymous users chưa có cookie consent preference
- [ ] Accept: ghi cookie `lms_consent=accepted`; banner ẩn; GA4 script inject (nếu TASK-071 done)
- [ ] Reject: ghi cookie `lms_consent=rejected`; banner ẩn; GA4 script KHÔNG inject
- [ ] Banner có link "Cookie Policy" trỏ đến `/cookies`
- [ ] `/privacy` — Privacy Policy page có content placeholder + last updated date
- [ ] `/terms` — Terms of Service page có content placeholder + last updated date
- [ ] `/cookies` — Cookie Policy page liệt kê cookies used (essential + analytics)
- [ ] Footer links đến `/privacy`, `/terms`, `/cookies` trên tất cả pages
- [ ] Banner responsive + accessible (keyboard navigable, ARIA labels)
- [ ] Consent preference persist 365 ngày (cookie max-age)

## Technical Notes
- Contrib: `drupal/eu_cookie_compliance` (verify có trong composer.json, cài nếu chưa có)
- Hoặc custom JS trong `lms_theme/js/cookie-consent.js` nếu contrib quá nặng
- Routes cho `/privacy`, `/terms`, `/cookies`: thêm vào `lms_base.routing.yml` với static page controller
- Content có thể dùng Drupal Basic Page node type hoặc hard-code controller text
- Footer region template: `web/themes/custom/lms_theme/templates/navigation/footer.html.twig`
- GA4 injection gate: kiểm tra cookie `lms_consent=accepted` trong `lms_theme/js/analytics.js` (TASK-071)
- SCSS: `_cookie-banner.scss`, `_legal-pages.scss`

## Dependencies
- TASK-071 (GA4) phụ thuộc TASK-062; TASK-062 không phụ thuộc TASK-071

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (Behat: consent flow accept/reject)
- [ ] Verified GA4 NOT firing khi consent = rejected (browser DevTools Network check)
- [ ] 3 legal pages có URL đúng C07 sitemap (update C07 nếu cần)
