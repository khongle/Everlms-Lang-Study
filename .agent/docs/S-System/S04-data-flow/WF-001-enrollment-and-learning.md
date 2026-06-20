# S04 Data Flow - WF-001 Enrollment & learning

```text
Learner (web/app)
   │ enroll (free / order / membership)
   ▼
app-drupal: create lms_user_course ──▶ db-mariadb
   │ open lesson (P019 / GET /api/v1/.../lessons)
   ▼
read lms_lesson (+media files) ──▶ render / JSON
   │ mark-complete (POST progress/mark-complete)
   ▼
write lms_user_lesson; recompute lms_user_course.field_progress ──▶ db
   │ completion → emits course-complete / QuizPassedEvent
   ▼
→ WF-003 certificate
```
Source: [C12 WF-001](../../C-Design/C12-workflows/WF-001-enrollment-and-learning.md). No external service; all data in DB + file storage.
