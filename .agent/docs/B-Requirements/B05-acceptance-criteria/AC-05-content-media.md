# AC-05 — Content & Media

**Feature ID:** F-005 (Content Media)
**Epic:** EPIC-A — Content Lifecycle
**Epic Owner:** Instructor / Admin
**Related Workflow:** [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md)
**Feature Doc:** [B03 F-005 Content Media](../B03-features/05-content-media.md)

---

## Acceptance Criteria

### AC-A03b — Video lesson render

**Priority:** Must
**Given** Lesson type = Video với YouTube/Vimeo URL hoặc uploaded file
**When** Enrolled student mở lesson
**Then** Video player embed đúng (iframe YouTube/Vimeo hoặc HTML5 video)
**And** Completion trigger: video watched ≥90%

### AC-CM01 — Text/rich content render

**Priority:** Must
**Given** Lesson type = Text với HTML content (headings, lists, images, code blocks)
**When** Student mở lesson
**Then** Content render với formatting đúng, không bị stripped
**And** Completion trigger: manual mark-complete button

### AC-CM02 — H5P interactive content

**Priority:** Must
**Given** Lesson type = H5P với package uploaded
**When** Student mở lesson
**Then** H5P player load và interactive content hoạt động
**And** H5P xAPI events ghi nhận completion khi đủ điều kiện

### AC-CM03 — SCORM package

**Priority:** Should
**Given** Lesson type = SCORM với uploaded .zip package
**When** Student mở lesson
**Then** SCORM player load, tracking hoạt động (cmi.completion_status)
**And** Completion status từ SCORM cập nhật LessonProgress

### AC-CM04 — Thumbnail & banner image

**Priority:** Must
**Given** Instructor upload thumbnail khi tạo course
**When** Course hiển thị trên catalog, search kết quả, social share
**Then** Thumbnail resize đúng (multiple breakpoints); lazy load
**And** Alt text được set từ course title cho accessibility

### AC-CM05 — File upload giới hạn và validation

**Priority:** Must
**Given** Instructor cố upload file vượt size limit hoặc sai format
**When** Submit upload form
**Then** Validation error hiển thị rõ: max size, allowed types
**And** Không tạo file record nếu validation fail

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| YouTube video private/deleted | Player hiển thị "Video unavailable"; lesson vẫn có thể mark complete |
| SCORM package corrupt | Error message rõ; không tạo lesson record |
| H5P content update sau student đã complete | Progress không bị reset; warning cho instructor |
| Large video (>500MB) | Chunked upload hoặc external URL; không block browser |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Video player load ≤3s |
| Storage | File upload có virus scan (if configured) |
| CDN | Static assets served via CDN nếu configured |
