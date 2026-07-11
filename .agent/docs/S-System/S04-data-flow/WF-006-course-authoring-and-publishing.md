# S04 Data Flow - WF-006 Course authoring & publishing

```text
Instructor/editor (workspace)
   │ create/edit course (Inline Entity Form)
   ▼
app-drupal: write lms_course + lms_chapter + lms_lesson, nested ──▶ db-mariadb
   │ optional AI draft (WF-010) feeds the same entity form
   ▼
media upload, video/doc/H5P ──▶ file storage / private files
   │ publish (status=1)
   ▼
catalog read, public ──▶ /courses, /courses/{id}
```
Source: [C12 WF-006](../../C-Design/C12-workflows/WF-006-course-authoring-and-publishing.md). External: none in this flow (the AI provider call belongs to WF-010).
