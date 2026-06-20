# Wireframe: Homepage

## Layout Structure

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
│ [Logo]  Home | Courses | Categories | Pricing | Blog | Contact      │
│                                                    [Login] [Register]│
├─────────────────────────────────────────────────────────────────────┤
│ HERO SECTION (100vw, h=500px)                                        │
│ ┌───────────────────────────────────────────────────────────────────┐│
│ │  Background: gradient overlay on image                            ││
│ │                                                                   ││
│ │  "Transform Your Future with EverLMS"        [Course illustration]││
│ │  Subtitle text about online learning                              ││
│ │                                                                   ││
│ │  [Browse Courses]  [Start Free Trial]         Stats: 1000+ courses││
│ │                                               50k+ students       ││
│ │                                               98% satisfaction    ││
│ └───────────────────────────────────────────────────────────────────┘│
├─────────────────────────────────────────────────────────────────────┤
│ FEATURED COURSES (container, py-80)                                  │
│                                                                      │
│  "Featured Courses"                              [View All →]        │
│                                                                      │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐                │
│  │Thumbnail│  │Thumbnail│  │Thumbnail│  │Thumbnail│                │
│  │         │  │         │  │         │  │         │                │
│  │ Title   │  │ Title   │  │ Title   │  │ Title   │                │
│  │ ★★★★☆  │  │ ★★★★★  │  │ ★★★★☆  │  │ ★★★☆☆  │                │
│  │ $49.99  │  │ FREE    │  │ $29.99  │  │ $79.99  │                │
│  │ [⏱ 12h] │  │ [⏱ 8h]  │  │ [⏱ 6h]  │  │ [⏱ 20h] │                │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘                │
│  (4 cols desktop, 2 cols tablet, 1 col mobile)                       │
├─────────────────────────────────────────────────────────────────────┤
│ CATEGORIES (container, py-80, bg-dark-alt)                           │
│                                                                      │
│  "Popular Categories"                                                │
│                                                                      │
│  [🖥 Programming]  [🎨 Design]  [📊 Business]  [📱 Mobile]          │
│  [📈 Marketing]   [🎵 Music]   [📸 Photo]     [🌐 Languages]       │
│  (icon + name + course count badge)                                  │
│  (4x2 grid desktop, 3 cols tablet, 2 cols mobile)                    │
├─────────────────────────────────────────────────────────────────────┤
│ HOW IT WORKS (container, py-80)                                      │
│                                                                      │
│  "How EverLMS Works"                                                 │
│                                                                      │
│  ① Browse               ② Enroll               ③ Learn              │
│  Find courses           Purchase or free        Watch, read,         │
│  that match             enrollment              practice & quiz      │
│  your goals                                                          │
│                                                                      │
│  ④ Get Certified                                                     │
│  Earn certificates                                                   │
│  upon completion                                                     │
├─────────────────────────────────────────────────────────────────────┤
│ TESTIMONIALS (container, py-80, bg-dark-alt)                         │
│                                                                      │
│  "What Students Say"                                                 │
│                                                                      │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐   │
│  │ "Great platform…"│  │ "Changed my …"  │  │ "Best courses…"  │   │
│  │ ★★★★★            │  │ ★★★★★            │  │ ★★★★☆            │   │
│  │ 👤 John D.       │  │ 👤 Sarah L.      │  │ 👤 Mike T.       │   │
│  │ Web Developer    │  │ Designer         │  │ Data Analyst     │   │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘   │
│  (carousel / 3 cols)                                                 │
├─────────────────────────────────────────────────────────────────────┤
│ CTA BANNER (container-fluid, py-60)                                  │
│                                                                      │
│  "Ready to Start Learning?"                                          │
│  "Join 50,000+ students today"                                       │
│  [Start Free Trial]  [Browse Courses]                                │
├─────────────────────────────────────────────────────────────────────┤
│ FOOTER                                                               │
│ ┌──────────┬──────────┬──────────┬──────────┐                        │
│ │ About    │ Courses  │ Support  │ Social   │                        │
│ │ About Us │ Popular  │ Contact  │ Facebook │                        │
│ │ FAQ      │ Latest   │ Help     │ Twitter  │                        │
│ │ Terms    │ Free     │ Forum    │ LinkedIn │                        │
│ │ Privacy  │          │          │ YouTube  │                        │
│ └──────────┴──────────┴──────────┴──────────┘                        │
│ © 2024 EverLMS. All rights reserved.                                 │
└─────────────────────────────────────────────────────────────────────┘
```

## Responsive Behavior

| Breakpoint | Changes |
|-----------|---------|
| ≥1200px | Full layout as above, 4-col course grid |
| 992-1199px | 3-col course grid, categories 3x3 |
| 768-991px | 2-col course grid, stacked hero, hamburger menu |
| <768px | 1-col everything, CTA buttons full-width |

---

## Implementation status (as of 2026-06-20)

**as-built.** Route `/homepage` → `CoursePublicController::homepage` → template `lms-homepage.html.twig`. The page is fully implemented and rendered by `lms_course` module. 15 switchable homepage style variants exist under `lms_theme/templates/home/styles/` (glassmorphism default, plus minimal, corporate, academy, community, creative, editorial, government, gradient, luxury, medical, nature, neon, playful, retro). The `homepage_style` variable is stored in `lms_base` settings and passed by the controller. The wireframe's hero + course grid + category sections are all rendered via this single template; actual content is pulled from `lms_course` entities.

Related: [../C05-modules/lms_course.md](../C05-modules/lms_course.md) · [../C07-sitemap.md](../C07-sitemap.md)
