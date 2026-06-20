# WF-006 — Course Creation (Manual + AI)

**ID:** WF-006
**Trigger:** Instructor hoặc Admin bắt đầu tạo khóa học mới
**Actors:** Instructor, Admin/Manager, System, AI Provider (optional)
**Preconditions:** Actor có role Instructor hoặc Admin. AI provider configured (nếu dùng AI).
**Related Features:** F-001 (Course Management), F-014 (AI Features)
**Related Entities:** Course, Chapter, Lesson, Quiz, CertificateTemplate, Category

---

## Happy Path — Manual Creation

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Instructor | Tạo course skeleton | Form: title, description, category, pricing, level |
| 2 | Instructor | Save as Draft | Course tạo với status=draft |
| 3 | Instructor | Thêm Chapter | Form chapter + thứ tự |
| 4 | Instructor | Thêm Lesson vào Chapter | Chọn lesson type: Video/Text/Quiz/H5P/SCORM/Zoom |
| 5 | Instructor | Configure từng lesson | Settings: free_preview, order, required/optional |
| 6 | Instructor | Drag-drop reorder | Persist thứ tự chapter/lesson |
| 7 | Instructor | (Optional) Gắn Final Quiz + Certificate Template | Link quiz và template |
| 8 | Instructor | Preview course | Xem như student |
| 9 | Admin/Manager | Review + Publish | Course visible trên catalog |

## Happy Path — AI-Assisted Creation

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Instructor | Click "Generate with AI" | Form: topic, level, language, options |
| 2 | Instructor | Set options (chapters, lessons/chapter, include quizzes) | Gửi prompt đến AI provider |
| 3 | System | AI generates course outline | Hiển thị preview: chapters + lesson titles |
| 4 | Instructor | Approve/Edit outline | Có thể chỉnh sửa trước khi proceed |
| 5 | System | AI generates lesson content | Progress bar: "Generating 3/24..." |
| 6 | System | AI generates quiz questions (optional) | Per-chapter quizzes |
| 7 | Instructor | Review từng lesson | Bắt buộc review, content ở Draft |
| 8 | Instructor | Edit/Refine AI content | Human quality control |
| 9 | (Continue normal flow) | Preview → Publish | — |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 4a | AI provider timeout | Retry với backoff | Tối đa 3 retries |
| 5a | AI generation partial failure | Rollback partial content, log | Instructor restart từ outline |
| 6a | AI content quality issue | Content vẫn ở Draft | Instructor edit thủ công |

## Business Rules

- **AI content**: luôn ở trạng thái Draft, instructor phải review và approve từng lesson.
- **Drag-drop ordering**: Chapter weight + Lesson weight persist vào DB ngay.
- **Course status flow**: Draft → Published → Archived (không revert từ Archived).
- **Publish permission**: chỉ Admin/Manager; Instructor tạo và submit để review.
- AI rollback: nếu generation thất bại mid-way, xóa partial nodes đã tạo.

## AI Generation Capabilities

| Content | Quality | Notes |
|---------|---------|-------|
| Course outline (chapters + lesson titles) | High | Well-organized structure |
| Lesson text content | Medium | Cần review |
| Quiz questions (MC/SC/TF) | Medium | Verify correctness |
| Course SEO description | High | Good starting point |

## Postconditions

- Course tồn tại với ≥1 Chapter và ≥1 Lesson.
- Tất cả content đã reviewed (AI) hoặc entered (manual).
- Course ở trạng thái Published sau admin approval.

## Related Docs

- B03 Feature: [F-001 Course Management](../B03-features/01-course-management.md)
- B03 Feature: [F-014 AI Features](../B03-features/14-ai-features.md)
- INT: [OpenAI](../B06-integration-requirements/INT-004-openai.md), [Google Gemini](../B06-integration-requirements/INT-005-google-gemini.md)
