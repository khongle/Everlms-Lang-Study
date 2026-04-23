# Feature Guide: Instructor — AI Course Builder

**Feature ID:** FEAT-14 (ai-features)
**Available since:** v0.1.0 (experimental, feature flag `FF_FEAT_AI_BUILDER`)
**User roles:** Instructor, Manager, Admin
**Prerequisites:** Feature flag bật + AI provider configured (OpenAI / Gemini / Pollinations).

> ⚠️ **Experimental.** Feature flag `FF_FEAT_AI_BUILDER` mặc định OFF trong production. Liên hệ admin để bật.
> ⚠️ **Known risk FIND-003:** OpenAI calls chưa có `max_tokens` cap — admin cần set quota trước khi bật production.

## What It Does

AI Course Builder dùng LLM để phác thảo cấu trúc khóa học từ một topic + audience level. Không thay thế instructor expertise — là **công cụ gợi ý** để tiết kiệm thời gian soạn outline.

## How to Use

### Bước 1: Mở AI Builder

1. **Instructor Dashboard** → **Create Course** → click **Use AI Builder** link.
2. Modal AI Builder mở.

### Bước 2: Điền brief

1. **Topic** (required): Chủ đề khóa, càng cụ thể càng tốt.
   - ✅ "Python async programming for backend developers"
   - ❌ "Programming"
2. **Target audience:** Beginner / Intermediate / Advanced.
3. **Duration:** Short (2-4h) / Medium (5-10h) / Long (10+h).
4. **Learning objectives** (optional): Textarea, mỗi dòng 1 objective.
5. **Style preferences:** Practical / Theoretical / Mixed.
6. **AI provider:** Dropdown (OpenAI / Gemini / Pollinations — tùy config).

### Bước 3: Generate

1. Click **Generate Outline**.
2. Loading 10-30s (tùy provider).
3. Output: JSON structure → render thành tree:
   - Course title + summary đề xuất.
   - List chapters (mỗi chapter: title + 2-3 lesson suggestions).
   - Estimated duration per lesson.

### Bước 4: Review & Edit

1. Tree hiển thị editable — click vào title/desc để sửa.
2. Drag-drop reorder chapter/lesson.
3. Delete lesson/chapter không phù hợp.
4. **Regenerate chapter** button nếu không hài lòng 1 chapter.

### Bước 5: Commit

1. **Create Course from Outline** → tạo real Course + Chapter entities.
2. Lesson stub được tạo với title + description; **bạn vẫn phải fill nội dung thật** (video / text / quiz).
3. Redirect về course edit → tiếp tục như [instructor-01-create-course.md](instructor-01-create-course.md) từ Bước 3.

## Configuration Options (admin set)

| Option | Default | Description |
|--------|---------|-------------|
| `default_provider` | `openai` | Provider mặc định |
| `openai.model` | `gpt-4o-mini` | Model cho cost/quality balance |
| `openai.max_tokens` | 2000 | **⚠️ Phải set** để tránh FIND-003 runaway cost |
| `openai.timeout_seconds` | 60 | Timeout per request |
| `gemini.model` | `gemini-1.5-flash` | — |
| `daily_quota_per_user` | 10 generations | Chống abuse |
| `cache_ttl_hours` | 24 | Cache output cho identical brief |

## Tips & Best Practices

- **Specific beats generic.** Brief càng cụ thể (audience, objective), output càng tốt.
- **Always review.** AI có thể hallucinate (đề xuất lesson không tồn tại, duration không hợp lý). Luôn edit.
- **Mix providers.** OpenAI tốt cho comprehensive outline, Gemini nhanh hơn, Pollinations free tier.
- **Iterate.** Regenerate chapter cho kết quả đa dạng; chọn version hợp lý nhất.
- **Dùng cho outline, không cho nội dung chi tiết.** Soạn lesson content thủ công để đảm bảo chất lượng.

## Known Limitations

- Chưa support upload reference material (textbook PDF → AI summary) — roadmap.
- Output luôn tiếng English trong MVP; chọn model multilingual nếu cần tiếng Việt.
- Không sinh quiz tự động trong MVP (roadmap v0.3.0).
- Cost không tracked per-user trong analytics (roadmap).

## Related

- Feature spec: `B-Requirements/B03-features/14-ai-features.md`
- AC: `B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md`
- ADR-006: AI Provider Plugin architecture
- Risk: `G-Governance/G02-risk-register.md` §RISK-003 (AI rollback) + FIND-003 (max_tokens)
- Admin setup: `U08-admin-guide.md` §AI Provider Setup
