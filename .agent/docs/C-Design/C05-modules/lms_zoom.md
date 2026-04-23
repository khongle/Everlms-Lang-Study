# lms_zoom — Zoom Integration Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_zoom` |
| Name | LMS Zoom |
| Layer | **Integration** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_lesson`, `lms_ai` |

## Mô tả

Module tích hợp Zoom API cho live class sessions gắn với lesson type `zoom`. Cung cấp ZoomService để call Zoom API, ZoomMeetingManager để CRUD meetings, ZoomAiService để tích hợp AI (transcript analysis, tóm tắt meeting), và Block để join meeting. Cũng có endpoint generate WebSDK JWT signature để embed Zoom trong browser.

---

## Provided Entities

Không có entity riêng. Zoom meeting data được lưu trong `field_zoom_meeting_id` của `lms_lesson`.

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `lms_zoom.settings` | `/admin/config/lms/zoom` | `ZoomSettingsForm` | `administer site configuration` |
| `lms_zoom.join_meeting` | `/zoom/join/{meeting_id}` | `ZoomController::joinMeeting` | logged in |
| `lms_zoom.generate_signature` | `/api/lms/websdk/signature` [POST] | `ZoomController::generateSignature` | logged in |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_zoom.service` | `ZoomService` | Core Zoom API client: call REST API, OAuth tokens; inject http_client, config.factory, logger, state |
| `lms_zoom.meeting_manager` | `ZoomMeetingManager` | Create/update/delete Zoom meetings; inject ZoomService, logger |
| `lms_zoom.ai_service` | `ZoomAiService` | AI-enhanced Zoom: transcript summary, auto Q&A; inject ZoomService, AI plugin manager, http_client, entity_type.manager, logger |
| `logger.channel.lms_zoom` | (parent: logger.channel_base) | Dedicated log channel |

---

## Provided Plugins

| Plugin Type | Implementation | Purpose |
|-------------|---------------|---------|
| Block | `ZoomJoinBlock` | Block hiển thị Join Meeting button cho lesson type zoom |

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Block `ZoomJoinBlock` cache per-user, per-route. JWT signature endpoint không cache.

---

## Related Units

- **C05** `05-integration-theme.md` — layer overview
- `lms_lesson.md` — lesson type `zoom`
- `lms_ai.md` — cung cấp `plugin.manager.lms_ai_provider` cho ZoomAiService
- `lms_progress.md` — lesson_view hiển thị Zoom join button

---

## Known Issues / Debt

- JWT signature generation cho WebSDK cần Zoom SDK Secret — phải được lưu an toàn (không hardcode).
- `ZoomAiService` depends on cả `lms_zoom.service` VÀ `plugin.manager.lms_ai_provider` — coupling cao.
- Không có fallback nếu Zoom API down.
- Meeting recording/transcript feature phụ thuộc Zoom Pro plan.
