# Wireframe: Mobile App (Flutter)

> 25+ screens cho iOS & Android app. Bottom tab navigation, dark theme.

## Design Rules

> [!IMPORTANT]
> - **Navigation:** Bottom Tab Bar (5 tabs) + Stack navigation
> - **Theme:** Dark premium, consistent with web. Blue/purple gradient accents
> - **Typography:** Inter / System font
> - **Platform:** Flutter — iOS 15+ / Android 10+
> - **Aspect Ratio:** 9:19.5 (modern smartphone)

---

## 1. Bottom Tab Bar

```
┌─────────────────────────────────────────────────┐
│  🏠 Home  │  🔍 Explore  │  📚 Learn  │  🔔  │  👤  │
└─────────────────────────────────────────────────┘
```

---

## 2. Authentication Screens

### S-01: Splash Screen
```
┌──────────────────────────┐
│                          │
│                          │
│                          │
│       [EverLMS Logo]     │
│       ══════════         │
│       Loading...         │
│                          │
│                          │
│                          │
└──────────────────────────┘
```

### S-02: Onboarding (3 slides)
```
┌──────────────────────────┐
│                          │
│    [Illustration 1]      │
│                          │
│  "Learn Anywhere"        │
│  Access 1000+ courses    │
│  on your mobile device   │
│                          │
│      ● ○ ○               │
│                          │
│  [Get Started]           │
│  Already have account?   │
└──────────────────────────┘
```

### S-03: Login
```
┌──────────────────────────┐
│  ←                       │
│                          │
│  Welcome Back 👋         │
│  Sign in to continue     │
│                          │
│  ┌────────────────────┐  │
│  │ 📧 Email           │  │
│  └────────────────────┘  │
│  ┌────────────────────┐  │
│  │ 🔒 Password     👁 │  │
│  └────────────────────┘  │
│                          │
│  [      Sign In      ]   │
│                          │
│  Forgot Password?        │
│                          │
│  ─── or continue with ── │
│                          │
│  [🔵 Google] [🔵 Facebook]│
│                          │
│  Don't have account?     │
│  Sign Up                 │
└──────────────────────────┘
```

### S-05: Register
```
┌──────────────────────────┐
│  ←                       │
│                          │
│  Create Account 🎓       │
│  Start your learning     │
│                          │
│  ┌────────────────────┐  │
│  │ 👤 Full Name       │  │
│  └────────────────────┘  │
│  ┌────────────────────┐  │
│  │ 📧 Email           │  │
│  └────────────────────┘  │
│  ┌────────────────────┐  │
│  │ 🔒 Password     👁 │  │
│  └────────────────────┘  │
│  ┌────────────────────┐  │
│  │ 🔒 Confirm PW   👁 │  │
│  └────────────────────┘  │
│                          │
│  [    Create Account   ] │
│                          │
│  Already have account?   │
│  Sign In                 │
└──────────────────────────┘
```

---

## 3. Home Tab (S-10)

```
┌──────────────────────────┐
│  [Avatar] Hi, John! 👋   │
│                 [🔔 3]   │
├──────────────────────────┤
│  Continue Learning       │
│  ┌──────────────────┐    │
│  │ [Thumb] Web Dev  │    │
│  │ ████████░░ 72%   │    │
│  │ [Continue]       │    │
│  └──────────────────┘    │
│                          │
│  Featured Courses        │
│  ┌───────┐ ┌───────┐    │
│  │[Thumb]│ │[Thumb]│    │
│  │React  │ │Python │    │
│  │⭐ 4.8 │ │⭐ 4.6 │    │
│  │$49    │ │$79    │    │
│  └───────┘ └───────┘    │
│           ← swipe →      │
│                          │
│  Popular Categories      │
│  [💻 Dev] [📊 Data]      │
│  [🎨 Design] [📱 Mobile] │
│                          │
│  Course Bundles 🔥       │
│  ┌──────────────────┐    │
│  │ Web Dev Bundle    │    │
│  │ 5 courses • -40% │    │
│  │ $139 (~~$235~~)   │    │
│  └──────────────────┘    │
├──────────────────────────┤
│ 🏠  🔍  📚  🔔  👤     │
└──────────────────────────┘
```

---

## 4. Explore Tab (S-11)

```
┌──────────────────────────┐
│  Explore                 │
│  ┌──────────────────┐    │
│  │ 🔍 Search courses │    │
│  └──────────────────┘    │
├──────────────────────────┤
│  Filter: [All ▼] [Level ▼]│
│          [Price ▼] [⭐ ▼] │
│                          │
│  234 courses found       │
│                          │
│  ┌──────────────────┐    │
│  │ [Thumb]           │    │
│  │ React Masterclass │    │
│  │ John Smith        │    │
│  │ ⭐ 4.8 (234)      │    │
│  │ $79     [❤️]      │    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ [Thumb]           │    │
│  │ Python for AI     │    │
│  │ Jane Doe          │    │
│  │ ⭐ 4.6 (189) FREE │    │
│  │           [❤️]    │    │
│  └──────────────────┘    │
│  ... scroll ...          │
├──────────────────────────┤
│ 🏠  🔍  📚  🔔  👤     │
└──────────────────────────┘
```

---

## 5. Course Detail (S-20)

```
┌──────────────────────────┐
│  ← Course Detail  [❤️]   │
├──────────────────────────┤
│  ┌──────────────────┐    │
│  │ [Course Banner]   │    │
│  │      ▶           │    │
│  └──────────────────┘    │
│                          │
│  React Masterclass       │
│  by John Smith           │
│  ⭐ 4.8 (234) | 12h      │
│  Intermediate            │
│                          │
│  [About] [Curriculum]    │
│  [Reviews] [Instructor]  │
│                          │
│  ── About ──             │
│  Learn React from scratch│
│  to advanced patterns... │
│                          │
│  ── Curriculum ──        │
│  ▼ Ch 1: Getting Started │
│    👁 1.1 Introduction   │
│    👁 1.2 Setup          │
│    🔒 1.3 Components     │
│    🔒 1.4 State          │
│  ▸ Ch 2: Advanced (4)    │
│  ▸ Ch 3: Projects (3)    │
│  ▸ Final Quiz            │
│                          │
├──────────────────────────┤
│  $79.99  [Enroll Now]    │
└──────────────────────────┘
```

---

## 6. My Learning Tab (S-12)

```
┌──────────────────────────┐
│  My Learning             │
│  [All] [In Progress]     │
│  [Completed] [Wishlist]  │
├──────────────────────────┤
│  ┌──────────────────┐    │
│  │ [Thumb] Web Dev   │    │
│  │ ████████░░ 72%    │    │
│  │ Ch 3, Lesson 2    │    │
│  │ [Continue →]      │    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ [Thumb] CSS Anim  │    │
│  │ ██░░░░░░░░ 15%    │    │
│  │ Ch 1, Lesson 3    │    │
│  │ [Continue →]      │    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ [Thumb] Python ✅ │    │
│  │ ██████████ 100%   │    │
│  │ Completed 2d ago  │    │
│  │ [🏆 Certificate]  │    │
│  └──────────────────┘    │
├──────────────────────────┤
│ 🏠  🔍  📚  🔔  👤     │
└──────────────────────────┘
```

---

## 7. Lesson View (S-30)

### Video Lesson
```
┌──────────────────────────┐
│  ← Lesson 1.3       [📝]│
├──────────────────────────┤
│  ┌──────────────────┐    │
│  │                  │    │
│  │   ▶ Video Player │    │
│  │                  │    │
│  │ ▶ 3:45 ━━━━━━ 12:30 │
│  │ [1x] [CC] [⛶]   │    │
│  └──────────────────┘    │
│                          │
│  Components & Props      │
│  Chapter 1 • Lesson 3    │
│                          │
│  Lesson content text     │
│  goes here...            │
│                          │
│  📎 Attachments (2)      │
│  ├ slides.pdf            │
│  └ code.zip              │
│                          │
│  💬 Comments (12)        │
│  ├ "Great explanation!"  │
│  └ "How about hooks?"   │
│                          │
├──────────────────────────┤
│  [← Prev]  ✅ Complete  [Next →]│
└──────────────────────────┘
```

### Notes Side Panel (tap 📝)
```
┌──────────────────────────┐
│  ← Notes      [Export]   │
├──────────────────────────┤
│  Lesson: Components      │
│  Auto-saved ✓            │
│                          │
│  ┌──────────────────┐    │
│  │ [B] [I] [• ] [1.]│    │
│  │                  │    │
│  │ Key takeaways:   │    │
│  │ • Props are      │    │
│  │   immutable      │    │
│  │ • State triggers │    │
│  │   re-render      │    │
│  │                  │    │
│  └──────────────────┘    │
└──────────────────────────┘
```

---

## 8. Quiz Taking (S-33)

```
┌──────────────────────────┐
│  Quiz: Ch 1 Test  ⏱ 8:45│
├──────────────────────────┤
│  Question 3 of 10        │
│  ━━━━━━━━━░░░░░ 30%     │
│                          │
│  What is a React         │
│  component?              │
│                          │
│  ○ A CSS class           │
│  ● A reusable UI piece   │
│  ○ A database table      │
│  ○ A server endpoint     │
│                          │
│                          │
│                          │
│                          │
│                          │
│                          │
├──────────────────────────┤
│  [← Prev]    [Next →]    │
│                          │
│  ● ● ●○○○○○○○           │
└──────────────────────────┘
```

---

## 9. Quiz Result (S-34)

```
┌──────────────────────────┐
│  Quiz Result             │
├──────────────────────────┤
│                          │
│       🎉 Passed!         │
│                          │
│     ┌──────────┐         │
│     │   85%    │         │
│     │  Score   │         │
│     └──────────┘         │
│                          │
│  ✅ 17/20 Correct        │
│  ⏱ Time: 12:30           │
│  Pass Score: 70%         │
│                          │
│  ── Answer Review ──     │
│  ✅ Q1: What is JSX?     │
│  ✅ Q2: Component...     │
│  ❌ Q3: Lifecycle...     │
│     Your: B → Correct: D │
│  ... tap to expand ...   │
│                          │
│  [🏆 View Certificate]   │
│  [🔄 Retake Quiz]        │
│  [← Back to Course]      │
└──────────────────────────┘
```

---

## 10. Notifications Tab (S-13)

```
┌──────────────────────────┐
│  Notifications     [All read]│
├──────────────────────────┤
│  Today                   │
│  ┌──────────────────┐    │
│  │ 🎉 Certificate   │    │
│  │ Python cert ready │    │
│  │ 2h ago       [→] │    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ 💬 Forum Reply   │    │
│  │ John replied to.. │    │
│  │ 5h ago       [→] │    │
│  └──────────────────┘    │
│                          │
│  Yesterday               │
│  ┌──────────────────┐    │
│  │ 📺 Live Session   │    │
│  │ React Q&A in 15m  │    │
│  │ 1d ago       [→] │    │
│  └──────────────────┘    │
├──────────────────────────┤
│ 🏠  🔍  📚  🔔  👤     │
└──────────────────────────┘
```

---

## 11. Profile Tab (S-14)

```
┌──────────────────────────┐
│  Profile          [⚙️]   │
├──────────────────────────┤
│        ┌──────┐          │
│        │Avatar│          │
│        └──────┘          │
│      John Doe            │
│      john@email.com      │
│                          │
│  Stats:                  │
│  [5 Courses] [3 Certs]   │
│  [🔥 12 day streak]      │
│                          │
│  ┌──────────────────┐    │
│  │ 📚 My Courses    →│    │
│  │ 🏆 Certificates  →│    │
│  │ 📦 My Orders     →│    │
│  │ 💳 Membership    →│    │
│  │ ❤️ Wishlist      →│    │
│  │ 📝 My Notes      →│    │
│  │ 🌙 Dark Mode   [●]│    │
│  │ 🌐 Language    [EN]│    │
│  │ 🔔 Notifications →│    │
│  │ ❓ Help & FAQ    →│    │
│  │ 🚪 Sign Out      →│    │
│  └──────────────────┘    │
├──────────────────────────┤
│ 🏠  🔍  📚  🔔  👤     │
└──────────────────────────┘
```

---

## 12. Checkout Flow (S-50 → S-53)

### Cart
```
┌──────────────────────────┐
│  ← Cart (2 items)        │
├──────────────────────────┤
│  ┌──────────────────┐    │
│  │ [T] React Master │    │
│  │     $79.99   [🗑]│    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ [T] CSS Anim     │    │
│  │     $29.99   [🗑]│    │
│  └──────────────────┘    │
│                          │
│  Coupon: [_________][Apply]│
│                          │
│  Subtotal:    $109.98    │
│  Discount:     -$10.00   │
│  ─────────────────────   │
│  Total:        $99.98    │
│                          │
│  [    Checkout    ]      │
└──────────────────────────┘
```

### Payment Success
```
┌──────────────────────────┐
│                          │
│         ✅               │
│                          │
│  Payment Successful!     │
│                          │
│  Order #12345            │
│  Amount: $99.98          │
│                          │
│  You're now enrolled in: │
│  • React Masterclass     │
│  • CSS Animation         │
│                          │
│  [Start Learning →]      │
│  [View My Courses]       │
└──────────────────────────┘
```

---

## 13. Offline Mode

```
┌──────────────────────────┐
│  ← Downloads             │
├──────────────────────────┤
│  Downloaded (3)          │
│  ┌──────────────────┐    │
│  │ ✅ Lesson 1.1    │    │
│  │   45 MB    [🗑]  │    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ ✅ Lesson 1.2    │    │
│  │   32 MB    [🗑]  │    │
│  └──────────────────┘    │
│  ┌──────────────────┐    │
│  │ ⬇️ Lesson 1.3    │    │
│  │   ━━━━━░░ 60%   │    │
│  └──────────────────┘    │
│                          │
│  Total: 112 MB           │
│  [Download All Ch. 1]    │
└──────────────────────────┘
```

---

## Screen Summary

| Section | Screens | IDs |
|---------|---------|-----|
| Auth | Splash, Onboarding, Login, Register, Forgot PW | S-01 → S-06 |
| Home | Home feed, Continue Learning | S-10 |
| Explore | Search, Filters, Course cards | S-11 |
| Course | Detail, Curriculum, Reviews | S-20 → S-23 |
| Learning | Lesson (Video/Text), Notes, Quiz Taking/Result | S-30 → S-34 |
| Profile | Profile, Settings, My Courses/Certs/Orders | S-14, S-40 → S-44 |
| Commerce | Cart, Checkout, Payment, Success | S-50 → S-53 |
| Other | Notifications, Downloads, My Learning | S-12, S-13 |
| **Total** | **~25 screens** | |

---

## Implementation status (as of 2026-06-20)

**as-built (Flutter app v1.0.10+33 with 33 screens; backend API fully implemented).**

The Flutter app (`app/` directory) is built with Riverpod/GoRouter/Dio and targets iOS/Android. Backend is served by `lms_mobile_api` module at `/api/v1/*` (22 REST endpoints + 9 web API endpoints).

**Authentication (S-01 → S-06):** Fully implemented. Custom bearer token auth via keyvalue.expirable + OTP (NOT OAuth2/JWT despite unused `oauth_*` config keys). Endpoints: `/api/v1/user/register`, `/api/v1/user/register/verify-otp`, `/api/v1/oauth/token`, `/api/v1/user/forgot-password`, `/api/v1/user/reset-password`.

**Course discovery (S-10, S-11, S-20 → S-23):** as-built. `/api/v1/courses/search`, `/api/v1/categories`, `/api/v1/courses/{id}`, `/api/v1/courses/{id}/enroll-free`.

**Learning (S-30 → S-34):** as-built. `/api/v1/courses/{courseId}/lessons/{lessonId}`, `/api/v1/quiz/{quizId}`, `/api/v1/quiz/{quizId}/submit`, `/api/v1/progress/mark-complete`.

**Commerce (S-50 → S-53):** The mobile checkout wireframe (Apple Pay/Google Pay style) depicts a payment flow. The `/api/v1/` API does **not** have a mobile checkout endpoint — mobile users are directed to the web checkout at `/checkout/{course_id}`. Mobile payment via native SDKs is **target-state**.

**Offline / Downloads (S-12, S-13):** **target-state** — no offline content download API exists.

All `/api/v1/*` routes declare `_access: 'TRUE'` but authentication is enforced at the controller level.

Related: [../C07-sitemap.md](../C07-sitemap.md)
