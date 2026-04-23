# Task: Zoom Meeting Integration

**ID:** TASK-023
**Unit:** U-049
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** DONE (75%)
**Completed:** 2026-04-15

## Audit Notes
- ZoomService: Server-to-Server OAuth, token caching in State API ✅
- ZoomMeetingManager: CRUD meetings synced with lesson lifecycle ✅
- ZoomController: Web SDK join page + JWT signature generation ✅
- ZoomJoinBlock: countdown block, ZoomSettingsForm: config ✅
- Auto-create meeting on zoom-type lesson save, auto-delete on lesson delete ✅
- ZoomAiService: AI meeting summaries ✅
- **Known gaps**: No feature flag (enable/disable toggle). Security: JWT signature endpoint has no CSRF protection. Static `\Drupal::` calls in controller. N+1 chain-loading lesson→chapter→course→instructor.

## Description
Tích hợp Zoom API: tạo meeting từ lesson, students nhận join link.

## Acceptance Criteria
- [ ] OAuth2 authentication với Zoom API
- [ ] Create Zoom meeting từ lesson (Zoom type)
- [ ] Students nhận meeting link qua lesson view
- [ ] Meeting details: title, start time, duration, host
- [ ] Auto-generate meeting khi lesson scheduled
- [ ] Feature flag: `FF_FEAT_ZOOM`

## Technical Notes
- Module: `lms_zoom`
- Service: `ZoomService::createMeeting()`, `::getMeetingDetails()`
- Config: `/manage/settings/zoom` (API credentials)

## Dependencies
- U-045 (ZoomService stub) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Integration test with Zoom API (sandbox)
