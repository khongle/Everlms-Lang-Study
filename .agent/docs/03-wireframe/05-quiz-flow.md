# Wireframe: Quiz Flow

## Quiz Taking Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ QUIZ HEADER (sticky)                                                 │
│ "Quiz: HTML Basics"  │  ⏱ Time Remaining: 14:32  │  Q 3 of 10      │
├─────────────────────────────────────────┬───────────────────────────┤
│ QUESTION AREA (col-8)                   │ NAVIGATOR (col-4)         │
│                                         │                           │
│ Question 3                              │ Question Status:          │
│ "Which tag is used for hyperlinks?"     │ [1✅][2✅][3▶][4○][5○]   │
│                                         │ [6○][7○][8○][9○][10○]    │
│ ○ <link>                               │                           │
│ ● <a>                                  │ ✅ Answered               │
│ ○ <href>                               │ ▶ Current                 │
│ ○ <url>                                │ ○ Not answered            │
│                                         │ ⚠️ Flagged                │
│ [🚩 Flag for Review]                   │                           │
│                                         │ Progress: ██░░░░ 20%     │
│ ┌─────────────────────────────────────┐ │                           │
│ │ [← Previous]  [Next →]             │ │                           │
│ │             [Submit Quiz]           │ │ [Submit Quiz]             │
│ └─────────────────────────────────────┘ │                           │
└─────────────────────────────────────────┴───────────────────────────┘
```

## Quiz Result Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ RESULT HEADER (centered, py-60)                                      │
│                                                                      │
│          ┌──────────────┐                                            │
│          │   85%        │  Score Ring (SVG circle)                    │
│          │   PASSED ✅  │  Green for pass, Red for fail              │
│          └──────────────┘                                            │
│                                                                      │
│  "Congratulations! You passed HTML Basics"                           │
│  Threshold: 70% │ Your Score: 85% │ Time: 12:45                     │
│                                                                      │
│  [🏆 View Certificate]  [🔄 Retake Quiz]  [← Back to Course]       │
├─────────────────────────────────────────────────────────────────────┤
│ ANSWER REVIEW (expandable)                                           │
│                                                                      │
│ Q1. "What does HTML stand for?" ✅ Correct                          │
│   Your answer: HyperText Markup Language                             │
│                                                                      │
│ Q2. "Which tag is self-closing?" ✅ Correct                         │
│   Your answer: <br>                                                  │
│                                                                      │
│ Q3. "Which is a block element?" ❌ Wrong                            │
│   Your answer: <span>                                                │
│   Correct answer: <div>                                              │
│   Explanation: Block elements take full width...                     │
├─────────────────────────────────────────────────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```
