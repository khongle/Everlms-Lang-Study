# Task: Video Handling — Decision + Implementation

**ID:** TASK-150
**Unit:** — (extend `lms_lesson`)
**Group:** G-CONTENT
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Decide on and implement the video hosting/delivery strategy for lesson content. Three options are evaluated in an ADR with cost and performance analysis for the Vietnamese audience. The chosen option is then implemented: lesson video field updated, player integrated, and basic access control applied (enrolled users only).

## Context
- `lms_lesson` currently has a placeholder video field; no actual video playback is implemented.
- Vietnamese audience: high mobile penetration, variable bandwidth; CDN edge in Singapore or Asia-Pacific preferred.
- Options: (a) self-host with HLS streaming (nginx + hls.js), (b) BunnyCDN / Mux / Cloudflare Stream (managed transcoding + CDN), (c) embed Vimeo/YouTube unlisted videos.
- ADR must cover: setup cost, monthly cost at 1 000 active users / 50 GB storage, latency to HCM City, DRM support, upload workflow complexity.

## Acceptance Criteria
- [ ] ADR document written at `/home/hainn/projects/everlms/.agent/docs/D-Development/D05-tasks/backlog/TASK-150-adr-video.md` covering options (a), (b), (c) with cost table and recommendation
- [ ] Chosen option implemented end-to-end: upload path, storage, transcoding (if applicable), delivery URL, player
- [ ] Lesson edit form updated: video field accepts URL (option b/c) or file upload (option a) depending on chosen path
- [ ] Player embedded in lesson view: responsive (16:9 ratio), dark themed, with play/pause/seek/quality selector/fullscreen
- [ ] Video playback restricted to enrolled users + instructors + admins; direct URL not accessible to unenrolled users
- [ ] Progress events: player fires `timeupdate` events; JS reports completion at 90% watch to `lms_progress` (mark lesson complete)
- [ ] Resume position: last watched position stored in `lms_note` or a new `lms_video_progress` table; player seeks to it on load
- [ ] Admin/instructor can replace the video for a lesson without losing enrolled student progress data
- [ ] Lesson thumbnail auto-generated or manually uploaded (uses "thumbnail" image style from TASK-149)

## Technical Notes
- Module: `web/modules/custom/lms_lesson/`
- ADR first — do not start implementation until ADR is reviewed and option chosen
- Recommended default (if no strong reason otherwise): BunnyCDN Stream (competitive pricing, Asia PoPs, simple API, HLS output)
- For BunnyCDN: `LessonVideoService::uploadToBunnyCdn(file)` → returns `video_id`; store `video_id` in lesson field; player embed via BunnyCDN iframe or hls.js + `.m3u8` URL
- Access control for option (b/c): signed URL or token-auth — generate time-limited signed playback URL server-side; never expose raw storage URL to browser
- For option (c) Vimeo: domain-level privacy + Vimeo oEmbed; simpler but less control
- JS: `lms_lesson/video-player` library — wraps chosen player SDK; emits custom events `lesson:progress`, `lesson:complete`
- `lms_progress` subscriber listens to `lesson:complete` event via AJAX endpoint `POST /lesson/{lesson}/complete`
- Resume: `lms_video_progress` table (uid, lesson_id, position_seconds, updated) or reuse `lms_note` timestamp field
- SCSS: `_lesson-video.scss` — player container, 16:9 aspect ratio, dark chrome

## Dependencies
- TASK-148 (Media library — lesson thumbnail field uses media reference)
- TASK-149 (Image styles — thumbnail style for lesson card)
- TASK-137 (Lesson comments — shares lesson view page; implement after or in parallel)

## Definition of Done
- [ ] ADR written and decision recorded
- [ ] Code complete (chosen path implemented)
- [ ] G1-G4 gates passed
- [ ] Playback restricted: unenrolled user gets 403 / redirect to enroll (manual test in PR)
- [ ] 90% completion event fires and marks lesson complete in `lms_progress` (integration test)
- [ ] Resume position works across sessions (manual test noted in PR)
- [ ] No new debt introduced
