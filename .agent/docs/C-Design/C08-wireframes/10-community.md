# Wireframe: Community Pages

## Forum Listing

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ "Discussion Forum"                           [+ New Topic]           │
│ [All Topics] [My Topics] [Popular] [Unanswered]                     │
│ Search: [_______________________________ 🔍]                        │
├─────────────────────────────────────────────────────────────────────┤
│ TOPIC LIST                                                           │
│ ┌───────────────────────────────────────────────────────────────┐   │
│ │ 📌 [Topic Title - Pinned]                              12 💬 │   │
│ │    By Admin │ Course: General │ 2 hours ago                   │   │
│ ├───────────────────────────────────────────────────────────────┤   │
│ │ [How to deploy React app?]                              8 💬 │   │
│ │ By John D. │ Course: Web Dev │ Yesterday │ ✅ Answered        │   │
│ ├───────────────────────────────────────────────────────────────┤   │
│ │ [CSS Grid vs Flexbox - when to use?]                    5 💬 │   │
│ │ By Sarah L. │ Course: CSS Mastery │ 3 days ago                │   │
│ └───────────────────────────────────────────────────────────────┘   │
│ Pagination                                                           │
└─────────────────────────────────────────────────────────────────────┘
```

## Learning Path Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ "Learning Path: Full Stack Web Developer"                            │
│ Path Progress: ████████░░░░░░ 35% (1 of 5 completed)               │
│ Est. Total Time: 105 hours │ Certificate: Full Stack Developer       │
├──────────────────────────────────────────┬──────────────────────────┤
│ PATH TIMELINE (col-8)                    │ PATH DETAILS (col-4)     │
│                                          │                          │
│ ●───── Step 1: HTML & CSS ──────✅      │ Total Courses: 5         │
│ │  [Thumb] HTML & CSS Basics             │ Est. Time: 105h          │
│ │  Duration: 12h │ Status: Completed     │ Certificate: Yes         │
│ │  [Review Course]                       │ Mentor: Sarah Chen       │
│ │                                        │                          │
│ ●───── Step 2: JavaScript ──────🔵      │ Prerequisites:           │
│ │  [Thumb] JS Fundamentals              │ Basic Computer Skills     │
│ │  Duration: 18h │ Progress: 60%         │                          │
│ │  [Continue Learning →]                 │ Next Lesson:             │
│ │                                        │ JavaScript Loops         │
│ ●───── Step 3: React ──────────🔒      │                          │
│ │  [Thumb] React Development            │ [Enroll in Path]         │
│ │  Duration: 25h │ Locked               │                          │
│ │                                        │                          │
│ ●───── Step 4: Node.js ────────🔒      │                          │
│ │  Duration: 20h │ Locked               │                          │
│ │                                        │                          │
│ ●───── Step 5: Final Project ──🔒      │                          │
│    Duration: 30h │ Locked               │                          │
└──────────────────────────────────────────┴──────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**Forum: as-built (with a known debug bug). Learning Path: not implemented.**

**Forum:**
- Forum listing: `/forum` → `LmsForumController::index` → `lms-forum-index.html.twig`. Topics list with reply count, author avatar, pin/lock indicators.
- Topic view: `/forum/{lms_forum_topic}` → `LmsForumController::viewTopic` → `lms-forum-topic.html.twig`. Reply form, mark-best-answer, edit/delete reply.
- New topic: `/forum/new` → `LmsForumController::addTopic`.
- **Known bug:** `LmsForumController::viewTopic` has a `messenger()->addMessage("FRESH CONTROLLER LOADED!")` debug statement left in production code. The dead `ForumController::viewTopic` contains `throw new \Exception("HARD DEBUG!")` — dangerous if ever routed.
- Anonymous users see topic content in teaser mode; replies visible only to authenticated users.

**Learning Path:** The second panel depicted (sequential step-locked course path) is **not implemented** — no `lms_learning_path` entity or module exists. This is a target-state feature (planned, not yet materialized).

Related: [../C04-entities/07-communication-entities.md](../C04-entities/07-communication-entities.md) · [../C05-modules/lms_forum.md](../C05-modules/lms_forum.md)
