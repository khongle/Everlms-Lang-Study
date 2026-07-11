# C06 API/Contract - INT-004 Zoom

**Requirement:** [B06 INT-004](../../B-Requirements/B06-integration-requirements/INT-004-zoom.md)
**Participating modules:** [lms_zoom](../C05-modules/lms_zoom.md)

## Purpose
Tạo Zoom meeting cho các buổi học trực tuyến (live session) của course và hiển thị join link cho learner đã enroll.

## Proposed contract
- **REST namespace:** gọi ra ngoài tới Zoom API (`api.zoom.us/v2/...`); route nội bộ `/zoom/*`.
- **Auth:** Zoom OAuth (server-to-server) / JWT credentials nằm trong `lms_zoom.settings.yml` (R-001).
- **Authorization:** instructor/staff được tạo meeting; learner đã enroll được xem join link.
- **Response envelope:** `{ data: {meeting_id, join_url, schedule}, error|null, meta: {provider: "zoom"} }`; lưu trên `lms_lesson.field_zoom_*`.
- **Audit:** ghi log các lời gọi create/update meeting; không lộ host key cho learner.

## Test requirements
- CI: mock Zoom client trả về payload meeting mẫu (canned).
- Auth+authz: chỉ người tạo được authorised; learner chỉ read-only.
- Failure/fallback: Zoom API lỗi → hiển thị lỗi rõ ràng cho người dùng, không hiện link hỏng.
- Contract test: create-meeting + kiểm tra quyền truy cập link theo role.
