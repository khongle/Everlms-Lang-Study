# AC-14 — AI Features

**Feature ID:** F-014
**Epic:** EPIC-H — AI & Integrations
**Epic Owner:** Instructor / System
**Related Workflow:** [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md)
**Feature Doc:** [B03 F-014 AI Features](../B03-features/14-ai-features.md)

---

## Acceptance Criteria

### AC-H01b — Provider abstraction (no hardcode)

**Priority:** Must
**Given** AI generation code sử dụng provider interface/plugin
**When** Code review hoặc static analysis
**Then** Không có string "openai" hoặc "gemini" hardcoded trong business logic controllers
**And** Provider switch qua config, không qua code change

### AC-H02b — AI generation flow với state management

**Priority:** Must
**Given** AI course generation được trigger với 20 lessons
**When** Generation in progress
**Then** Progress state tracked (pending/running/completed/failed) per generation job
**And** Error ở bất kỳ step nào: rollback partial, log error, notify instructor
**And** Retry mechanism hoạt động cho transient errors (network timeout, 429 rate limit)

### AC-AI01 — AI outline generation

**Priority:** Must
**Given** Instructor nhập: topic, level, language, số chapters, lessons/chapter
**When** Submit AI generation form
**Then** AI trả về outline với chapter titles + lesson titles trong ≤30 giây
**And** Outline format parseable; hiển thị preview cho instructor review

### AC-AI02 — AI lesson content generation

**Priority:** Must
**Given** Instructor approve outline
**When** Content generation chạy
**Then** Mỗi lesson nhận rich text content (heading, paragraphs, lists)
**And** Tất cả generated content ở Draft status; instructor phải publish từng lesson
**And** Progress indicator cập nhật real-time (WebSocket hoặc polling)

### AC-AI03 — AI quiz generation

**Priority:** Should
**Given** Instructor enable "Include quizzes" trong generation options
**When** Content generation hoàn tất
**Then** Mỗi chapter có quiz với ≥5 câu hỏi (MC/SC/TF)
**And** Câu hỏi liên quan đến lesson content trong chapter
**And** Đáp án đúng được mark; instructor có thể edit trước khi publish

### AC-H04b — AI rollback on failure

**Priority:** Must
**Given** AI generation đã tạo 15/20 lessons và fail ở lesson 16
**When** Failure detected sau max retries
**Then** Toàn bộ 15 partial nodes xóa (rollback)
**And** Course trở về trạng thái trước generation (chỉ còn skeleton nếu có)
**And** Instructor nhận error notification với option "Try Again"

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| AI returns empty content for lesson | Placeholder lesson với message "AI could not generate content" |
| AI rate limit (429) mid-generation | Pause + wait + resume; không restart từ đầu |
| Instructor close tab during generation | Generation tiếp tục background; kết quả available khi quay lại |
| AI generates content in wrong language | Instructor can re-generate single lesson với correct language prompt |
| Provider API key expired | Graceful error: "AI service unavailable" + admin alert |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Cost control | Max token budget per generation job (configurable) |
| Performance | Generation progress visible ≤5s sau trigger |
| Reliability | Retry 3x exponential backoff: 1s, 2s, 4s |
| Audit | All AI API calls logged với cost estimate |
