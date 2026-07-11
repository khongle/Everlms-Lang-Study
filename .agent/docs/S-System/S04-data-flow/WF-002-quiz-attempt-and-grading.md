# S04 Data Flow - WF-002 Quiz attempt & grading

```text
Learner (web/app)
   │ submit quiz (POST /quiz/{quiz}/submit)
   ▼
app-drupal: GradingStrategyFactory scores objective items
   │ write lms_question_response (per question)
   ▼
write lms_quiz_result (field_result, field_pending_review) ──▶ db-mariadb
   │ objective-only → finalize now; essay/short-answer → hold pending
   ▼
instructor grades pending (/manage/quiz-results/{r}/grade) ──▶ db update
   │ finalize pass → emits QuizPassedEvent
   ▼
→ WF-003 certificate
```
Source: [C12 WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md). No external service; all data in DB.
