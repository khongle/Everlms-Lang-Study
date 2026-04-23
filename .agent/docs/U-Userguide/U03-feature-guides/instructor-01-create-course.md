# Feature Guide: Instructor — Tạo Khóa học

**Feature ID:** FEAT-01 (course-management)
**Available since:** v0.1.0
**User roles:** Instructor, Manager, Admin
**Prerequisites:** Role `instructor` được admin gán.

## What It Does

Tạo khóa học với cấu trúc **Course → Chapter → Lesson** (9 lesson types), cấu hình giá/membership access, publish/unpublish.

## How to Use

### Bước 1: Tạo Course

1. **Instructor Dashboard** → **Create Course**.
2. Form:
   - **Title** (required, max 255 chars).
   - **Slug** (auto-generate từ title, override được).
   - **Category** (taxonomy: chọn 1 trong 8 vocab Category).
   - **Level** (Beginner / Intermediate / Advanced).
   - **Summary** (short text, hiển thị trên card).
   - **Description** (rich text, full page).
   - **Thumbnail** (image upload, recommended 16:9).
   - **Preview video** (optional, URL hoặc upload).
   - **Price** (0 = free, số > 0 = paid).
   - **Membership required** (chọn plan nếu khóa exclusive).
3. **Save as Draft** → tạo course với status `unpublished`.

### Bước 2: Thêm Chapter

1. Trong course edit → tab **Curriculum**.
2. **Add Chapter** → title + ordering + unlock rule (`sequential` / `free`).
3. Sắp xếp drag-drop thứ tự chapter.

### Bước 3: Thêm Lesson trong Chapter

1. Inside chapter → **Add Lesson**.
2. Chọn **Lesson Type** (9 types — xem [learner-02-take-lesson.md](learner-02-take-lesson.md)).
3. Form động theo type:
   - **video:** Upload / URL YouTube/Vimeo + duration + VTT subtitle (optional).
   - **text:** Rich text editor.
   - **quiz:** Chọn quiz đã tạo hoặc **Create New Quiz** (xem [instructor-02-build-quiz.md](instructor-02-build-quiz.md)).
   - **h5p:** Upload H5P package.
   - **scorm:** Upload SCORM 1.2/2004 zip.
   - **pdf:** Upload PDF.
   - **pptx:** Upload PPTX (server convert sang slides).
   - **zoom:** Meeting ID + passcode + scheduled time.
   - **mixed:** Multi-block editor (thêm block).
4. **Completion criteria** (tùy type): % watched / quiz pass / all slides / etc.
5. **Save**.

### Bước 4: Preview & Publish

1. Tab **Preview** → mở trong browser như learner thấy.
2. Click **Publish** khi hoàn thành.
3. Course status đổi `published` → visible trên `/courses`.
4. Có thể **Unpublish** bất cứ lúc nào (enrolled students vẫn truy cập được).

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `max_enrollments` | unlimited | Giới hạn số học viên |
| `start_date` | — | Nếu set, khóa chỉ enrollable từ ngày này |
| `end_date` | — | Nếu set, khóa khóa enrollment sau ngày này |
| `prerequisites` | — | Danh sách course IDs phải pass trước |
| `certificate_template` | null | Template phát certificate khi complete |

## Tips & Best Practices

- **Start with outline** — tạo chapter stubs trước, rồi fill lesson sau.
- **Dùng AI Course Builder** (xem [instructor-03-ai-course-builder.md](instructor-03-ai-course-builder.md)) để phác thảo nhanh.
- **Test quiz** bằng role Student (impersonate hoặc tạo test account).
- **Preview video** để thu hút enrollment — nên là 1-2 min intro.
- **Validate lesson order** — `sequential` lock rule chặt hơn, `free` cho phép nhảy cóc.

## Known Limitations

- Drag-drop reorder chỉ trong 1 chapter, chưa cho drag lesson giữa chapter (workaround: delete + recreate hoặc backend API).
- Bulk import lesson chưa có UI — admin có thể dùng `drush lms:import-course` (future).
- Revision history chưa có cho course content (roadmap v0.3.0).

## Related

- Feature spec: `B-Requirements/B03-features/01-course-management.md`
- AC: `B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`
- Workflow: `B-Requirements/B04-business-workflows/WF-006-course-creation.md`
- Entity: `C-Design/C04-entities/01-content-entities.md`
- Wireframe: `C-Design/C08-wireframes/12-admin-courses.md`
- Tutorial: `U06-tutorials/tutorial-01-create-first-course.md`
