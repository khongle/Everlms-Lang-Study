# Wireframe: Course Detail

## Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER (mega-menu)                                                   │
├─────────────────────────────────────────────────────────────────────┤
│ HERO BANNER (h=300px, bg: course banner image + gradient overlay)    │
│                                                                      │
│  Breadcrumb: Home > Courses > Category > [Course Title]              │
│                                                                      │
│  "Complete Web Development Bootcamp"                                 │
│  Short description text here                                         │
│  👤 By John Doe │ ★★★★☆ 4.5 (234 reviews) │ 👥 1,234 enrolled      │
│  🏷️ Web Dev, JavaScript │ 📚 Intermediate                           │
│                                                                      │
├──────────────────────────────────────────┬──────────────────────────┤
│ CONTENT (col-8)                          │ SIDEBAR (col-4, sticky)  │
│                                          │                          │
│ [Overview] [Curriculum] [Instructor]     │ ┌────────────────────┐   │
│ [Reviews] [FAQ]                          │ │ Preview Video      │   │
│                                          │ │ ▶ [Play Button]    │   │
│ ─── OVERVIEW TAB ───                     │ ├────────────────────┤   │
│                                          │ │ $49.99             │   │
│ "What You'll Learn"                      │ │ ~~$99.99~~ 50% off │   │
│ ✅ Build 16 real projects                │ │                    │   │
│ ✅ Master HTML, CSS, JS                  │ │ [Enroll Now]       │   │
│ ✅ Learn React & Node.js                 │ │ [Add to Wishlist]  │   │
│ ✅ Deploy production apps                │ │                    │   │
│                                          │ │ 30-day guarantee   │   │
│ "Description"                            │ ├────────────────────┤   │
│ Full course description...               │ │ Course Includes:   │   │
│                                          │ │ ⏱ 40 hours video  │   │
│ "Requirements"                           │ │ 📄 15 articles     │   │
│ • Basic computer skills                  │ │ 📎 24 downloads    │   │
│ • No programming experience              │ │ 📜 Certificate     │   │
│                                          │ │ 📱 Mobile access   │   │
│ "Who This Course Is For"                 │ │ ♾️ Lifetime access  │   │
│ • Beginners wanting...                   │ └────────────────────┘   │
│                                          │                          │
│ ─── CURRICULUM TAB ───                   │ "Share this course"      │
│                                          │ [FB] [TW] [LI] [📋]     │
│ ▼ Chapter 1: Getting Started (5 lessons) │                          │
│   ✅ 1.1 Introduction (5 min, preview)   │                          │
│   🔒 1.2 Setup Environment (10 min)     │                          │
│   🔒 1.3 Your First Page (15 min)       │                          │
│   🔒 1.4 Quiz: Basics                   │                          │
│                                          │                          │
│ ▸ Chapter 2: HTML Deep Dive (8 lessons)  │                          │
│ ▸ Chapter 3: CSS Mastery (10 lessons)    │                          │
│                                          │                          │
│ ─── INSTRUCTOR TAB ───                   │                          │
│                                          │                          │
│ 👤 [Avatar] John Doe                    │                          │
│ ★ 4.7 Rating │ 👥 12,345 Students       │                          │
│ 📚 15 Courses                            │                          │
│ Bio text...                              │                          │
│                                          │                          │
│ ─── REVIEWS TAB ───                      │                          │
│                                          │                          │
│ Rating Summary:  ★★★★☆ 4.5/5            │                          │
│ ████████████ 5★ 65%                      │                          │
│ ██████       4★ 20%                      │                          │
│ ███          3★ 10%                      │                          │
│ █            2★  3%                      │                          │
│ █            1★  2%                      │                          │
│                                          │                          │
│ [Review Card] [Review Card] ...          │                          │
├──────────────────────────────────────────┴──────────────────────────┤
│ RELATED COURSES                                                      │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐                        │
│ │ Card 1 │ │ Card 2 │ │ Card 3 │ │ Card 4 │                        │
│ └────────┘ └────────┘ └────────┘ └────────┘                        │
├─────────────────────────────────────────────────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```

## Sidebar Sticky Behavior
- Sidebar card sticks to top when scrolling
- On mobile: sidebar moves below hero, floating "Enroll" button at bottom
