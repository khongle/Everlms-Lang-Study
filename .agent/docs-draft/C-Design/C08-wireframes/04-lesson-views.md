# Wireframe: Lesson Views

> 5 lesson types share same layout shell, content area changes per type.

## Common Layout Shell

```
┌─────────────────────────────────────────────────────────────────────┐
│ LESSON HEADER                                                        │
│ [← Back to Course]  "Course Name"  Progress: ████████░░ 65%         │
├─────────────────────────────────────────┬───────────────────────────┤
│ CONTENT AREA (col-8)                    │ CURRICULUM SIDEBAR (col-4)│
│                                         │                           │
│ Lesson Title                            │ Course: PHP Basics        │
│ [Type-specific content below]           │ Progress: ████ 65%        │
│                                         │                           │
│                                         │ ▼ Chapter 1               │
│                                         │   ✅ 1.1 Lesson          │
│                                         │   ✅ 1.2 Lesson          │
│                                         │   ▶ 1.3 Lesson ← current│
│                                         │   ○ 1.4 Lesson           │
│                                         │ ▸ Chapter 2              │
│                                         │ ▸ Chapter 3              │
│                                         │                           │
│                                         │ Icon Legend:               │
│                                         │ ✅ Completed              │
│                                         │ ▶ Current                 │
│                                         │ ○ Not started             │
│ ┌─────────────────────────────────────┐ │ 🔒 Locked                │
│ │ [← Previous Lesson] [Mark Complete]│ │                           │
│ │                     [Next Lesson →]│ │                           │
│ └─────────────────────────────────────┘ │                           │
├─────────────────────────────────────────┴───────────────────────────┤
│ LESSON RESOURCES (collapsible)                                       │
│ 📎 Resource 1.pdf  📎 Resource 2.zip  📎 Slides.pptx               │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Type 1: Video Lesson

```
┌─────────────────────────────────────────┐
│ VIDEO PLAYER (16:9 aspect ratio)         │
│ ┌─────────────────────────────────────┐ │
│ │                                     │ │
│ │         ▶ Play Button               │ │
│ │                                     │ │
│ ├─────────────────────────────────────┤ │
│ │ ▶ ████████░░░░░ 12:34 / 45:00     │ │
│ │ [🔊] [CC] [⚙️ Quality] [⛶ Full]  │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ Lesson Description text...              │
│                                         │
│ "Comments" (expandable)                 │
│ [Add comment _________________________] │
│ 💬 User1: "Great explanation!"          │
│ 💬 User2: "Can you clarify X?"          │
└─────────────────────────────────────────┘
```

---

## Type 2: Text Lesson

```
┌─────────────────────────────────────────┐
│ RICH TEXT CONTENT                        │
│                                         │
│ Formatted text with:                    │
│ • Headings (H2, H3)                    │
│ • Bold, italic, lists                  │
│ • Code blocks with syntax highlighting │
│ • Embedded images                      │
│ • Info/Warning callout boxes           │
│ • Tables                               │
│                                         │
│ Estimated reading time: 15 min          │
│ [Mark as Complete ✅]                   │
└─────────────────────────────────────────┘
```

---

## Type 3: H5P Interactive

```
┌─────────────────────────────────────────┐
│ H5P CONTENT FRAME                       │
│ ┌─────────────────────────────────────┐ │
│ │ [H5P Interactive Content]           │ │
│ │                                     │ │
│ │  (drag-drop, quiz, fill-blank,     │ │
│ │   interactive video, etc.)          │ │
│ │                                     │ │
│ │  Score: 6/10                        │ │
│ │  [Submit Answer]                    │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ H5P auto-reports completion to LMS      │
└─────────────────────────────────────────┘
```

---

## Type 4: SCORM

```
┌─────────────────────────────────────────┐
│ SCORM PLAYER (iframe, min-height 600px) │
│ ┌─────────────────────────────────────┐ │
│ │                                     │ │
│ │  [SCORM Content]                    │ │
│ │  Navigation: [< Prev] [Next >]     │ │
│ │  [Chapter Menu]                     │ │
│ │                                     │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ SCORM Tracking Info:                    │
│ Status: In Progress │ Score: --/100     │
│ Time: 00:32:15      │ Completion: 72%   │
└─────────────────────────────────────────┘
```

---

## Type 5: Zoom Live Session

```
┌─────────────────────────────────────────┐
│ MEETING INFO CARD                       │
│                                         │
│ 📅 "Live Session: Advanced React"      │
│                                         │
│ Date: March 15, 2024 at 10:00 AM (GMT) │
│ Duration: 2 hours                       │
│ Instructor: John Doe                    │
│                                         │
│ Countdown: 02d 14h 32m 15s             │
│                                         │
│ [Join Meeting 🔵] (active when live)   │
│ [Add to Calendar 📅]                   │
│                                         │
│ Meeting Description:                    │
│ We'll cover advanced React patterns...  │
│                                         │
│ Recording: [Available after session]    │
└─────────────────────────────────────────┘
```
