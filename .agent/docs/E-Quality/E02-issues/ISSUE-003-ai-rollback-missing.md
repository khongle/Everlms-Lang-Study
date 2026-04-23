# ISSUE-003 — AI Generation thiếu Rollback/Compensation

| Field | Value |
|-------|-------|
| ID | ISSUE-003 |
| Severity | **Medium** |
| Status | OPEN |
| Source | RISK-003 (A-Project-Information/A03-project-context.md) |
| Related Unit | U-063 — AI rollback + compensation logic |
| Related Flag | FF_FEAT_AI_PROVIDER |
| Found | 2026-04-15 |
| Owner | Unassigned |

## Mô tả

`CourseBuilderService::generateCourseStructure()` tạo entity Course + Chapter + Lesson theo pipeline. Nếu pipeline thất bại giữa chừng (ví dụ: AI trả về JSON không hợp lệ ở lesson N, hoặc entity creation throw exception), các entity đã được persist trước đó không bị rollback. Kết quả: course ở trạng thái nửa vời — không có đủ chapters/lessons, không publish được.

## Cách tái hiện (Reproduction)

1. Kích hoạt `FF_FEAT_AI_BUILDER`
2. Gọi course builder với topic hợp lệ nhưng AI provider trả lỗi quota ở giữa chừng (mock 429)
3. Kiểm tra DB: course entity tồn tại, nhưng thiếu chapters → course ở trạng thái "broken"

## Root Cause Hypothesis

`CourseBuilderService` hiện chỉ có try/catch log lỗi, không có state machine tracking trạng thái generation. Không có cleanup khi partial failure. Queue worker (`CourseContentGenerator`) xử lý từng lesson độc lập mà không link về parent generation job.

## Tác động

- **Error Handling** (G4): Broken course records trong DB
- **UX**: Instructor thấy course tạo nhưng rỗng, không xóa được nếu không có cleanup UI
- **Data Integrity**: Orphan chapters/lessons nếu course bị xóa thủ công

## Phương án khắc phục đề xuất

1. Implement generation state machine: `PENDING → IN_PROGRESS → COMPLETE / FAILED`
2. On failure: rollback tất cả entity đã tạo trong session (lưu list IDs tạm trong state key)
3. Retry policy: exponential backoff với max 3 lần per provider
4. Compensation: đánh dấu course `status=draft_broken`, cho phép instructor retry hoặc delete

## Traceability

- Risk: RISK-003
- Unit spec: U-063
- Related: U-047 (AI provider plugin architecture), U-048 (AI course builder)
