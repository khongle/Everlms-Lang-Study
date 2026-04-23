# D03 — Unit Spec: ZoomService (API stub)

**Unit ID:** U-045
**Group:** G-INTEGRATION
**Title:** ZoomService (API stub)
**Status:** DONE
**Priority:** P2

---

## Scope

Implements the `lms_zoom` module providing Zoom OAuth2 API integration with `ZoomService` (meeting CRUD, ZAK token), `ZoomMeetingManager`, `ZoomAiService`, `ZoomController`, admin settings form, a `ZoomJoinBlock`, and templates for the web player. The core `ZoomService` is functional (not merely a stub); it includes real HTTP calls via a `request()` helper.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_zoom/lms_zoom.info.yml` | Module declaration |
| `web/modules/custom/lms_zoom/lms_zoom.module` | Hook implementations |
| `web/modules/custom/lms_zoom/lms_zoom.routing.yml` | Zoom controller routes |
| `web/modules/custom/lms_zoom/lms_zoom.services.yml` | Service registration |
| `web/modules/custom/lms_zoom/lms_zoom.libraries.yml` | JS libraries |
| `web/modules/custom/lms_zoom/lms_zoom.links.menu.yml` | Admin menu links |
| `web/modules/custom/lms_zoom/src/Service/ZoomService.php` | Core API service |
| `web/modules/custom/lms_zoom/src/Service/ZoomMeetingManager.php` | Meeting lifecycle manager |
| `web/modules/custom/lms_zoom/src/Service/ZoomAiService.php` | Zoom AI companion service |
| `web/modules/custom/lms_zoom/src/Controller/ZoomController.php` | Zoom web player controller |
| `web/modules/custom/lms_zoom/src/Form/ZoomSettingsForm.php` | Admin settings form |
| `web/modules/custom/lms_zoom/src/Plugin/Block/ZoomJoinBlock.php` | Zoom join block |
| `web/modules/custom/lms_zoom/templates/lms-zoom-web-player.html.twig` | Web player template |
| `web/modules/custom/lms_zoom/templates/lms-zoom-join-block.html.twig` | Join block template |
| `web/modules/custom/lms_zoom/templates/html--lms-zoom-web-player.html.twig` | HTML override for player |
| `web/modules/custom/lms_zoom/templates/page--lms-zoom-web-player.html.twig` | Page override for player |
| `web/modules/custom/lms_zoom/js/zoom-countdown.js` | Countdown timer JS |
| `web/modules/custom/lms_zoom/js/zoom-websdk-runner.js` | Zoom Web SDK runner JS |

---

## Entities / Services / Controllers / Templates

**`ZoomService` methods:**
- `getAccessToken(): ?string` — OAuth2 token fetch
- `request(string $method, string $endpoint, array $options): ?array` — HTTP helper
- `createMeeting(string $topic, string $startTime, int $durationMinutes, ?string $instructorEmail): ?array`
- `getMeeting(string $meetingId): ?array`
- `deleteMeeting(string $meetingId): bool`
- `getRecordings(string $meetingId): ?array`
- `getZakToken(string $userId): ?string`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Module installs; services register |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | ZoomService methods callable; web player renders |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `ZoomService` injectable via DI
- [x] `createMeeting()`, `getMeeting()`, `deleteMeeting()` implemented
- [x] `ZoomJoinBlock` renders join button with countdown
- [x] Admin settings form configures Zoom API credentials
- [x] Web player template renders Zoom Web SDK

---

## Known Debt / Issues

- Full Zoom meeting integration UI (U-049) is SPEC — `ZoomService` provides the API layer but the lesson-level meeting scheduling UI is not yet wired.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-007 | `zoom` lesson type uses this service |
| U-049 | Full Zoom meeting integration (SPEC) |

---

*End of U-045-zoom-service.md*
