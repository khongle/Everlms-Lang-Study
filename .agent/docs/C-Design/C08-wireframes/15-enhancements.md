# Wireframe: Essential Enhancements

> Wireframes cho các tính năng Tier 1 + Tier 2 mới.

## Wishlist Button (on Course Card + Detail)

```
┌──────────────────────────────────────────────────────┐
│ Course Card                                          │
│ ┌──────────────────────┐                             │
│ │ [Course Thumbnail]   │  Web Development Bootcamp   │
│ │                  [❤️]│  ⭐ 4.8 (234) | Beginner    │
│ └──────────────────────┘  $49.99                     │
│                           [Enroll Now]               │
└──────────────────────────────────────────────────────┘

Course Detail Page:
┌──────────────────────────────────────────────────────┐
│ [Course Banner]                                      │
│ Web Development Bootcamp          [❤️ Add to Wishlist]│
│ ⭐ 4.8 (234 reviews) | 12h | Beginner               │
└──────────────────────────────────────────────────────┘
```

## My Wishlist Page (`/my-wishlist`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ "My Wishlist" (5 courses)                                            │
│                                                                      │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐                              │
│ │ [Thumb]  │ │ [Thumb]  │ │ [Thumb]  │                              │
│ │ Web Dev  │ │ React    │ │ CSS Ani  │                              │
│ │ $49.99   │ │ $79.99   │ │ FREE     │                              │
│ │ ⭐ 4.8   │ │ ⭐ 4.5   │ │ ⭐ 4.2   │                              │
│ │[Enroll][🗑]│[Enroll][🗑]│[Enroll][🗑]│                              │
│ └──────────┘ └──────────┘ └──────────┘                              │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Course Bundles (`/bundles`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Course Bundles — Save more, learn more"                             │
│                                                                      │
│ ┌────────────────────────────────────────────────────────────────┐  │
│ │ 🔥 WEB DEVELOPMENT BUNDLE                    Save 40%         │  │
│ │ 5 courses • 48 hours total                                    │  │
│ │                                                                │  │
│ │ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐                │  │
│ │ │HTML  │ │CSS   │ │JS    │ │React │ │Node  │                │  │
│ │ │$29   │ │$29   │ │$49   │ │$79   │ │$49   │                │  │
│ │ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘                │  │
│ │                                                                │  │
│ │ ~~$235~~ → $139                              [Buy Bundle]     │  │
│ └────────────────────────────────────────────────────────────────┘  │
│                                                                      │
│ ┌────────────────────────────────────────────────────────────────┐  │
│ │ 📱 MOBILE DEV BUNDLE                         Save 35%         │  │
│ │ 3 courses • 36 hours                                          │  │
│ │ ~~$179~~ → $119                              [Buy Bundle]     │  │
│ └────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Free Preview Indicator (Lesson List)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Curriculum                                                           │
│                                                                      │
│ Chapter 1: Getting Started                                           │
│ ├ ✅ Lesson 1: Introduction (5 min)          [👁 FREE PREVIEW]      │
│ ├ ✅ Lesson 2: Setup Environment (10 min)    [👁 FREE PREVIEW]      │
│ ├ 🔒 Lesson 3: Your First Code (15 min)     [Enroll to access]     │
│ ├ 🔒 Lesson 4: Variables (12 min)            [Enroll to access]     │
│ └ 🔒 Quiz: Chapter 1 Test                    [Enroll to access]     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Student Notes (Side Panel in Lesson View)

```
┌─────────────────────────────────────────────┬───────────────────────┐
│ LESSON CONTENT                               │ 📝 MY NOTES          │
│                                              │                      │
│ [Video Player / Text Content]                │ Auto-saved ✓         │
│                                              │                      │
│                                              │ ┌──────────────────┐│
│                                              │ │ [B] [I] [• ]     ││
│                                              │ │                  ││
│                                              │ │ This lesson      ││
│                                              │ │ explains how...  ││
│                                              │ │                  ││
│                                              │ │ Key takeaways:   ││
│                                              │ │ • Concept A      ││
│                                              │ │ • Concept B      ││
│                                              │ └──────────────────┘│
│                                              │                      │
│                                              │ [📥 Export PDF]      │
├─────────────────────────────────────────────┴───────────────────────┤
│ [← Prev] Navigation [Next →]                                        │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Dark Mode Toggle

```
Header (Light):
┌──────────────────────────────────────────────────────────────────────┐
│  [Logo] Home | Courses | ...            [☀️→🌙] [🔔] [👤 Profile ▼] │
└──────────────────────────────────────────────────────────────────────┘

Header (Dark):
┌──────────────────────────────────────────────────────────────────────┐
│  [Logo] Home | Courses | ...            [🌙→☀️] [🔔] [👤 Profile ▼] │
│  ████████████████████████████████████████████ dark bg ████████████████│
└──────────────────────────────────────────────────────────────────────┘
```

---

## Abandoned Cart Email (Admin View)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR                                                    │
├─────────────────────────────────────────────────────────────────────┤
│ "Cart Recovery"                               [⚙️ Settings]        │
│                                                                      │
│ KPIs: [🛒 45 Abandoned] [📧 38 Emails Sent] [✅ 8 Recovered] [18%] │
│                                                                      │
│ Settings:                                                            │
│ ☑ Enable abandoned cart emails                                       │
│ Reminder 1: [1 hour] after abandonment   [Edit Template]            │
│ Reminder 2: [24 hours] after abandonment [Edit Template]            │
│ Reminder 3: [72 hours] + coupon          [Edit Template]            │
│ Coupon discount: [10] %                                              │
│                                                                      │
│ [Save Settings]                                                      │
│                                                                      │
│ Recent Abandoned Carts:                                              │
│ # │ User        │ Course         │ Amount │ Status     │ Date        │
│ 1 │ John Doe    │ Web Dev Boot   │ $49    │ ✅ Recovered│ 2h ago     │
│ 2 │ Sarah Lee   │ React Master   │ $79    │ 📧 Email 2 │ 5h ago     │
│ 3 │ Mike Chen   │ CSS Animation  │ $29    │ ❌ Lost     │ 3d ago     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Instructor Revenue Dashboard (`/manage/instructor`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "My Revenue"                              Date: [This Month ▼]      │
│                                                                      │
│ KPIs:                                                                │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                │
│ │ 💰 $2,450│ │ 📈 $890  │ │ 👥 156   │ │ ⏳ $340  │                │
│ │ Total    │ │ This Mo  │ │ Students │ │ Pending  │                │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘                │
│                                                                      │
│ REVENUE BY COURSE                                                    │
│ # │ Course Title     │ Enrolled │ Revenue │ Avg Rating │ Completion │
│ 1 │ Web Dev Bootcamp │ 89       │ $1,200  │ ⭐ 4.8     │ 72%       │
│ 2 │ React Master     │ 45       │ $890    │ ⭐ 4.6     │ 58%       │
│ 3 │ CSS Animation    │ 22       │ $360    │ ⭐ 4.3     │ 85%       │
│                                                                      │
│ [📥 Export CSV]                                                      │
└─────────────────────────────────────────────────────────────────────┘
```
