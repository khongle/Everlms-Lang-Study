# Task: Google Analytics 4 — Frontend Tracking Integration

**ID:** TASK-071
**Unit:** — (extends U-046: AnalyticsService)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Inject GA4 measurement script vào `lms_theme` và track các key events trong learner journey. Script chỉ inject khi user đã consent (per TASK-062 cookie consent). GA4 Measurement ID cấu hình qua Drupal State/Config (không hardcode). Events được fire qua `gtag('event', ...)` trong custom JS.

## Acceptance Criteria
- [ ] GA4 script (`gtag.js`) inject vào `<head>` chỉ khi cookie `lms_consent=accepted`
- [ ] GA4 Measurement ID lấy từ Drupal config `lms_analytics.settings.ga4_measurement_id` (configurable qua admin)
- [ ] Events tracked:
  - `course_view` — khi user visit `/courses/{slug}`
  - `course_enroll` — khi enrollment thành công
  - `lesson_complete` — khi lesson marked complete
  - `quiz_attempt` — khi user start quiz
  - `quiz_pass` — khi quiz result >= pass score
  - `course_complete` — khi course marked 100%
  - `certificate_download` — khi user download cert PDF
  - `purchase_complete` — khi order payment confirmed
- [ ] Mỗi event có custom dimensions: `course_id`, `lesson_id`, `quiz_id` (khi relevant)
- [ ] GA4 NOT fire khi `lms_consent=rejected` (verify trong Network tab DevTools)
- [ ] Admin config page tích hợp vào `/manage/settings/analytics` hoặc `/manage/settings` overview

## Technical Notes
- Module: `web/modules/custom/lms_analytics/` (U-046: `AnalyticsService.php`)
- Thêm `lms_analytics.settings` config schema: `config/schema/lms_analytics.schema.yml`
- GA4 script inject: `hook_page_attachments()` trong `lms_analytics.module` — check consent cookie
- JS library: `lms_analytics/ga4-events` trong `lms_analytics.libraries.yml`
- Event fires: JS gọi `drupalSettings.lmsAnalytics.events` (server-side push event data vào drupalSettings)
- Hoặc: DOM event listeners trên lesson complete button, quiz submit, etc.
- Cookie check trong PHP: `$_COOKIE['lms_consent'] === 'accepted'`
- Tham khảo U-046 `AnalyticsService.php` cho existing query methods

## Dependencies
- TASK-062 (GDPR consent banner — GA4 phụ thuộc consent gate)
- U-046 (AnalyticsService — DONE)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: script inject when consent=accepted; NOT inject when rejected)
- [ ] GA4 DebugView verified với 5+ events trong staging environment
- [ ] `purchase_complete` event gửi revenue value (order total)
