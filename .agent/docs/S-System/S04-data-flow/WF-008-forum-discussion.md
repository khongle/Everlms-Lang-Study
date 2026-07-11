# S04 Data Flow - WF-008 Forum discussion

```text
Learner/instructor (web)
   │ POST /forum/new (title/body/category)
   ▼
app-drupal: create lms_forum_topic ──▶ db-mariadb
   │ another user replies (POST /forum/{id})
   ▼
create lms_forum_reply; bump topic reply-count/last-reply ──▶ db
   │ notify topic owner
   ▼
write lms_notification ──▶ db; cache tags invalidate list/detail
```
Source: [C12 WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md). No external service; notification is in_app (email/push partial, R-005).
