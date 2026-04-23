# Feature Guide: Học bài với 9 Lesson Types

**Feature ID:** FEAT-01, FEAT-03
**Available since:** v0.1.0
**User roles:** Student (đã enroll khóa)
**Prerequisites:** Đã enroll vào khóa, chapter cha đã unlock.

## What It Does

EverLMS hỗ trợ 9 loại bài học. Mỗi loại có UI riêng phù hợp với nội dung. Progress được tự động cascade: lesson → chapter → course.

## 9 Lesson Types

| Type | UI | Progress Trigger |
|------|-----|------------------|
| **video** | HTML5 player với chapter markers, speed control 0.5-2x | Watched ≥ 80% hoặc reach end |
| **text** | Long-form article, scroll tracking | Scroll ≥ 90% + Time on page ≥ min |
| **quiz** | Embed quiz (xem [learner-03-take-quiz.md](learner-03-take-quiz.md)) | Pass quiz với điểm ≥ passing score |
| **h5p** | H5P interactive content (từ Drupal H5P module) | H5P report xAPI statement "completed" |
| **scorm** | SCORM 1.2/2004 player | SCORM API `cmi.core.lesson_status = "completed"` |
| **pdf** | PDF viewer inline với zoom/page nav | Last page reached + min time |
| **pptx** | Slide viewer (convert server-side) | All slides viewed |
| **zoom** | Embed Zoom meeting iframe / join button | Attendance polled từ Zoom API |
| **mixed** | Multi-block (video + text + embed) | Tất cả block con hoàn thành |

## How to Use

### Bước 1: Mở lesson

1. `/my-learning` → click khóa đang học → resume button đưa đến lesson gần nhất.
2. Hoặc click tab **Curriculum** → chọn lesson cụ thể (nếu đã unlock).

### Bước 2: Completion flow

1. Nội dung render theo type (xem bảng trên).
2. System tự detect completion theo trigger.
3. Khi lesson complete → progress bar chapter/course update.
4. Nếu chapter cuối lesson complete → next chapter auto-unlock.

### Bước 3: Navigation

- **Prev / Next** button cuối lesson.
- Sidebar curriculum highlight lesson hiện tại và lesson đã complete (tick xanh).
- **Take Notes** button (xem [learner-07-notifications-notes.md](learner-07-notifications-notes.md)).
- **Mark as Complete** manual button (nếu admin bật).

## Configuration Options (học viên)

| Option | Default | Description |
|--------|---------|-------------|
| Autoplay next lesson | On | Tự chuyển lesson tiếp khi video end |
| Playback speed | 1x | Nhớ theo user (localStorage) |
| Subtitle | Off | Bật nếu lesson có VTT |

## Tips & Best Practices

- Dùng **bookmark** (Ctrl+D trên video) để đánh dấu đoạn quan trọng.
- Lesson type **mixed** có thể có pre-work text + post-work quiz; đọc instruction trên đầu.
- Chapter có **unlock_rule: sequential** sẽ khóa lesson sau cho đến khi hoàn thành trước.

## Known Limitations

- SCORM player yêu cầu browser hỗ trợ iframe (không dùng private/incognito nếu có cookie block).
- Zoom lesson: join link chỉ active trong khung giờ meeting (±15 min).
- Progress cascade có race condition edge-case (xem RISK-002, TASK-017).

## Related

- Feature spec: `B-Requirements/B03-features/01-course-management.md`, `03-learning-progress.md`
- Wireframe: `C-Design/C08-wireframes/04-lesson-views.md`
- Entity doc: `C-Design/C04-entities/03-progress-entities.md` (UserLesson, UserChapter, UserCourse)
- ADR-008: Progress cascade architecture
- Troubleshooting: `U05-troubleshooting.md` §Lesson playback issues
