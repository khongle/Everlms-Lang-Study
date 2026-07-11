# ISSUE-0009 - Zoom attendance/recording partial

**State:** open
**Severity:** P2 · **Module:** [lms_zoom](../../../C-Design/C05-modules/lms_zoom.md) · **Story:** [E08S07-lms_zoom](../../../D-Development/D03-stories/E08S07-lms_zoom.md)

## Summary
`lms_zoom` supports meeting scheduling and join links, but attendance tracking and recording handling appear partial/incomplete, so compliance training that requires verified live attendance can't fully rely on Zoom-sourced data.

## Steps to reproduce
1. As an instructor, schedule a live session via `/workspace/zoom-settings` (P048) and share the join link.
2. Have a test student join the live meeting and stay for its duration.
3. After the meeting ends, check the session's attendance record in `lms_zoom` → attendance duration/presence data is missing or incomplete; separately check for a recording link → also partial.

## Root cause
The Zoom integration was built against the meeting-creation/join-link API surface first; the webhook/reporting API calls needed to pull post-meeting attendance and recording metadata back into `lms_zoom` were not fully implemented.

## Fix
Confirm the Zoom API scope needed (meeting webhooks, Report API for attendance, Cloud Recording API); implement attendance capture and recording-link storage on the session entity; add tests with a mocked Zoom client so the integration can be verified without live Zoom credentials.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `schedule-meeting` Kernel test extended with a mocked-webhook assertion that a `meeting.ended` payload populates attendance duration and recording URL on the session entity.
