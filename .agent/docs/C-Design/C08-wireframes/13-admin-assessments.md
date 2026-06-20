# Wireframe: Admin — Assessments

## Quiz List (`/manage/quizzes`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR                                                    │
├─────────────────────────────────────────────────────────────────────┤
│ "Assessment Management"                         [+ Create Quiz]      │
│ [Quizzes] [Question Bank] [Grading Queue (5)]                       │
│ Filters: [Course ▼] [Status ▼]  Search: [_________ 🔍]             │
├─────────────────────────────────────────────────────────────────────┤
│ # │ Quiz Title        │Course      │Questions│Pass%│Attempts│Status │
│ 1 │ HTML Basics Quiz   │Web Dev     │10      │70%  │234     │✅     │
│ 2 │ CSS Test          │CSS Mastery │15      │60%  │156     │✅     │
│ 3 │ JS Final Exam     │JavaScript  │25      │80%  │89      │📝     │
│                                                                      │
│ Pagination                                                           │
└─────────────────────────────────────────────────────────────────────┘
```

## Question Bank (`/manage/questions`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Question Bank"                               [+ Create Question]    │
│ Filters: [Type ▼] [Difficulty ▼] [Tags ▼]   Search: [_________ 🔍] │
├─────────────────────────────────────────────────────────────────────┤
│ # │ Question (truncated)          │Type       │Diff.│Used in│Pts   │
│ 1 │ What does HTML stand for?     │Single Ch. │Easy │3 quiz │10    │
│ 2 │ Select block elements:        │Multi Ch.  │Med  │2 quiz │15    │
│ 3 │ CSS stands for ___            │Fill Blank │Easy │1 quiz │10    │
│ 4 │ Explain the box model         │Essay      │Hard │1 quiz │25    │
│ 5 │ Match HTML tags to purpose    │Matching   │Med  │2 quiz │20    │
└─────────────────────────────────────────────────────────────────────┘
```

## Grading Queue (`/manage/grading`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Grading Queue" (5 pending)                                          │
├─────────────────────────────────────────────────────────────────────┤
│ # │ Student        │Quiz             │Question      │Submitted │Act │
│ 1 │ John Doe       │JS Final Exam    │Q15: Essay    │2h ago   │[📝]│
│ 2 │ Sarah Lee      │JS Final Exam    │Q15: Essay    │5h ago   │[📝]│
│ 3 │ Mike Chen      │CSS Test         │Q10: Essay    │1d ago   │[📝]│
│                                                                      │
│ Click [📝] to open grading panel:                                   │
│ ┌─────────────────────────────────────────────────────────┐         │
│ │ Question: "Explain the CSS box model..."               │         │
│ │ Student Answer: "The box model consists of..."         │         │
│ │ Rubric: Content (30%), Structure (30%), Grammar (20%)  │         │
│ │ Score: [__] / 25   Feedback: [____________________]    │         │
│ │ [Save Grade]                                            │         │
│ └─────────────────────────────────────────────────────────┘         │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built (quiz CRUD, 8 question types); manual grading panel is target-state.**

- Quiz list: `/workspace/quizzes` → `AdminController::quizList` → `lms-admin-quizzes.html.twig`.
- Quiz create/edit: `/workspace/quizzes/create`, `/workspace/quizzes/{id}/edit` → entity forms wrapped in `lms-admin-entity-form.html.twig`.
- Question types: 8 bundles of `lms_question` entity — single_choice, multiple_choice, true_false, essay, fill_blank, matching, ordering, short_answer.
- Grading: static `GradingStrategyFactory` + strategy classes in `lms_quiz`. Binary grading only (no partial credit). Essay questions result in `pending_review` status.
- **Manual grading panel** depicted in the wireframe (rubric scoring for essays) is **target-state** — no dedicated grading UI route exists. Essay results stay as `pending_review` with no instructor UI to apply a score.
- Quiz analytics: `/admin/lms/reports/quizzes` and `/admin/lms/reports/quizzes/{id}` — fully implemented with item analysis and CSV export.

Related: [../C04-entities/01-content-entities.md](../C04-entities/01-content-entities.md) · [../C05-modules/lms_quiz.md](../C05-modules/lms_quiz.md)
