# Wireframe: Public Misc Pages

## Pricing Page (`/pricing`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ "Membership & License Plans"                                         │
│ "Choose the best plan for your learning journey"                     │
│                                                                      │
│ [Monthly] [Annual (Save 20%)]  ← toggle                            │
│                                                                      │
│ ┌────────────┐  ┌─────────────────┐  ┌────────────┐                │
│ │ One-Time   │  │ MONTHLY         │  │ ANNUAL     │                │
│ │ License    │  │ 🔥 Most Popular │  │ Best Value │                │
│ │            │  │                 │  │            │                │
│ │ $50        │  │ $39/mo          │  │ $299/yr    │                │
│ │ per course │  │                 │  │            │                │
│ │            │  │ ✅ All courses  │  │ ✅ All     │                │
│ │ ✅ Single  │  │ ✅ New monthly  │  │ ✅ Priority│                │
│ │ ✅ Lifetime│  │ ✅ Cancel any   │  │ ✅ Webinars│                │
│ │ ✅ Cert    │  │ ✅ Certificates │  │ ✅ Cert    │                │
│ │            │  │                 │  │            │                │
│ │ [Buy Now]  │  │ [Subscribe]     │  │ [Subscribe]│                │
│ └────────────┘  └─────────────────┘  └────────────┘                │
│                                                                      │
│ COMPARISON TABLE                                                     │
│ Feature          │ One-Time │ Monthly │ Annual                      │
│ Course Access    │ 1 course │ All     │ All                         │
│ Certificates     │ ✅       │ ✅      │ ✅                          │
│ Priority Support │ ❌       │ ❌      │ ✅                          │
│ Lifetime Access  │ ✅       │ ❌      │ ❌                          │
│                                                                      │
│ FAQ ACCORDION                                                        │
│ ▸ What is a One-Time License?                                       │
│ ▸ Can I cancel my subscription?                                     │
│ ▸ Do I keep access after cancellation?                              │
└─────────────────────────────────────────────────────────────────────┘
```

## About Us (`/about`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ HERO (bg: team photo + gradient overlay)                             │
│ "About EverLMS"                                                      │
├─────────────────────────────────────────────────────────────────────┤
│ OUR JOURNEY — Introduction paragraph                                │
├──────────────────────────────┬──────────────────────────────────────┤
│ 🎯 Our Vision               │ 🚀 Our Mission                       │
│ Text about empowering...     │ Text about cutting-edge LMS...       │
├──────────────────────────────┴──────────────────────────────────────┤
│ CORE VALUES (4 cards)                                                │
│ [♾️ Build Once Use Forever] [⚙️ Customizable] [🛡️ Your Data] [🚀 Fast]│
├─────────────────────────────────────────────────────────────────────┤
│ WARRANTY BANNER │ CONTACT CTA                                        │
└─────────────────────────────────────────────────────────────────────┘
```

## Blog Listing (`/blog`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├──────────────────────────────────────────┬──────────────────────────┤
│ FEATURED ARTICLE (col-8)                 │ SIDEBAR (col-4)          │
│ [Large banner image]                     │                          │
│ "Title of Featured Article"             │ Popular Tags             │
│ Excerpt... [Read More]                   │ [AI] [Career] [Tech]    │
├──────────────────────────────────────────┤                          │
│ ARTICLE GRID (2 col)                     │ Recent Posts             │
│ ┌──────────┐ ┌──────────┐               │ 1. Post title (date)    │
│ │ [Thumb]  │ │ [Thumb]  │               │ 2. Post title (date)    │
│ │ Title    │ │ Title    │               │                          │
│ │ [NEWS]   │ │ [UPDATE] │               │ Newsletter Signup        │
│ │ Date     │ │ Date     │               │ [Email___] [Subscribe]  │
│ └──────────┘ └──────────┘               │                          │
│ Pagination                               │                          │
└──────────────────────────────────────────┴──────────────────────────┘
```

## FAQ Page (`/faq`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ "Frequently Asked Questions"                                         │
│ Category tabs: [General] [Courses] [Payment] [Technical]            │
├─────────────────────────────────────────────────────────────────────┤
│ ▼ How do I enroll in a course?                                      │
│   Answer text here with step-by-step instructions...                │
│                                                                      │
│ ▸ Can I get a refund?                                               │
│ ▸ How do certificates work?                                         │
│ ▸ What payment methods do you accept?                               │
│                                                                      │
│ "Still have questions?" [Contact Support]                            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built (Pricing, About, Contact, Blog); FAQ is as-built via lms_faq entity.**

- Pricing page: `/pricing` → `PageController::pricing` → `lms-pricing.html.twig`. Static marketing page.
- About page: `/about` → `PageController::about` → `lms-about.html.twig`. Static.
- Contact page: `/contact` → `PageController::contact` → `lms-contact.html.twig`. Static.
- Blog/News listing: `/news` → `LmsBlogController::index` → `lms-blog-list.html.twig`. **Dual-track:** public frontend reads core `node:blog`; the `lms_article` entity only backs `/manage/blog` admin CRUD.
- Blog single post: `/news/{node}` → `LmsBlogController::view` → `lms-blog-single.html.twig`.
- FAQ: `lms_faq` entity provides FAQ items managed at `/workspace/content/faqs`. Display is rendered within existing page templates or the glossary page at `/glossary` → `lms-glossary.html.twig`. No dedicated public `/faq` route exists — FAQ accordion is embedded in other page templates.
- Certificate verification: `/certificate/verify` → `Form\VerificationForm` → `certificate-verification-result.html.twig`. Fully implemented (code lookup, revoked check).

Related: [../C05-modules/lms_blog.md](../C05-modules/lms_blog.md) · [../C07-sitemap.md](../C07-sitemap.md)
