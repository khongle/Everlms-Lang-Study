# C07-sitemap - Logical Sitemap

**Last updated:** 2026-06-21 · **source_state:** AS_BUILT (route paths from `*.routing.yml`, 2026-06-21)

Global page numbering (`#` 1..N) is the backbone reused by [C11 pages](../C11-pages-components/pages/_index.md) and [C08 wireframes](../C08-wireframes/_index.md). Route groups per [C01](../C01-architecture-decision/_index.md): Public `/*`, App (learner `/dashboard`,`/my-*`,`/learn/*`), Manage (`/workspace/*`,`/manage/*`), Admin (`/admin/*`), API (`/api/*`, documented in [I-External](../../I-External/I01-public-api-spec/README.md)). Host: `https://everlms.ddev.site`.

## Public / Marketing
| # | Label | Title | URL |
| - | ----- | ----- | --- |
| 1 | Homepage | EverLMS home | `/` (`/homepage`) |
| 2 | Course catalog | Browse courses | `/courses` |
| 3 | Course detail | Course landing | `/courses/{lms_course}` |
| 4 | Categories | Category index | `/categories` |
| 5 | Category detail | Courses in category | `/categories/{taxonomy_term}` |
| 6 | Pricing | Plans & pricing | `/pricing` |
| 7 | Instructor profile | Instructor page | `/instructor/{user}` |
| 8 | Blog / News | Article listing | `/news` |
| 9 | Article detail | Blog article | `/news/{node}` |
| 10 | FAQ / About | About page | `/about` |
| 11 | Contact | Contact page | `/contact` |
| 12 | Glossary | Term glossary | `/glossary` |
| 13 | Forum index | Community forum | `/forum` |
| 14 | Forum topic | Topic + replies | `/forum/{lms_forum_topic}` |
| 15 | Certificate verify | Verify a certificate | `/certificate/verify` |
| 16 | Login / Register | Auth | `/user/login`, `/user/register` |

## Account / Learner (App)
| # | Label | Title | URL |
| - | ----- | ----- | --- |
| 17 | Dashboard | Learner dashboard | `/dashboard` |
| 18 | My courses | Enrolled courses | `/my-courses` |
| 19 | Lesson player | Learn a lesson | `/learn/{lms_course}/{lms_lesson}` |
| 20 | Quiz take | Attempt a quiz | `/quiz/{lms_quiz}/take` |
| 21 | Quiz result | Attempt result | `/quiz-result/{lms_quiz_result}` |
| 22 | My quiz results | Results history | `/my-quiz-results` |
| 23 | My certificates | Earned certificates | `/my-certificates` |
| 24 | My notes | Personal notes | `/my-notes` |
| 25 | My wishlist | Saved courses | `/my-wishlist` |
| 26 | My membership | Subscription status | `/my-membership` |
| 27 | Notifications | Notification center | `/notifications` |
| 28 | Profile | User profile | `/profile` |
| 29 | Checkout (course) | Buy a course | `/checkout/{course_id}` |
| 30 | Checkout (membership) | Subscribe | `/checkout/membership/{plan_id}` |

## Workspace / Manage (back-office)
| # | Label | Title | URL |
| - | ----- | ----- | --- |
| 31 | Workspace home | Staff dashboard | `/workspace` |
| 32 | Manage courses | Course list + create | `/workspace/courses` |
| 33 | Course editor | Create/edit course | `/workspace/courses/{lms_course}/edit` |
| 34 | Manage quizzes | Quiz list + create | `/workspace/quizzes` |
| 35 | Manage users | User admin | `/workspace/users` |
| 36 | Manage orders | Order list | `/workspace/orders` |
| 37 | Manage content (FAQ/nodes) | Content admin | `/workspace/content/{content_type}` |
| 38 | Manage pricing/plans | Membership plans | `/workspace/pricing` |
| 39 | Manage ratings | Review moderation | `/workspace/ratings` |
| 40 | Certificate templates | Template editor | `/workspace/certificate-templates` |
| 41 | Issued certificates | Cert admin | `/workspace/certificates` |
| 42 | Content tools | Seed/import/clean | `/workspace/content-tools` |
| 43 | Manage blog | Article admin | `/manage/blog` |
| 44 | AI course builder | AI authoring | `/manage/courses/ai-builder` |
| 45 | Reports (workspace) | Manager reports | `/workspace/reports` |

## Admin / Config
| # | Label | Title | URL |
| - | ----- | ----- | --- |
| 46 | AI settings | AI provider config | `/admin/config/lms/ai` |
| 47 | Payment settings | Gateway config | `/admin/config/everlms/payment` |
| 48 | Zoom settings | Zoom config | `/admin/config/lms/zoom` |
| 49 | Analytics reports | Courses/quizzes/users | `/admin/lms/reports/courses` |
| 50 | Notification broadcast | Send broadcast | `/admin/lms/notifications/broadcast` |
| 51 | Switch user | Impersonation | `/admin/switch-user` |

> 51 logical pages (data-derived from routing, 2026-06-21; per-id add/edit/delete action routes collapsed into their parent logical page). Each row maps to a [C11 page spec](../C11-pages-components/pages/_index.md); each area maps to a [C08 wireframe](../C08-wireframes/_index.md). API routes (`/api/v1/*`) are specified in [I-External](../../I-External/I01-public-api-spec/README.md), not as themed pages.
