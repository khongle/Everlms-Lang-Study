# Feature Guide: Theo dõi Tiến độ Học tập

**Feature ID:** FEAT-03 (learning-progress)
**Available since:** v0.1.0
**User roles:** Student
**Prerequisites:** Đã enroll ít nhất 1 khóa.

## What It Does

Mỗi khi hoàn thành lesson, system update `UserLesson` entity → cascade lên `UserChapter` → `UserCourse`. Dashboard hiển thị progress realtime.

## How to Use

### Mở dashboard

1. User menu → **My Learning** (`/my-learning`).
2. Tabs: **In Progress**, **Completed**, **Wishlist**, **Certificates**.

### In Progress card

Mỗi card hiển thị:
- Course thumbnail + title.
- Progress bar (%).
- Lesson đang học hiện tại (clickable → resume).
- Last accessed timestamp.
- Estimated time remaining.

### Completed card

- Progress 100%, đánh dấu ✓.
- Link: **View Certificate** (nếu khóa có certificate template).
- **Review Course** → mở rating + text review (xem FEAT-08).

### Progress detail page

Click **View Details** trên card → `/my-learning/{course}`:
- Curriculum tree với status mỗi lesson (not started / in progress / completed / failed).
- Chapter progress bar.
- Quiz scores (nếu có).
- Notes của user (xem learner-07).
- Time spent total.

## Cascade Logic (how it works)

```
UserLesson.status = completed
  → UserChapter recompute % = completed_lessons / total_lessons
  → If all lessons in chapter completed → UserChapter.status = completed
  → UserCourse recompute % = completed_chapters / total_chapters (weighted)
  → If all chapters completed → UserCourse.status = completed
  → Trigger:
      - Certificate issuance (nếu eligible)
      - Badge award (nếu eligible)
      - Notification to student
```

Xem ADR-008 (progress cascade architecture) và RISK-002 (race condition handling).

## Configuration Options (học viên)

| Option | Default | Description |
|--------|---------|-------------|
| Email reminder | Weekly | Nhắc học nếu không login 7 ngày |
| Goal | — | Set daily/weekly study time goal (minutes) |

## Tips & Best Practices

- Dùng **Calendar view** để lên lịch học đều đặn.
- **Reset progress** (admin-only action): liên hệ support nếu cần reset khóa.
- Progress đồng bộ giữa thiết bị qua cùng user account.

## Known Limitations

- Offline progress chưa được sync tự động; mobile app (v2+) sẽ hỗ trợ.
- Race condition khi submit lesson từ 2 tab đồng thời — RISK-002 mitigate bằng SELECT FOR UPDATE (ADR-010, pending).

## Related

- Feature spec: `B-Requirements/B03-features/03-learning-progress.md`
- Entity: `C-Design/C04-entities/03-progress-entities.md`
- ADR-008: Progress cascade logic
- Wireframe: `C-Design/C08-wireframes/07-user-dashboard.md`
- Risk: `G-Governance/G02-risk-register.md` §RISK-002
